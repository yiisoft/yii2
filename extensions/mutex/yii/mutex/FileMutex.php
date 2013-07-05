<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\InvalidConfigException;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class FileMutex extends Mutex
{
	/**
	 * @var string the directory to store mutex files. You may use path alias here.
	 * If not set, it will use the "mutex" subdirectory under the application runtime path.
	 */
	public $mutexPath = '@runtime/mutex';
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
			throw new InvalidConfigException('FileMutex does not have MS Windows operating system support.');
		}
		$this->mutexPath = Yii::getAlias($this->mutexPath);
		if (!is_dir($this->mutexPath)) {
			mkdir($this->mutexPath, 0777, true);
		}
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 */
	protected function acquireLock($name, $timeout = 0)
	{
		$file = fopen($this->mutexPath . '/' . md5($name) . '.lock', 'w+');
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
	protected function releaseLock($name)
	{
		if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
			return false;
		} else {
			fclose($this->_files[$name]);
			unset($this->_files[$name]);
			return true;
		}
	}
}
