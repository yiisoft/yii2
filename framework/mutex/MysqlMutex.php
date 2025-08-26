<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\mutex;

use yii\base\InvalidConfigException;
use yii\db\Expression;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'db' => [
 *             'class' => 'yii\db\Connection',
 *             'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
 *         ]
 *         'mutex' => [
 *             'class' => 'yii\mutex\MysqlMutex',
 *         ],
 *     ],
 * ]
 * ```
 *
 * @see Mutex
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class MysqlMutex extends DbMutex
{
    /**
     * @var Expression|string|null prefix value. If null (by default) then connection's current database name is used.
     * @since 2.0.47
     */
    public $keyPrefix = null;


    /**
     * Initializes MySQL specific mutex component implementation.
     * @throws InvalidConfigException if [[db]] is not MySQL connection.
     */
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== 'mysql') {
            throw new InvalidConfigException('In order to use MysqlMutex connection must be configured to use MySQL database.');
        }
        if ($this->keyPrefix === null) {
            $this->keyPrefix = new Expression('DATABASE()');
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param int $timeout time (in seconds) to wait for lock to become released.
     * @return bool acquiring result.
     * @see https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_get-lock
     */
    protected function acquireLock($name, $timeout = 0)
    {
        return $this->db->useMaster(function ($db) use ($name, $timeout) {
            /** @var \yii\db\Connection $db */
            $nameData = $this->prepareName();
            return (bool)$db->createCommand(
                'SELECT GET_LOCK(' . $nameData[0] . ', :timeout), :prefix',
                array_merge(
                    [':name' => $this->hashLockName($name), ':timeout' => $timeout, ':prefix' => $this->keyPrefix],
                    $nameData[1]
                )
            )->queryScalar();
        });
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return bool release result.
     * @see https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_release-lock
     */
    protected function releaseLock($name)
    {
        return $this->db->useMaster(function ($db) use ($name) {
            /** @var \yii\db\Connection $db */
            $nameData = $this->prepareName();
            return (bool)$db->createCommand(
                'SELECT RELEASE_LOCK(' . $nameData[0] . '), :prefix',
                array_merge(
                    [':name' => $this->hashLockName($name), ':prefix' => $this->keyPrefix],
                    $nameData[1]
                )
            )->queryScalar();
        });
    }

    /**
     * Prepare lock name
     * @return array expression and params
     * @since 2.0.48
     */
    protected function prepareName()
    {
        $params = [];
        $expression = 'SUBSTRING(CONCAT(:prefix, :name), 1, 64)';
        if ($this->keyPrefix instanceof Expression) {
            $expression = strtr($expression, [':prefix' => $this->keyPrefix->expression]);
            $params = $this->keyPrefix->params;
        }
        return [$expression, $params];
    }

    /**
     * Generate hash for lock name to avoid exceeding lock name length limit.
     *
     * @param string $name
     * @return string
     * @since 2.0.16
     * @see https://github.com/yiisoft/yii2/pull/16836
     */
    protected function hashLockName($name)
    {
        return sha1($name);
    }
}
