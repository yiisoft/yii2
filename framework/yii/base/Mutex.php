<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Mutex component represents mutual exclusion technique which is used to prevent running same code block
 * in two or more processes at the same time (i.e. critical section). This component can be configured
 * as follows:
 *
 * ```php
 * return array(
 *     'components' => array(
 *         'mutex' => array(
 *             'class' => 'yii\base\Mutex',
 *             'mutexFile' => '@common/runtime/mutex.bin',
 *         ),
 *         // ... other application components
 *     ),
 * );
 * ```
 *
 * Usage sample and common use case (locks nesting also supported and handled properly):
 *
 * ```php
 * class FooController extends \yii\console\Controller
 * {
 *     public function actionBar()
 *     {
 *         // 120 seconds timeout
 *         if (Yii::$app->getComponent('mutex')->acquireLock('fooBar', 120)) {
 *             echo "Started working on task...\n";
 *
 *             // ... do some stuff that should be executed only by single PHP process
 *
 *             echo "Done!\n";
 *             Yii::$app->getComponent('mutex')->releaseLock('fooBar');
 *             return 0;
 *         } else {
 *             echo "Already working on this task!\n";
 *             return -1;
 *         }
 *     }
 * }
 * ```
 *
 * @see http://en.wikipedia.org/wiki/Mutual_exclusion
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class Mutex extends Component
{
	/**
	 * @var string the mutex data file. This can be either a file path or path alias.
	 */
	public $mutexFile = '@app/runtime/mutex';
	/**
	 * @var string[] lock names acquired in the current process.
	 */
	private $_locks = array();
	/**
	 * @var resource[] file lock handles.
	 */
	private $_fileLocks = array();


	/**
	 * Initializes the mutex.
	 * @throws InvalidConfigException if the mutex file does not exist and cannot be created.
	 */
	public function init()
	{
		$this->mutexFile = Yii::getAlias($this->mutexFile);
		if (!is_file($this->mutexFile) && !@touch($this->mutexFile)) {
			throw new InvalidConfigException("The mutex file does not exist and cannot be created: {$this->mutexFile}");
		}
	}

	/**
	 * Acquires lock by given unique name/ID.
	 * @param string $name of the lock to be created. Must be unique.
	 * @param integer $expire time of the lock.
	 * @return boolean false if lock cannot be acquired (e.g. other process already got it).
	 */
	public function acquireLock($name, $expire = 0)
	{
		$result = false;
		if ($this->acquireFileLock($name)) {
			$locks = @unserialize(@file_get_contents($this->mutexFile));
			if (empty($locks)) {
				$locks = array();
			}
			if (!isset($locks[$name]) || $locks[$name][0] > 0 && $locks[$name][0] + $locks[$name][1] <= microtime(true)) {
				$locks[$name] = array($expire, microtime(true));
				$result = (boolean)file_put_contents($this->mutexFile, serialize($locks));
				$this->_locks[] = $name;
			}
		}
		$this->releaseFileLock($name);
		return $result;
	}

	/**
	 * Releases lock by given unique name/ID.
	 * @param null|string $name of the lock be release. Must be unique.
	 * @return boolean returns false if lock cannot be released.
	 * @throws InvalidCallException in case locks to be released are not found.
	 * @throws InvalidCallException in case name was provided while acquiring happened in the same process.
	 */
	public function releaseLock($name = null)
	{
		if ($name === null && ($name = array_pop($this->_locks)) === null) {
			throw new InvalidCallException('Locks to be released not found. Ensure you have acquired lock before calling this method.');
		} elseif (in_array($name, $this->_locks)) {
			throw new InvalidCallException('You must not specify name when releasing lock acquired in the same process.');
		}
		$result = false;
		if ($this->acquireFileLock($name)) {
			$locks = @unserialize(@file_get_contents($this->mutexFile));
			if (isset($locks[$name])) {
				unset($locks[$name]);
				$result = (boolean)file_put_contents($this->mutexFile, serialize($locks));
			}
		}
		$this->releaseFileLock($name);
		return $result;
	}

	/**
	 * Acquires file lock.
	 * @param string $name of the new lock.
	 * @return boolean result of the locking.
	 */
	protected function acquireFileLock($name)
	{
		$this->_fileLocks[$name] = fopen($this->mutexFile . '.' . md5($name) . '.lock', 'a+b');
		return flock($this->_fileLocks[$name], LOCK_EX);
	}

	/**
	 * Releases file lock.
	 * @param string $name of the existing lock.
	 */
	protected function releaseFileLock($name)
	{
		flock($this->_fileLocks[$name], LOCK_UN);
		fclose($this->_fileLocks[$name]);
		@unlink($this->mutexFile . '.' . md5($name) . '.lock');
	}
}
