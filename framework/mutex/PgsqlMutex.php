<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

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
     * Converts a string into two 16 bit integer keys using the SHA1 hash function.
     * @param string $name
     * @return array contains two 16 bit integer keys
     */
    private function getKeysFromName($name)
    {
        return array_values(unpack('n2', sha1($name, true)));
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @return boolean acquiring result.
     * @see http://www.postgresql.org/docs/9.0/static/functions-admin.html
     */
    protected function acquireLock($name, $timeout = 0)
    {
        if ($timeout !== 0) {
            throw new InvalidParamException('PgsqlMutex does not support timeout.');
        }
        list($key1, $key2) = $this->getKeysFromName($name);
        return (bool) $this->db
            ->createCommand('SELECT pg_try_advisory_lock(:key1, :key2)', [':key1' => $key1, ':key2' => $key2])
            ->queryScalar();
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return boolean release result.
     * @see http://www.postgresql.org/docs/9.0/static/functions-admin.html
     */
    protected function releaseLock($name)
    {
        list($key1, $key2) = $this->getKeysFromName($name);
        return (bool) $this->db
            ->createCommand('SELECT pg_advisory_unlock(:key1, :key2)', [':key1' => $key1, ':key2' => $key2])
            ->queryScalar();
    }
}
