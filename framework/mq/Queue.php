<?php

namespace yii\mq;

/**
 */
abstract class Queue extends \yii\base\Component implements QueueInterface
{
	/**
	 * @var string $id Id of the queue, required. Should be set to the component id.
	 */
	public $id;
	/**
	 * @var string $label Human readable name of the queue, required.
	 */
	public $label;

	/**
	 * @inheritdoc
	 */
    public function beforePut($message)
	{
		$event = new QueueEvent(['message'=>$message]);
		$this->trigger(self::EVENT_BEFORE_PUT, $event);
		return $event->isValid;
	}
	/**
	 * @inheritdoc
	 */
    public function afterPut($message)
	{
		$this->trigger(self::EVENT_AFTER_PUT, new QueueEvent(['message'=>$message]));
	}
}
