<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

use Yii;
use yii\helpers\FileHelper;

/**
 * FileTarget records profiling messages in a file specified via [[filename]].
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'profiler' => [
 *         'targets' => [
 *             [
 *                 '__class' => yii\profile\FileTarget::class,
 *                 //'filename' => '@runtime/profiling/{date}-{time}.txt',
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 3.0.0
 */
class FileTarget extends Target
{
    /**
     * @var string file path or [path alias](guide:concept-aliases). File name may contain the placeholders,
     * which will be replaced by computed values. The supported placeholders are:
     *
     * - '{ts}' - profiling completion timestamp.
     * - '{date}' - profiling completion date in format 'ymd'.
     * - '{time}' - profiling completion time in format 'His'.
     *
     * The directory containing the file will be automatically created if not existing.
     * If target file is already exist it will be overridden.
     */
    public $filename = '@runtime/profiling/{date}-{time}.txt';
    /**
     * @var int the permission to be set for newly created files.
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
     * {@inheritdoc}
     */
    public function export(array $messages)
    {
        $memoryPeakUsage = memory_get_peak_usage();
        $totalTime = microtime(true) - YII_BEGIN_TIME;
        $text = "Total processing time: {$totalTime} ms; Peak memory: {$memoryPeakUsage} B. \n\n";

        $text .= implode("\n", array_map([$this, 'formatMessage'], $messages));

        $filename = $this->resolveFilename();
        if (file_exists($filename)) {
            unlink($filename);
        } else {
            $filePath = dirname($filename);
            if (!is_dir($filePath)) {
                FileHelper::createDirectory($filePath, $this->dirMode, true);
            }
        }
        file_put_contents($filename, $text);
    }

    /**
     * Resolves value of [[filename]] processing path alias and placeholders.
     * @return string actual target filename.
     */
    protected function resolveFilename()
    {
        $filename = Yii::getAlias($this->filename);

        return preg_replace_callback('/{\\w+}/', function ($matches) {
            switch ($matches[0]) {
                case '{ts}':
                    return time();
                case '{date}':
                    return gmdate('ymd');
                case '{time}':
                    return gmdate('His');
            }
            return $matches[0];
        }, $filename);
    }

    /**
     * Formats a profiling message for display as a string.
     * @param array $message the profiling message to be formatted.
     * The message structure follows that in [[Profiler::$messages]].
     * @return string the formatted message.
     */
    protected function formatMessage(array $message)
    {
        return date('Y-m-d H:i:s', $message['beginTime']) . " [{$message['duration']} ms][{$message['memoryDiff']} B][{$message['category']}] {$message['token']}";
    }
}