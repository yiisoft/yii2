<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex\base;

use Yii;
use yii\base\InvalidCallException;

/**
 * Mutex helper class provides implementation of the mutual exclusion technique which is used to prevent
 * running same code block in two or more processes at the same time (critical section). This class can
 * be used as follows:
 *
 * ```php
 * class FooController extends \yii\console\Controller
 * {
 *     public function actionBar()
 *     {
 *         if (\yii\mutex\Mutex::acquireLock('fooBar', 20)) {
 *             echo "Working on Bar task...\n";
 *
 *             // ... do some stuff that should be executed only by a single PHP process
 *
 *             echo "Done!\n";
 *             \yii\mutex\Mutex::releaseLock();
 *             return 0;
 *         } else {
 *             echo "Already working on Bar task. Try to rerun this action later.\n";
 *             return -1;
 *         }
 *     }
 * }
 * ```
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class Mutex
{
	/**
	 * @var string prefix of the files containing mutex data. This can be either a file path or path alias.
	 * Default value `'@app/runtime/mutex'` means that mutex data file and mutex data file lock will be located
	 * in `'@app/runtime/mutex.c4ca4238a0b923820dcc509a6f75849b.bin'` and
	 * `'@app/runtime/mutex.c4ca4238a0b923820dcc509a6f75849b.lock'` files respectively.
	 */
	public static $lockFilePrefix = '@app/runtime/mutex';
	/**
	 * @var string[] lock names acquired in the current process.
	 */
	private static $_locks = array();
	/**
	 * @var resource[] file lock handles.
	 */
	private static $_files = array();


	/**
	 * Acquires lock by given name. If lock was already acquired by an another process this method will return false.
	 * If another process hanged without releasing lock this method will continue to return false for amount of
	 * seconds specified in timeout parameter.
	 * @param string $name of the lock to be created. Must be unique.
	 * @param integer $timeout value.
	 * @return boolean false if lock cannot be acquired (i.e. other process already got it).
	 */
	public static function acquireLock($name, $timeout = 0)
	{
		$result = false;
		if (static::acquireFileLock($name)) {
			$file = Yii::getAlias(static::$lockFilePrefix) . '.' . md5($name) . '.bin';
			$data = @unserialize(@file_get_contents($file));
			if (empty($data) || !is_array($data) || $data[0] > 0 && $data[0] + $data[1] <= microtime(true)) {
				$data = array($timeout, microtime(true));
				$result = (boolean)file_put_contents($file, serialize($data));
				self::$_locks[] = $name;
			}
		}
		static::releaseFileLock($name);
		return $result;
	}

	/**
	 * Releases lock by given name.
	 * @param null|string $name of the lock be release.
	 * @throws InvalidCallException if no locks to be released are exists.
	 * @throws InvalidCallException if lock name was provided when acquiring happened in the same process.
	 * @see acquireLock
	 */
	public static function releaseLock($name = null)
	{
		if ($name === null && ($name = array_pop(self::$_locks)) === null) {
			throw new InvalidCallException('Locks to be released not found. Ensure you have acquired lock before calling this method.');
		} elseif (in_array($name, self::$_locks)) {
			throw new InvalidCallException('You must not specify name when releasing lock acquired in the same process.');
		}
		if (static::acquireFileLock($name)) {
			unlink(Yii::getAlias(static::$lockFilePrefix) . '.' . md5($name) . '.bin');
		}
		static::releaseFileLock($name);
	}

	/**
	 * Acquires file lock by given unique name. This is needed to prevent lock data file from being written
	 * by two or more processes at the same time.
	 * @param string $name of the lock to be created.
	 * @return boolean false in case lock is already acquired.
	 */
	protected static function acquireFileLock($name)
	{
		self::$_files[$name] = fopen(Yii::getAlias(static::$lockFilePrefix) . '.' . md5($name) . '.lock', 'a+b');
		return flock(self::$_files[$name], LOCK_EX);
	}

	/**
	 * Releases file lock.
	 * @param string $name of the lock to be removed.
	 * @see acquireFileLock
	 */
	protected static function releaseFileLock($name)
	{
		flock(self::$_files[$name], LOCK_UN);
		fclose(self::$_files[$name]);
		unset(self::$_files[$name]);
		@unlink(Yii::getAlias(static::$lockFilePrefix) . '.' . md5($name) . '.lock');
	}
}
