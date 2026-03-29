<?php
declare(strict_types=1);

namespace App\Authentication\Resolver;

use Authentication\Identifier\Resolver\OrmResolver;
use Cake\Core\Configure;

/**
 * ORM resolver that transparently substitutes email lookups with email_hash
 * so that the users table never needs to be searched by plain-text email.
 */
class EncryptedEmailOrmResolver extends OrmResolver
{
    /**
     * @inheritDoc
     */
    public function find(array $conditions, $type = self::TYPE_AND)
    {
        $key = Configure::read('App.emailEncryptionKey');

        $transformed = [];
        foreach ($conditions as $field => $value) {
            if ($field === 'email' && is_string($value) && str_contains($value, '@')) {
                $transformed['email_hash'] = hash_hmac('sha256', strtolower($value), $key);
            } else {
                $transformed[$field] = $value;
            }
        }

        return parent::find($transformed, $type);
    }
}
