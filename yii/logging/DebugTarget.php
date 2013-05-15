<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DebugTarget extends Target
{
	public $maxLogFiles = 20;

	/**
	 * Exports log messages to a specific destination.
	 * Child classes must implement this method.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 */
	public function export($messages)
	{
		$path = Yii::$app->getRuntimePath() . '/debug';
		if (!is_dir($path)) {
			mkdir($path);
		}
		$file = $path . '/' . Yii::getLogger()->getTag() . '.log';
		$data = array(
			'messages' => $messages,
			'_SERVER' => $_SERVER,
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => empty($_FILES) ? array() : $_FILES,
			'_SESSION' => empty($_SESSION) ? array() : $_SESSION,
			'memory' => memory_get_peak_usage(),
			'time' => microtime(true) - YII_BEGIN_TIME,
		);
		file_put_contents($file, json_encode($data));
	}

	/**
	 * Processes the given log messages.
	 * This method will filter the given messages with [[levels]] and [[categories]].
	 * And if requested, it will also export the filtering result to specific medium (e.g. email).
	 * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
	 * of each message.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function collect($messages, $final)
	{
		if (Yii::$app->getModule('debug', false) !== null) {
			return;
		}
		$this->messages = array_merge($this->messages, $this->filterMessages($messages));
		if ($final) {
			$this->export($this->messages);
			$this->gc();
		}
	}

	protected function gc()
	{
		if (mt_rand(0, 10000) > 100) {
			return;
		}
		$iterator = new \DirectoryIterator(Yii::$app->getRuntimePath() . '/debug');
		$files = array();
		foreach ($iterator as $file) {
			if (preg_match('/^[\d\-]+\.log$/', $file->getFileName()) && $file->isFile()) {
				$files[] = $file->getPathname();
			}
		}
		sort($files);
		if (count($files) > $this->maxLogFiles) {
			$n = count($files) - $this->maxLogFiles;
			foreach ($files as $i => $file) {
				if ($i < $n) {
					unlink($file);
				} else {
					break;
				}
			}
		}
	}
}
