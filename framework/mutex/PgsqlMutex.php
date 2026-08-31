<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\mutex;

use yii\base\InvalidConfigException;

/**
 * PgsqlMutex implements mutex "lock" mechanism via PgSQL locks.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'db' => [
 *             'class' => 'yii\db\Connection',
 *             'dsn' => 'pgsql:host=127.0.0.1;dbname=demo',
 *         ]
 *         'mutex' => [
 *             'class' => 'yii\mutex\PgsqlMutex',
 *         ],
 *     ],
 * ]
 * ```
 *
 * @see Mutex
 *
 * @author nineinchnick <janek.jan@gmail.com>
 * @since 2.0.8
 */
class PgsqlMutex extends DbMutex
{
    use RetryAcquireTrait;


    /**
     * Initializes PgSQL specific mutex component implementation.
     * @throws InvalidConfigException if [[db]] is not PgSQL connection.
     */
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== 'pgsql') {
            throw new InvalidConfigException('In order to use PgsqlMutex connection must be configured to use PgSQL database.');
        }
    }

    /**
     * Converts a string into two 32 bit integer keys using the first 8 bytes of the SHA1 hash function.
     * @param string $name
     * @return array contains two 32 bit integer keys
     */
    private function getKeysFromName($name)
    {
        $keys = unpack('N2', substr(sha1($name, true), 0, 8));

        // PostgreSQL advisory locks accept two signed 4-byte integers, while unpack('N')
        // produces unsigned values. Convert values above the signed range accordingly.
        return array_values(array_map(static function ($key) {
            return $key > 0x7FFFFFFF ? $key - 0x100000000 : $key;
        }, $keys));
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param int $timeout time (in seconds) to wait for lock to become released.
     * @return bool acquiring result.
     * @see https://www.postgresql.org/docs/9.0/functions-admin.html
     */
    protected function acquireLock($name, $timeout = 0)
    {
        list($key1, $key2) = $this->getKeysFromName($name);

        return $this->retryAcquire($timeout, function () use ($key1, $key2) {
            return $this->db->useMaster(function ($db) use ($key1, $key2) {
                /** @var \yii\db\Connection $db */
                return (bool) $db->createCommand(
                    'SELECT pg_try_advisory_lock(:key1, :key2)',
                    [':key1' => $key1, ':key2' => $key2]
                )->queryScalar();
            });
        });
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return bool release result.
     * @see https://www.postgresql.org/docs/9.0/functions-admin.html
     */
    protected function releaseLock($name)
    {
        list($key1, $key2) = $this->getKeysFromName($name);
        return $this->db->useMaster(function ($db) use ($key1, $key2) {
            /** @var \yii\db\Connection $db */
            return (bool) $db->createCommand(
                'SELECT pg_advisory_unlock(:key1, :key2)',
                [':key1' => $key1, ':key2' => $key2]
            )->queryScalar();
        });
    }
}
