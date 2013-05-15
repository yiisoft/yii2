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
			'globals' => $GLOBALS,
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
		$this->messages = array_merge($this->messages, $this->filterMessages($messages));
		if ($final) {
			$this->export($this->messages);
		}
	}
}
