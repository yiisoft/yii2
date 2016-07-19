<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use PDO;
use Yii;
use yii\base\InvalidConfigException;

/**
 * OracleMutex implements mutex "lock" mechanism via Oracle locks.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'db' => [
 *             'class' => 'yii\db\Connection',
 *             'dsn' => 'oci:dbname=LOCAL_XE',
 *              ...
 *         ]
 *         'mutex' => [
 *             'class' => 'yii\mutex\OracleMutex',
 *              ...
 *         ],
 *     ],
 * ]
 * ```
 *
 * @see Mutex
 *
 * @author Alexander Zlakomanov <zlakomanoff@gmail.com>
 * @since 1.0
 */
class OracleMutex extends DbMutex
{
    /**
     * @var string driver name.
     */
    public $driverName = 'oci';

    /**
     * @var array available lock modes
     */
    const AVAILABLE_LOCK_MODES = [
        'X_MODE',
        'NL_MODE',
        'S_MODE',
        'SX_MODE',
        'SS_MODE',
        'SSX_MODE'
    ];

    /**
     * Initializes Oracle specific mutex component implementation.
     * @throws InvalidConfigException if [[db]] is not Oracle connection.
     */
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== $this->driverName) {
            throw new InvalidConfigException('In order to use OracleMutex connection must be configured to use Oracle database.');
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @param string $lockMode lock mode.
     * @param boolean $releaseOnCommit release on commit.
     * @return boolean acquiring result.
     * @see http://docs.oracle.com/cd/B19306_01/appdev.102/b14258/d_lock.htm
     */
    protected function acquireLock($name, $timeout = 0, $lockMode = 'X_MODE', $releaseOnCommit = false)
    {
        $lockStatus = null;

        /** clean vars before using */
        $releaseOnCommit = ($releaseOnCommit === true) ? 'TRUE' : 'FALSE';
        $timeout = abs((int)$timeout);

        if(!in_array($lockMode, self::AVAILABLE_LOCK_MODES)){
            throw new InvalidConfigException('Wrong lock mode');
        }

        /** inside pl/sql scopes pdo binding not working correctly :(  */
        $this->db
            ->createCommand(
                'DECLARE
    handle VARCHAR2(128);
BEGIN
    DBMS_LOCK.ALLOCATE_UNIQUE(:name, handle);
    :lockStatus := DBMS_LOCK.REQUEST(handle, DBMS_LOCK.' . $lockMode . ', ' . $timeout . ', ' . $releaseOnCommit . ');
END;',
                [':name' => $name])
            ->bindParam(':lockStatus', $lockStatus, PDO::PARAM_INT, 1)
            ->execute();

        return ($lockStatus === 0 or $lockStatus === '0') ? true : false;
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return boolean release result.
     * @see http://docs.oracle.com/cd/B19306_01/appdev.102/b14258/d_lock.htm
     */
    protected function releaseLock($name)
    {
        $releaseStatus = null;
        $this->db
            ->createCommand(
                'DECLARE
    handle VARCHAR2(128);
BEGIN
    DBMS_LOCK.ALLOCATE_UNIQUE(:name, handle);
    :result := DBMS_LOCK.RELEASE(handle);
END;',
                [':name' => $name])
            ->bindParam(':result', $releaseStatus, PDO::PARAM_INT, 1)
            ->execute();

        return ($releaseStatus == 0 or $releaseStatus === '0') ? true : false;
    }
}
