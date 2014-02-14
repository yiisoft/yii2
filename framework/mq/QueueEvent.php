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
	 * @var yii\mq\Message queue message being put in
	 */
	public $message;
	/**
	 * @var string id of subscriber, null if putting in a general queue
	 */
	public $subscriber_id;
	/**
	 * @var boolean whether to continue putting a message. Event handlers of
	 * [[yii\mq\Queue::EVENT_BEFORE_PUT]] or [[yii\mq\Queue::EVENT_BEFORE_PUT_SUBSCRIPTION]]
	 * may set this property to decide whether to continue putting or not.
	 */
	public $isValid = true;
}
