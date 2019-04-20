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
 * FileTarget 在文件中记录日志消息。
 *
 * 日志文件通过 [[logFile]] 指定。
 * 如果日志文件的大小超过 [[maxFileSize]]（以千字节为单位），将启用日志文件轮换。
 * 会使用 '.1' 后缀文件名来重命名当前日志文件。
 * 所有现有的日志文件向后移动一个位置，即 '.1' 到 '.2'，'.2' 到 '.3'，依此类推。
 * 属性 [[maxLogFiles]] 指定要保留的历史文件数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileTarget extends Target
{
    /**
     * @var string 日志文件路径或[路径别名]（概念别名）。如果未设置，它将使用“@runtime/logs/app.log”文件。
     * 如果不存在，将自动创建包含日志文件的目录。
     */
    public $logFile;
    /**
     * @var bool 当日志文件达到某个 [[maxFileSize|maximum size]] 时是否应该轮换日志文件。
     * 默认情况下已启用日志轮换。
     * 当您在服务器上配置了用于日志轮换的外部工具时，允许您通过此属性禁用它。
     * @since 2.0.3
     */
    public $enableRotation = true;
    /**
     * @var int 最大日志文件大小，以千字节为单位。默认为 10240，表示 10MB。
     */
    public $maxFileSize = 10240; // 以KB为单位
    /**
     * @var int 用于轮换的日志文件数。默认为 5。
     */
    public $maxLogFiles = 5;
    /**
     * @var int 为新创建的日志文件设置的权限。
     * 该值将由 PHP chmod() 函数使用。没有默认值。
     * 如果未设置，权限将由当前环境确定。
     */
    public $fileMode;
    /**
     * @var int 为新创建的目录设置的权限。
     * 该值将由 PHP chmod() 函数使用。没有默认值。
     * 默认为 0775，表示目录所有者和组可读写，其他用户只读。
     *
     */
    public $dirMode = 0775;
    /**
     * @var bool 是否通过复制和截断来轮换日志文件，而不是通过重命名文件。
     * 默认为 'true'，以便与日志跟踪程序更兼容。
     * 并且在 Windows 系统中不能很好地重命名已打开的文件。
     * 但是，通过重命名进行轮换要快一点。
     *
     * 在PHP文档中 [comment by Martin Pelletier](http://www.php.net/manual/en/function.rename.php#102274)
     * 中描述了 Windows
     * 系统中 [rename()](http://www.php.net/manual/en/function.rename.php) 函数不能与某些进程打开的文件一起使用的问题。
     * 您可以通过将 rotateByCopy
     * 设置为 `true` 解决此问题。
     */
    public $rotateByCopy = true;


    /**
     * 初始化路由。
     * 路由管理器创建路由后调用此方法。
     */
    public function init()
    {
        parent::init();
        if ($this->logFile === null) {
            $this->logFile = Yii::$app->getRuntimePath() . '/logs/app.log';
        } else {
            $this->logFile = Yii::getAlias($this->logFile);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }

    /**
     * 将日志消息写入文件。
     * 从版本 2.0.14 开始，如果无法导出日志，此方法将抛出 LogRuntimeException 异常。
     * @throws InvalidConfigException 如果无法打开日志文件进行写入
     * @throws LogRuntimeException 如果无法将完整的日志写入文件
     */
    public function export()
    {
        $logPath = dirname($this->logFile);
        FileHelper::createDirectory($logPath, $this->dirMode, true);

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
            @flock($fp, LOCK_UN);
            @fclose($fp);
            $this->rotateFiles();
            $writeResult = @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
            if ($writeResult === false) {
                $error = error_get_last();
                throw new LogRuntimeException("Unable to export log through file!: {$error['message']}");
            }
            $textSize = strlen($text);
            if ($writeResult < $textSize) {
                throw new LogRuntimeException("Unable to export whole log through file! Wrote $writeResult out of $textSize bytes.");
            }
        } else {
            $writeResult = @fwrite($fp, $text);
            if ($writeResult === false) {
                $error = error_get_last();
                throw new LogRuntimeException("Unable to export log through file!: {$error['message']}");
            }
            $textSize = strlen($text);
            if ($writeResult < $textSize) {
                throw new LogRuntimeException("Unable to export whole log through file! Wrote $writeResult out of $textSize bytes.");
            }
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     * 轮换日志文件。
     */
    protected function rotateFiles()
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                    continue;
                }
                $newFile = $this->logFile . '.' . ($i + 1);
                $this->rotateByCopy ? $this->rotateByCopy($rotateFile, $newFile) : $this->rotateByRename($rotateFile, $newFile);
                if ($i === 0) {
                    $this->clearLogFile($rotateFile);
                }
            }
        }
    }

    /***
     * 清除日志文件而不关闭任何其他进程打开句柄
     * @param string $rotateFile
     */
    private function clearLogFile($rotateFile)
    {
        if ($filePointer = @fopen($rotateFile, 'a')) {
            @ftruncate($filePointer, 0);
            @fclose($filePointer);
        }
    }

    /***
     * 将轮换的文件复制到新文件中
     * @param string $rotateFile
     * @param string $newFile
     */
    private function rotateByCopy($rotateFile, $newFile)
    {
        @copy($rotateFile, $newFile);
        if ($this->fileMode !== null) {
            @chmod($newFile, $this->fileMode);
        }
    }

    /**
     * 将轮换的文件重命名为新文件
     * @param string $rotateFile
     * @param string $newFile
     */
    private function rotateByRename($rotateFile, $newFile)
    {
        @rename($rotateFile, $newFile);
    }
}
