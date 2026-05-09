<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * FileMutex implements mutex "lock" mechanism via local file system files.
 *
 * This component relies on PHP `flock()` function.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'mutex' => [
 *             'class' => 'yii\mutex\FileMutex'
 *         ],
 *     ],
 * ]
 * ```
 *
 * > Note: this component can maintain the locks only for the single web server,
 * > it probably will not suffice in case you are using cloud server solution.
 *
 * > Warning: due to `flock()` function nature this component is unreliable when
 * > using a multithreaded server API like ISAPI.
 *
 * @see Mutex
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class FileMutex extends Mutex
{
    use RetryAcquireTrait;

    /**
     * @var string the directory to store mutex files. You may use [path alias](guide:concept-aliases) here.
     * Defaults to the "mutex" subdirectory under the application runtime path.
     */
    public $mutexPath = '@runtime/mutex';
    /**
     * @var int|null the permission to be set for newly created mutex files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var int the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var bool|null whether file handling should assume a Windows file system.
     * This value will determine how [[releaseLock()]] goes about deleting the lock file.
     * If not set, it will be determined by checking the DIRECTORY_SEPARATOR constant.
     * @since 2.0.16
     */
    public $isWindows;

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
        parent::init();
        $this->mutexPath = Yii::getAlias($this->mutexPath);
        if (!is_dir($this->mutexPath)) {
            FileHelper::createDirectory($this->mutexPath, $this->dirMode, true);
        }
        if ($this->isWindows === null) {
            $this->isWindows = DIRECTORY_SEPARATOR === '\\';
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param int $timeout time (in seconds) to wait for lock to become released.
     * @return bool acquiring result.
     */
    protected function acquireLock($name, $timeout = 0)
    {
        $filePath = $this->getLockFilePath($name);
        return $this->retryAcquire($timeout, function () use ($filePath, $name) {
            $file = fopen($filePath, 'w+');
            if ($file === false) {
                return false;
            }

            if ($this->fileMode !== null) {
                @chmod($filePath, $this->fileMode);
            }

            if (!flock($file, LOCK_EX | LOCK_NB)) {
                fclose($file);
                return false;
            }

            // Under unix we delete the lock file before releasing the related handle. Thus it's possible that we've acquired a lock on
            // a non-existing file here (race condition). We must compare the inode of the lock file handle with the inode of the actual lock file.
            // If they do not match we simply continue the loop since we can assume the inodes will be equal on the next try.
            // Example of race condition without inode-comparison:
            // Script A: locks file
            // Script B: opens file
            // Script A: unlinks and unlocks file
            // Script B: locks handle of *unlinked* file
            // Script C: opens and locks *new* file
            // In this case we would have acquired two locks for the same file path.
            if (DIRECTORY_SEPARATOR !== '\\' && fstat($file)['ino'] !== @fileinode($filePath)) {
                clearstatcache(true, $filePath);
                flock($file, LOCK_UN);
                fclose($file);
                return false;
            }

            $this->_files[$name] = $file;
            return true;
        });
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return bool release result.
     */
    protected function releaseLock($name)
    {
        if (!isset($this->_files[$name])) {
            return false;
        }

        if ($this->isWindows) {
            // Under windows it's not possible to delete a file opened via fopen (either by own or other process).
            // That's why we must first unlock and close the handle and then *try* to delete the lock file.
            flock($this->_files[$name], LOCK_UN);
            fclose($this->_files[$name]);
            @unlink($this->getLockFilePath($name));
        } else {
            // Under unix it's possible to delete a file opened via fopen (either by own or other process).
            // That's why we must unlink (the currently locked) lock file first and then unlock and close the handle.
            unlink($this->getLockFilePath($name));
            flock($this->_files[$name], LOCK_UN);
            fclose($this->_files[$name]);
        }

        unset($this->_files[$name]);
        return true;
    }

    /**
     * Generate path for lock file.
     * @param string $name
     * @return string
     * @since 2.0.10
     */
    protected function getLockFilePath($name)
    {
        return $this->mutexPath . DIRECTORY_SEPARATOR . md5($name) . '.lock';
    }
}
