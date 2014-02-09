<?php

namespace yii\mq;

use yii\base\Event;

/**
 * QueueEvent represents the event parameter used for a queue event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 */
class QueueEvent extends Event
{

	/**
	 * @var yii\mq\Message queue message being send
	 */
	public $message;
	/**
	 * @var string id of subscriber, null if sending to a general queue
	 */
	public $subscriber_id;
	/**
	 * @var boolean whether to continue sending a message. Event handlers of
	 * [[yii\mq\Queue::EVENT_BEFORE_SEND]] or [[yii\mq\Queue::EVENT_BEFORE_SEND_SUBSCRIPTION]]
	 * may set this property to decide whether to continue send or not.
	 */
	public $isValid = true;
}
