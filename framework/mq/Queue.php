<?php

namespace yii\mq;

/**
 * The NfyQueue class acts like the CLogger class. Instead of collecting the messages,
 * it instantly processes them, similar to CLogRouter calling collectLogs on each route on log flush event.
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
	 * @var integer $timeout Number of seconds after which a reserved message is considered timed out and available again.
	 * If null, reserved messages never time out.
	 */
	public $timeout;
	/**
	 * @var boolean $blocking If true, when fetching messages, waits until a new message is sent if there are none in the queue. Does not determine blocking on sending.
	 */
	public $blocking = false;

	/**
	 * @inheritdoc
	 */
    public function beforeSend($message)
	{
		$event = new QueueEvent(['message'=>$message]);
		$this->trigger(self::EVENT_BEFORE_SEND, $event);
		return $event->isValid;
	}
	/**
	 * @inheritdoc
	 */
    public function afterSend($message)
	{
		$this->trigger(self::EVENT_AFTER_SEND, new QueueEvent(['message'=>$message]));
	}
	/**
	 * @inheritdoc
	 */
    public function beforeSendSubscription($message, $subscriber_id)
	{
		$event = new QueueEvent(['message'=>$message, 'subscriber_id'=>$subscriber_id]);
		$this->trigger(self::EVENT_BEFORE_SEND_SUBSCRIPTION, $event);
		return $event->isValid;
	}
	/**
	 * @inheritdoc
	 */
    public function afterSendSubscription($message, $subscriber_id)
	{
		$this->trigger(self::EVENT_AFTER_SEND_SUBSCRIPTION, new QueueEvent(['message'=>$message, 'subscriber_id'=>$subscriber_id]));
	}
}
