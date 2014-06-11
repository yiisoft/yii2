<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * FileMutex implements mutex "lock" mechanism via local file system files.
 * This component relies on PHP `flock()` function.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'mutex'=> [
 *             'class' => 'yii\mutex\FileMutex'
 *         ],
 *     ],
 * ]
 * ```
 *
 * Note: this component can maintain the locks only for the single web server,
 * it probably will not suffice to your in case you are using cloud server solution.
 *
 * Warning: due to `flock()` function nature this component is unreliable when
 * using a multithreaded server API like ISAPI.
 *
 * @see Mutex
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class FileMutex extends Mutex
{
    /**
     * @var string the directory to store mutex files. You may use path alias here.
     * Defaults to the "mutex" subdirectory under the application runtime path.
     */
    public $mutexPath = '@runtime/mutex';
    /**
     * @var integer the permission to be set for newly created mutex files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var resource[] stores all opened lock files. Keys are lock names and values are file handles.
     */
    private $_files = [];

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
            FileHelper::createDirectory($this->mutexPath, $this->dirMode, true);
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @return boolean acquiring result.
     */
    protected function acquireLock($name, $timeout = 0)
    {
        $fileName = $this->mutexPath . '/' . md5($name) . '.lock';
        $file = fopen($fileName, 'w+');
        if ($file === false) {
            return false;
        }
        if ($this->fileMode !== null) {
            @chmod($fileName, $this->fileMode);
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
     * Releases lock by given name.
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
