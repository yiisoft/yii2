<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\log\Target;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogTarget extends Target
{
	/**
	 * @var Module
	 */
	public $module;
	public $maxLogFiles = 20;

	public function __construct($module, $config = array())
	{
		parent::__construct($config);
		$this->module = $module;
	}

	/**
	 * Exports log messages to a specific destination.
	 * Child classes must implement this method.
	 */
	public function export()
	{
		$path = Yii::$app->getRuntimePath() . '/debug';
		if (!is_dir($path)) {
			mkdir($path);
		}
		$tag = Yii::$app->getLog()->getTag();
		$file = "$path/$tag.log";
		$data = array();
		foreach ($this->module->panels as $panel) {
			$data[$panel->id] = $panel->save();
		}
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
			/** @var \DirectoryIterator $file */
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
