<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use Yii;
use yii\base\InvalidConfigException;

/**
 * FileTarget records log messages in a file.
 *
 * The log file is specified via [[logFile]]. If the size of the log file exceeds
 * [[maxFileSize]] (in kilo-bytes), a rotation will be performed, which renames
 * the current log file by suffixing the file name with '.1'. All existing log
 * files are moved backwards by one place, i.e., '.2' to '.3', '.1' to '.2', and so on.
 * The property [[maxLogFiles]] specifies how many files to keep.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileTarget extends Target
{
	/**
	 * @var string log file path or path alias. If not set, it will use the "runtime/logs/app.log" file.
	 * The directory containing the log files will be automatically created if not existing.
	 */
	public $logFile;
	/**
	 * @var integer maximum log file size, in kilo-bytes. Defaults to 10240, meaning 10MB.
	 */
	public $maxFileSize = 10240; // in KB
	/**
	 * @var integer number of log files used for rotation. Defaults to 5.
	 */
	public $maxLogFiles = 5;


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
			@mkdir($logPath, 0777, true);
		}
		if ($this->maxLogFiles < 1) {
			$this->maxLogFiles = 1;
		}
		if ($this->maxFileSize < 1) {
			$this->maxFileSize = 1;
		}
	}

	/**
	 * Sends log messages to specified email addresses.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 * @throws InvalidConfigException if unable to open the log file for writing
	 */
	public function export($messages)
	{
		$text = '';
		foreach ($messages as $message) {
			$text .= $this->formatMessage($message);
		}
		if (($fp = @fopen($this->logFile, 'a')) === false) {
			throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
		}
		@flock($fp, LOCK_EX);
		if (@filesize($this->logFile) > $this->maxFileSize * 1024) {
			$this->rotateFiles();
			@flock($fp, LOCK_UN);
			@fclose($fp);
			@file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
		} else {
			@fwrite($fp, $text);
			@flock($fp, LOCK_UN);
			@fclose($fp);
		}
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file = $this->logFile;
		for ($i = $this->maxLogFiles; $i > 0; --$i) {
			$rotateFile = $file . '.' . $i;
			if (is_file($rotateFile)) {
				// suppress errors because it's possible multiple processes enter into this section
				if ($i === $this->maxLogFiles) {
					@unlink($rotateFile);
				} else {
					@rename($rotateFile, $file . '.' . ($i + 1));
				}
			}
		}
		if (is_file($file)) {
			@rename($file, $file . '.1'); // suppress errors because it's possible multiple processes enter into this section
		}
	}
}
