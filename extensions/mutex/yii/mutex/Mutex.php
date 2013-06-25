<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\Component;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
abstract class Mutex extends Component
{
	/**
	 * @var boolean whether all locks acquired in this process (i.e. local locks) must be released automagically
	 * before finishing script execution. Defaults to true. Setting this property to true
	 */
	public $autoRelease = true;
	/**
	 * @var string[] names of the locks acquired in the current PHP process.
	 */
	private $_locks = array();


	/**
	 * Initializes the mutex component.
	 */
	public function init()
	{
		if ($this->autoRelease) {
			$referenceHolder = new stdClass();
			$referenceHolder->mutex = &$this;
			$referenceHolder->locks = &$this->_locks;
			register_shutdown_function(function ($ref) {
				foreach ($ref->locks as $lock) {
					$ref->mutex->release($lock);
				}
			}, $referenceHolder);
		}
	}

	/**
	 * Never call this method directly under any circumstances. This method is intended for internal use only.
	 */
	public function shutdownFunction()
	{

	}

	/**
	 * @param string $name of the lock to be acquired. Must be unique.
	 * @param integer $timeout to wait for lock to be released. Defaults to zero meaning that method will return
	 * false immediately in case lock was already acquired.
	 * @return boolean lock acquiring result.
	 */
	public function acquireLock($name, $timeout = 0)
	{
		if ($this->acquire($name, $timeout)) {
			$this->_locks[] = $name;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Release acquired lock.
	 * @param string $name of the lock to be released. This lock must be already created.
	 * @return boolean lock release result.
	 */
	public function releaseLock($name)
	{
		if ($this->release($name)) {
			unset($this->_locks[array_search($name, $this->_locks)]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks whether named lock was already opened.
	 * @param string $name of the lock to be checked. This lock must be already created.
	 * @return boolean|null whether named lock was already opened. Returns `null` value in case concrete
	 * mutex implementation does not support this operation.
	 */
	public function getIsLockAcquired($name)
	{
		if (in_array($name, $this->_locks)) {
			return true;
		} else {
			return $this->getIsAcquired($name);
		}
	}

	/**
	 * Checks whether given lock is local. In other words local lock means that it was opened in the current
	 * PHP process.
	 * @param string $name of the lock to be checked. This lock must be already created.
	 * @return boolean whether named lock was locally acquired.
	 */
	public function getIsLockLocal($name)
	{
		return in_array($name, $this->_locks);
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 */
	abstract protected function acquire($name, $timeout = 0);

	/**
	 * This method should be extended by concrete mutex implementations. Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 */
	abstract protected function release($name);

	/**
	 * This method may optionally be extended by concrete mutex implementations. Checks whether lock has been
	 * already acquired by given name.
	 * @param string $name of the lock to be released.
	 * @return null|boolean whether lock has been already acquired. Returns `null` in case this feature
	 * is not supported by concrete mutex implementation.
	 */
	protected function getIsAcquired($name)
	{
		return null;
	}

	/**
	 * This method should be extended by concrete mutex implementations. Returns whether current mutex
	 * implementation can be used in a distributed environment.
	 * @return boolean whether current mutex implementation can be used in a distributed environment.
	 */
	abstract public function getIsDistributed();
}
