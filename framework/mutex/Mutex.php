<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use yii\base\Component;

/**
 * The Mutex component allows mutual execution of concurrent processes in order to prevent "race conditions".
 *
 * This is achieved by using a "lock" mechanism. Each possibly concurrent thread cooperates by acquiring
 * a lock before accessing the corresponding data.
 *
 * Usage example:
 *
 * ```
 * if ($mutex->acquire($mutexName)) {
 *     // business logic execution
 * } else {
 *     // execution is blocked!
 * }
 * ```
 *
 * This is a base class, which should be extended in order to implement the actual lock mechanism.
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
abstract class Mutex extends Component
{
    /**
     * @var bool whether all locks acquired in this process (i.e. local locks) must be released automatically
     * before finishing script execution. Defaults to true. Setting this property to true means that all locks
     * acquired in this process must be released (regardless of errors or exceptions).
     */
    public $autoRelease = true;

    /**
     * @var string[] names of the locks acquired by the current PHP process.
     */
    private $_locks = [];


    /**
     * Initializes the Mutex component.
     */
    public function init()
    {
        if ($this->autoRelease) {
            $locks = &$this->_locks;
            register_shutdown_function(function () use (&$locks) {
                foreach ($locks as $lock) {
                    $this->release($lock);
                }
            });
        }
    }

    /**
     * Acquires a lock by name.
     * @param string $name of the lock to be acquired. Must be unique.
     * @param int $timeout time (in seconds) to wait for lock to be released. Defaults to zero meaning that method will return
     * false immediately in case lock was already acquired.
     * @return bool lock acquiring result.
     */
    public function acquire($name, $timeout = 0)
    {
        if (!in_array($name, $this->_locks, true) && $this->acquireLock($name, $timeout)) {
            $this->_locks[] = $name;

            return true;
        }

        return false;
    }

    /**
     * Releases acquired lock. This method will return false in case the lock was not found.
     * @param string $name of the lock to be released. This lock must already exist.
     * @return bool lock release result: false in case named lock was not found..
     */
    public function release($name)
    {
        if ($this->releaseLock($name)) {
            $index = array_search($name, $this->_locks);
            if ($index !== false) {
                unset($this->_locks[$index]);
            }

            return true;
        }

        return false;
    }

    /**
     * This method should be extended by a concrete Mutex implementations. Acquires lock by name.
     * @param string $name of the lock to be acquired.
     * @param int $timeout time (in seconds) to wait for the lock to be released.
     * @return bool acquiring result.
     */
    abstract protected function acquireLock($name, $timeout = 0);

    /**
     * This method should be extended by a concrete Mutex implementations. Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return bool release result.
     */
    abstract protected function releaseLock($name);
}
