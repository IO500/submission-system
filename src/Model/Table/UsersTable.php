<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;

/**
 * UsersTable with PII encryption at rest.
 *
 * Encrypted fields (via App\Database\Type\EncryptedType)
 * -------------------------------------------------------
 * - email      : AES-256 ciphertext; companion email_hash (HMAC-SHA256) for DB lookups.
 * - first_name : AES-256 ciphertext; no hash column needed (never queried by value).
 * - last_name  : AES-256 ciphertext; same as first_name.
 *
 * The EncryptedType is registered in Application::bootstrap() and declared on
 * the schema columns here, so encrypt/decrypt happens transparently at the SQL
 * binding layer for ALL queries — no dependency on which code path loads the table.
 *
 * Remaining hooks
 * ---------------
 * - beforeSave : normalise email, compute email_hash from plain text BEFORE the
 *               type encrypts the value for the DB write.
 * - beforeFind : rewrite WHERE email = ? clauses to WHERE email_hash = hmac(?).
 * - buildRules : uniqueness check via email_hash.
 */
class UsersTable extends \CakeDC\Users\Model\Table\UsersTable
{
    /**
     * Declare encrypted column types so the EncryptedType handles
     * transparent encrypt/decrypt at the SQL binding layer.
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();

        $schema->setColumnType('email', 'encrypted');
        $schema->setColumnType('first_name', 'encrypted');
        $schema->setColumnType('last_name', 'encrypted');

        return $schema;
    }

    private function encKey(): string
    {
        return (string)Configure::read('App.emailEncryptionKey');
    }

    // -------------------------------------------------------------------------
    // beforeSave – normalise email and compute email_hash from plain text.
    // The EncryptedType will then encrypt the email value when binding the SQL.
    // -------------------------------------------------------------------------

    public function beforeSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options): void
    {
        $email = $entity->get('email');
        if ($email === null || !str_contains((string)$email, '@')) {
            return;
        }

        $normalized = strtolower(trim((string)$email));
        $entity->set('email', $normalized);
        $entity->set('email_hash', hash_hmac('sha256', $normalized, $this->encKey()));
        $entity->setDirty('email', true);
        $entity->setDirty('email_hash', true);
    }

    // -------------------------------------------------------------------------
    // beforeFind – rewrite WHERE email = 'plain@text' → WHERE email_hash = hmac
    // -------------------------------------------------------------------------

    public function beforeFind(EventInterface $event, Query $query, \ArrayObject $options, bool $primary): void
    {
        $where = $query->clause('where');
        if ($where === null) {
            return;
        }

        $key = $this->encKey();

        $where->iterateParts(function ($condition, &$condKey) use ($key) {
            if (!($condition instanceof \Cake\Database\Expression\ComparisonExpression)) {
                return $condition;
            }

            $field     = $condition->getField();
            $value     = $condition->getValue();
            $bareField = str_replace('Users.', '', (string)$field);

            if (($bareField === 'email' || $field === 'email') &&
                is_string($value) && str_contains($value, '@')
            ) {
                return new \Cake\Database\Expression\ComparisonExpression(
                    str_replace('email', 'email_hash', (string)$field),
                    hash_hmac('sha256', strtolower($value), $key),
                    'string',
                    $condition->getOperator()
                );
            }

            return $condition;
        });
    }

    // -------------------------------------------------------------------------
    // buildRules – uniqueness check via email_hash
    // -------------------------------------------------------------------------

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $originalFlag = $this->isValidateEmail;
        $this->isValidateEmail = false;

        parent::buildRules($rules);

        $this->isValidateEmail = $originalFlag;

        if ($originalFlag) {
            $key = $this->encKey();
            $rules->add(
                function (EntityInterface $entity) use ($key) {
                    $email = $entity->get('email');
                    if (!$email || !str_contains((string)$email, '@')) {
                        return true;
                    }
                    $hash = hash_hmac('sha256', strtolower((string)$email), $key);

                    return $this->find()->where(['email_hash' => $hash])->count() === 0;
                },
                '_isUniqueEmail',
                [
                    'errorField' => 'email',
                    'message' => __d('cake_d_c/users', 'Email already exists'),
                ]
            );
        }

        return $rules;
    }
}
