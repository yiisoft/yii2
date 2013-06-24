<?php

namespace yii\mutex;

use Yii;
use yii\base\Component;

abstract class Mutex extends Component
{
	public $autoRelease = true;

	private $_locks = array();


	public function init()
	{
		if ($this->autoRelease) {
			register_shutdown_function(array($this, 'shutdownFunction'));
		}
	}

	/**
	 * NEVER CALL THIS METHOD UNDER ANY CIRCUMSTANCES
	 */
	public function shutdownFunction()
	{
		foreach ($this->_locks as $lock) {
			$this->release($lock);
		}
	}

	public function acquireLock($name, $timeout = 0)
	{
		if ($this->acquire($name, $timeout)) {
			$this->_locks[] = $name;
			return true;
		} else {
			return false;
		}
	}

	public function releaseLock($name)
	{
		if ($this->release($name)) {
			unset($this->_locks[array_search($name, $this->_locks)]);
			return true;
		} else {
			return false;
		}
	}

	public function getIsLockAcquired($name)
	{
		if (in_array($name, $this->_locks)) {
			return true;
		} else {
			return $this->getIsAcquired($name);
		}
	}

	public function getIsLockLocal($name)
	{
		return in_array($name, $this->_locks);
	}

	abstract protected function acquire($name, $timeout = 0);

	abstract protected function release($name);

	protected function getIsAcquired($name)
	{
		return null;
	}

	abstract public function getIsDistributed();
}
