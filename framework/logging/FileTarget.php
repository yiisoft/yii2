<?php
/**
 * FileTarget class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * FileTarget records log messages in files.
 *
 * The log files are stored under [[logPath]] and their name
 * is specified by [[logFile]]. If the size of the log file exceeds
 * [[maxFileSize]] (in kilo-bytes), a rotation will be performed,
 * which renames the current log file by suffixing the file name
 * with '.1'. All existing log files are moved backwards one place,
 * i.e., '.2' to '.3', '.1' to '.2', and so on. The property
 * [[maxLogFiles]] specifies how many files to keep.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileTarget extends Target
{
	/**
	 * @var integer maximum log file size, in kilo-bytes. Defaults to 1024, meaning 1MB.
	 */
	public $maxFileSize = 1024; // in KB
	/**
	 * @var integer number of log files used for rotation. Defaults to 5.
	 */
	public $maxLogFiles = 5;
	/**
	 * @var string directory storing log files. Defaults to the application runtime path.
	 */
	public $logPath;
	/**
	 * @var string log file name. Defaults to 'application.log'.
	 */
	public $logFile = 'application.log';


	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		parent::init();
		if ($this->logPath === null) {
			$this->logPath = \Yii::$application->getRuntimePath();
		}
		if (!is_dir($this->logPath) || !is_writable($this->logPath)) {
			throw new \yii\base\Exception("Directory '{$this->logPath}' does not exist or is not writable.");
		}
		if ($this->maxLogFiles < 1) {
			$this->maxLogFiles = 1;
		}
		if ($this->maxFileSize < 1) {
			$this->maxFileSize = 1;
		}
	}

	/**
	 * Sends log [[messages]] to specified email addresses.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function exportMessages($final)
	{
		$logFile = $this->logPath . DIRECTORY_SEPARATOR . $this->logFile;
		if (@filesize($logFile) > $this->maxFileSize * 1024) {
			$this->rotateFiles();
		}
		$messages = array();
		foreach ($this->messages as $message) {
			$messages[] = $this->formatMessage($message);
		}
		@file_put_contents($logFile, implode('', $messages), FILE_APPEND | LOCK_EX);
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file = $this->logPath . DIRECTORY_SEPARATOR . $this->logFile;
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
