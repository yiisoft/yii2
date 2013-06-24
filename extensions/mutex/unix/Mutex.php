<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex\unix;

use Yii;
use yii\base\InvalidConfigException;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class Mutex extends \yii\mutex\Mutex
{
	/**
	 * @var resource[] stores all opened lock files. Keys are lock names and values are file handles.
	 */
	private $_files = array();


	/**
	 * Initializes mutex component implementation dedicated for UNIX, GNU/Linux, Mac OS X, and other UNIX-like
	 * operating systems.
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		if (stripos(php_uname('s'), 'win') === 0) {
			throw new InvalidConfigException('');
		}
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 */
	protected function acquire($name, $timeout = 0)
	{
		$file = fopen(Yii::$app->getRuntimePath() . '/mutex.' . md5($name) . '.lock', 'w+');
		if ($file === false) {
			return false;
		}
		$waitTime = 0;
		while (!flock($file, LOCK_EX | LOCK_NB)) {
			$waitTime++;
			if ($waitTime > $timeout) {
				fclose($file);
				return false;
			}
			sleep(1);
		}
		$this->_files[$name] = $file;
		return true;
	}

	/**
	 * This method should be extended by concrete mutex implementations. Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 */
	protected function release($name)
	{
		if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
			return false;
		} else {
			fclose($this->_files[$name]);
			unset($this->_files[$name]);
			return true;
		}
	}

	/**
	 * This method may optionally be extended by concrete mutex implementations. Checks whether lock has been
	 * already acquired by given name.
	 * @param string $name of the lock to be released.
	 * @return null|boolean whether lock has been already acquired. Returns `null` in case this feature
	 * is not supported by concrete mutex implementation.
	 */
	protected function getIsAcquired($name)
	{
		return false;
	}

	/**
	 * This method should be extended by concrete mutex implementations. Returns whether current mutex
	 * implementation can be used in a distributed environment.
	 * @return boolean whether current mutex implementation can be used in a distributed environment.
	 */
	public function getIsDistributed()
	{
		return false;
	}
}
