<?php

namespace yii\mq;

/**
 * The Message class represents one message sent to/received from a queue.
 */
class Message
{
	const AVAILABLE = 0;
	const RESERVED = 1;
	const DELETED = 2;
	/**
	 * @var string $id Unique identifier of the message
	 */
	public $id;
	/**
	 * @var string $created_on Date and time when the message has been created, in Y-m-d H:i:s format
	 */
	public $created_on;
	/**
	 * @var string $sender_id Unique identifier of the user who created the message
	 */
	public $sender_id;
	/**
	 * @var string $message_id Unique identifier of the main message in a queue
	 * if the current one has been delivered to a subscription
	 */
	public $message_id;
	/**
	 * @var integer $status One of self::AVAILABLE, self::RESERVED or self::DELETED
	 */
	public $status;
	/**
	 * @var string $subscriber_id Unique identifier of the subscriber (user) to which this message has been delivered to
	 */
	public $subscriber_id;
	/**
	 * @var integer $timeout Number of seconds after which when the current message is reserved it becomes available again
	 */
	public $timeout;
	/**
	 * @var string $reserved_on Date and time when the message has been reserved, in Y-m-d H:i:s format
	 */
	public $reserved_on;
	/**
	 * @var string $deleted_on Date and time when the message has been deleted, in Y-m-d H:i:s format
	 */
	public $deleted_on;
	/**
	 * @var string $body Message body
	 */
	public $body;

	public function __sleep()
	{
		$attributes = array('id', 'created_on', 'sender_id', 'timeout', 'body');
		if ($this->status == self::RESERVED) $attributes[] = 'reserved_on';
		if ($this->status == self::DELETED) $attributes[] = 'deleted_on';
		return $attributes;
	}

	/**
	 * Sets the properties values in a massive way.
	 * @param array $values properties values (name=>value) to be set.
	 */
	public function setAttributes($values)
	{
		if(!is_array($values))
			return;
		foreach($values as $name=>$value) {
			$this->$name=$value;
		}
	}
}
