<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * FileTarget records log messages in a file.
 *
 * The log file is specified via [[logFile]]. If the size of the log file exceeds
 * [[maxFileSize]] (in kilo-bytes), a rotation will be performed, which renames
 * the current log file by suffixing the file name with '.1'. All existing log
 * files are moved backwards by one place, i.e., '.2' to '.3', '.1' to '.2', and so on.
 * The property [[maxLogFiles]] specifies how many history files to keep.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileTarget extends Target
{
    /**
     * @var string log file path or [path alias](guide:concept-aliases). If not set, it will use the "@runtime/logs/app.log" file.
     * The directory containing the log files will be automatically created if not existing.
     */
    public $logFile;
    /**
     * @var bool whether log files should be rotated when they reach a certain [[maxFileSize|maximum size]].
     * Log rotation is enabled by default. This property allows you to disable it, when you have configured
     * an external tools for log rotation on your server.
     * @since 2.0.3
     */
    public $enableRotation = true;
    /**
     * @var int maximum log file size, in kilo-bytes. Defaults to 10240, meaning 10MB.
     */
    public $maxFileSize = 10240; // in KB
    /**
     * @var int number of log files used for rotation. Defaults to 5.
     */
    public $maxLogFiles = 5;
    /**
     * @var int the permission to be set for newly created log files.
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
     * @var bool Whether to rotate log files by copy and truncate in contrast to rotation by
     * renaming files. Defaults to `true` to be more compatible with log tailers and is windows
     * systems which do not play well with rename on open files. Rotation by renaming however is
     * a bit faster.
     *
     * The problem with windows systems where the [rename()](http://www.php.net/manual/en/function.rename.php)
     * function does not work with files that are opened by some process is described in a
     * [comment by Martin Pelletier](http://www.php.net/manual/en/function.rename.php#102274) in
     * the PHP documentation. By setting rotateByCopy to `true` you can work
     * around this problem.
     */
    public $rotateByCopy = true;
    /**
     * @var bool whether to compress rotation files with gzip. Defaults to `false`.
     * @since 2.0.13
     */
    public $compressRotatedFiles = false;


    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();
        if ($this->logFile === null) {
            $this->logFile = Yii::$app->getRuntimePath() . '/logs/app.log';
        } else {
            $this->logFile = Yii::getAlias($this->logFile);
        }
        $logPath = dirname($this->logFile);
        if (!is_dir($logPath)) {
            FileHelper::createDirectory($logPath, $this->dirMode, true);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }

    /**
     * Writes log messages to a file.
     * @throws InvalidConfigException if unable to open the log file for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            $rotateTarget = $file . '.' . ($i + 1);
            if (!is_file($rotateFile)) {
                if ($this->compressRotatedFiles && is_file("$rotateFile.gz")) {
                    $rotateFile = "$rotateFile.gz";
                    $rotateTarget = "$rotateTarget.gz";
                } else {
                    continue;
                }
            }
            if ($i === $this->maxLogFiles) {
                // suppress errors because it's possible multiple processes enter into this section
                @unlink($rotateFile);
            } else {
                $this->rotateFile($rotateFile, $rotateTarget);
            }
        }
    }

    /**
     * Rotate a single file
     * @param string $file the file to rotate.
     * @param string $target the target file name.
     * @since 2.0.13
     */
    protected function rotateFile($file, $target)
    {
        // suppress errors because it's possible multiple processes enter into this section
        if ($this->rotateByCopy) {
            @copy($file, $target);
            if ($fp = @fopen($file, 'a')) {
                @ftruncate($fp, 0);
                @fclose($fp);
            }
            if ($this->fileMode !== null) {
                @chmod($target, $this->fileMode);
            }
        } else {
            @rename($file, $target);
        }
        if ($this->compressRotatedFiles) {
            $this->compressFile($target);
        }
    }

    /**
     * Compresses a file with gzip and renames it by appending `.gz` to the filename
     * @param string $filename the file to compress.
     * @return string the name of the compressed file.
     * @since 2.0.13
     */
    protected function compressFile($filename)
    {
        if (substr($filename, -3, 3) === '.gz') {
            return $filename;
        }
        $gzFilename = $filename . '.gz';
        $f = fopen($filename, 'rb');
        $gz = gzopen($gzFilename, 'wb9');
        while (!feof($f)) {
            gzwrite($gz, fread($f, 8192));
        }
        fclose($f);
        gzclose($gz);
        @unlink($filename);
        return $gzFilename;
    }
}
