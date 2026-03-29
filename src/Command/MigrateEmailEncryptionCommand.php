<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;

/**
 * bin/cake migrate_email_encryption
 *
 * Three-step migration:
 *   1. Add email_hash column + index to the users table (DDL, idempotent).
 *   2. Replace information_submitter email addresses with user IDs.
 *   3. Encrypt plain-text user emails and populate email_hash.
 */
class MigrateEmailEncryptionCommand extends Command
{
    public static function defaultName(): string
    {
        return 'migrate_email_encryption';
    }

    /**
     * Keys that may have been used before the real key was configured.
     * If a value was encrypted with one of these, it will be re-encrypted
     * with the current key during migration.
     */
    private const LEGACY_KEYS = [
        'CHANGE_ME_32_BYTES_MIN_HEX_STRING',
    ];

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(
            'Migrate users.email to AES-256 ciphertext and replace ' .
            'submissions.information_submitter emails with user IDs.'
        );

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $key = (string)Configure::read('App.emailEncryptionKey');
        if (empty($key) || $key === 'CHANGE_ME_32_BYTES_MIN_HEX_STRING') {
            $io->error('App.emailEncryptionKey is not configured. Aborting.');

            return self::CODE_ERROR;
        }

        /** @var \Cake\Database\Connection $db */
        $db = ConnectionManager::get('default');

        // ------------------------------------------------------------------
        // Step 1 – DDL: widen name columns + add email_hash column + index
        // ------------------------------------------------------------------
        $io->out('<info>Step 1: DDL changes (widen name columns, add email_hash)…</info>');
        $this->runDdl($db, $io);

        // ------------------------------------------------------------------
        // Step 2 – submissions: replace email with user ID
        // ------------------------------------------------------------------
        $io->out('<info>Step 2: Replacing information_submitter emails with user IDs…</info>');
        $this->migrateSubmissions($db, $io, $key);

        // ------------------------------------------------------------------
        // Step 3 – users: encrypt emails + populate email_hash
        // ------------------------------------------------------------------
        $io->out('<info>Step 3: Encrypting user emails…</info>');
        $this->migrateUsers($db, $io, $key);

        $io->success('Migration complete.');

        return self::CODE_SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function runDdl(\Cake\Database\Connection $db, ConsoleIo $io): void
    {
        // --- Widen first_name / last_name to VARCHAR(255) -------------------
        foreach (['first_name', 'last_name'] as $col) {
            $row = $db->execute(
                "SELECT CHARACTER_MAXIMUM_LENGTH AS len
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME   = 'users'
                   AND COLUMN_NAME  = ?",
                [$col]
            )->fetch('assoc');

            $currentLen = (int)($row['len'] ?? 255);
            if ($currentLen < 255) {
                $db->execute("ALTER TABLE users MODIFY COLUMN {$col} VARCHAR(255) NULL");
                $io->out("  Widened {$col} from VARCHAR({$currentLen}) to VARCHAR(255).");
            } else {
                $io->out("  {$col} is already VARCHAR({$currentLen}), no change.");
            }
        }

        // --- Add email_hash column + index ----------------------------------
        $result = $db->execute(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'users'
               AND COLUMN_NAME  = 'email_hash'"
        )->fetch('assoc');

        if ((int)($result['cnt'] ?? 0) > 0) {
            $io->out('  email_hash column already exists, skipping.');
        } else {
            $db->execute('ALTER TABLE users ADD COLUMN email_hash VARCHAR(64) NULL AFTER email');
            $db->execute('ALTER TABLE users ADD INDEX idx_users_email_hash (email_hash)');
            $io->out('  email_hash column and index created.');
        }
    }

    // -------------------------------------------------------------------------

    private function migrateSubmissions(\Cake\Database\Connection $db, ConsoleIo $io, string $key): void
    {
        // At this point emails in users table are still plain-text, so we can
        // look them up directly.
        $submissions = $db->execute(
            "SELECT id, information_submitter
             FROM submissions
             WHERE information_submitter LIKE '%@%'"
        )->fetchAll('assoc');

        if (empty($submissions)) {
            $io->out('  No submissions with plain-text email found, skipping.');

            return;
        }

        $io->out('  Found ' . count($submissions) . ' submission(s) to migrate.');

        $usersTable = TableRegistry::getTableLocator()->get('Users', [
            'className' => \App\Model\Table\UsersTable::class,
        ]);

        foreach ($submissions as $sub) {
            $email = strtolower(trim((string)$sub['information_submitter']));

            // Look up user by plain-text email (before encryption runs)
            $userRow = $db->execute(
                'SELECT id FROM users WHERE email = ? LIMIT 1',
                [$email]
            )->fetch('assoc');

            if ($userRow) {
                $userId = $userRow['id'];
            } else {
                // Create a placeholder inactive user
                $username = strstr($email, '@', true) ?: $email;
                // Ensure unique username
                $baseUsername = $username;
                $suffix = 0;
                while ($db->execute('SELECT id FROM users WHERE username = ? LIMIT 1', [$username])->fetch('assoc')) {
                    $suffix++;
                    $username = $baseUsername . '_' . $suffix;
                }

                $newUser = $usersTable->newEmptyEntity();
                $newUser->set([
                    'username' => $username,
                    'email'    => $email,
                    'password' => bin2hex(random_bytes(16)),
                    'active'   => false,
                    'role'     => 'user',
                ]);
                // Save bypasses beforeSave because we want plain-text temporarily;
                // the email will be encrypted in Step 3.
                // We bypass this by using direct SQL for the insert.
                $db->execute(
                    'INSERT INTO users (id, username, email, password, active, role, created, modified)
                     VALUES (?, ?, ?, ?, 0, ?, NOW(), NOW())',
                    [
                        \Cake\Utility\Text::uuid(),
                        $username,
                        $email,
                        password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                        'user',
                    ]
                );

                $newRow = $db->execute(
                    'SELECT id FROM users WHERE username = ? LIMIT 1',
                    [$username]
                )->fetch('assoc');

                $userId = $newRow['id'];
                $io->out("  Created placeholder user '{$username}' for {$email}.");
            }

            $db->execute(
                'UPDATE submissions SET information_submitter = ? WHERE id = ?',
                [(string)$userId, $sub['id']]
            );
        }

        $io->out('  Submissions migrated.');
    }

    // -------------------------------------------------------------------------

    /**
     * Minimum base64-encoded length of a Security::encrypt() ciphertext.
     * 64 B HMAC + 16 B IV + ≥16 B payload = 96 B raw → 128 chars base64.
     */
    private const MIN_CIPHER_LEN = 128;

    /**
     * Try to decrypt $value with $key. Returns plain text on success, null on failure.
     */
    private function tryDecrypt(string $value, string $key): ?string
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return null;
        }
        $plain = Security::decrypt($decoded, $key);

        return ($plain !== false && $plain !== null) ? $plain : null;
    }

    /**
     * If $value looks like ciphertext but can't be decrypted with $currentKey,
     * try each legacy key. Returns decrypted plain text, or null if none work.
     */
    private function tryLegacyDecrypt(string $value, string $currentKey): ?string
    {
        // Already decryptable with the current key — not a legacy value.
        if ($this->tryDecrypt($value, $currentKey) !== null) {
            return null;
        }

        foreach (self::LEGACY_KEYS as $legacyKey) {
            $plain = $this->tryDecrypt($value, $legacyKey);
            if ($plain !== null) {
                return $plain;
            }
        }

        return null;
    }

    private function migrateUsers(\Cake\Database\Connection $db, ConsoleIo $io, string $key): void
    {
        // Use raw SQL to avoid triggering beforeSave double-encryption.
        $users = $db->execute(
            "SELECT id, email, first_name, last_name FROM users"
        )->fetchAll('assoc');

        if (empty($users)) {
            $io->out('  No users found, skipping.');

            return;
        }

        $emailCount    = 0;
        $nameCount     = 0;
        $reencryptCount = 0;

        foreach ($users as $user) {
            $updates = [];
            $params  = [];

            // --- email ------------------------------------------------------
            $email = (string)($user['email'] ?? '');

            if (str_contains($email, '@')) {
                // Plain-text email
                $plain     = strtolower(trim($email));
                $updates[] = 'email = ?';
                $params[]  = base64_encode(Security::encrypt($plain, $key));
                $updates[] = 'email_hash = ?';
                $params[]  = hash_hmac('sha256', $plain, $key);
                $emailCount++;
            } elseif ($email !== '' && strlen($email) >= self::MIN_CIPHER_LEN) {
                // Possibly encrypted — check for legacy key mismatch
                $plain = $this->tryLegacyDecrypt($email, $key);
                if ($plain !== null) {
                    $plain     = strtolower(trim($plain));
                    $updates[] = 'email = ?';
                    $params[]  = base64_encode(Security::encrypt($plain, $key));
                    $updates[] = 'email_hash = ?';
                    $params[]  = hash_hmac('sha256', $plain, $key);
                    $reencryptCount++;
                }
            }

            // --- first_name / last_name -------------------------------------
            foreach (['first_name', 'last_name'] as $field) {
                $value = (string)($user[$field] ?? '');
                if ($value === '') {
                    continue;
                }

                $isEncrypted = strlen($value) >= self::MIN_CIPHER_LEN
                    && base64_decode($value, true) !== false;

                if (!$isEncrypted) {
                    // Could be plain text OR a truncated ciphertext (column was
                    // too narrow and silently cut our 128-char output to 50 chars).
                    // Detect truncated ciphertext: valid base64 but too short to
                    // decrypt, and contains no spaces/typical name chars.
                    $isTruncated = base64_decode($value, true) !== false
                        && !preg_match('/[\s\-\'\.]/u', $value)
                        && !preg_match('/^\p{L}[\p{L}\s\-\'\.]*$/u', $value);

                    if ($isTruncated) {
                        // Unrecoverable — zero it out so the UI shows blank
                        // instead of garbage. The user will need to re-enter.
                        $updates[] = "{$field} = NULL";
                        $io->warning("  User {$user['id']}: {$field} appears to be truncated ciphertext — clearing it.");
                    } else {
                        // Plain text — encrypt now
                        $updates[] = "{$field} = ?";
                        $params[]  = base64_encode(Security::encrypt($value, $key));
                        $nameCount++;
                    }
                } else {
                    // Already looks like full ciphertext — check for legacy key mismatch
                    $plain = $this->tryLegacyDecrypt($value, $key);
                    if ($plain !== null) {
                        $updates[] = "{$field} = ?";
                        $params[]  = base64_encode(Security::encrypt($plain, $key));
                        $reencryptCount++;
                    }
                    // If no legacy key works either, leave as-is (unknown key).
                }
            }

            if (empty($updates)) {
                continue;
            }

            $params[] = $user['id'];
            $db->execute(
                'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?',
                $params
            );
        }

        $io->out("  Encrypted {$emailCount} email(s), {$nameCount} name field(s), re-encrypted {$reencryptCount} legacy field(s).");
    }
}
