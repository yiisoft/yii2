<?php

namespace yii\mq;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Sends and receives messages using System V message queues.
 */
class SysVQueue extends Queue
{
	const MSG_MAXSIZE = 1024;
	/**
	 * @var integer Number representing the current queue, obtained by ftok(), used by msg_* functions family.
	 */
	private $_key;
	/**
	 * @var resource A handle obtained using msg_get_queue().
	 */
	private $_queue;
	/**
	 * @var integer New queues filesystem permissions, defaults to 0666, @see msg_get_queue().
	 */
	public $permissions = 0666;

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();
		if (strlen($this->id)!==1) {
			throw new InvalidConfigException(Yii::t('app', 'Queue id must be exactly a one character.'));
		}
	}

	/**
	 * Return a number representing the current queue.
	 * @return integer
	 */
	private function getKey()
	{
		if ($this->_key === null) {
			$this->_key = ftok(__FILE__, $this->id);
		}
		return $this->_key;
	}
	private function getQueue()
	{
		if ($this->_queue === null) {
			$this->_queue = msg_get_queue($this->getKey(), $this->permissions);
		}
		return $this->_queue;
	}
	/**
	 * Creates an instance of Message model. The passed message body may be modified, @see formatMessage().
	 * This method may be overriden in extending classes.
	 * @param string $body message body
	 * @return Message
	 */
	protected function createMessage($body)
	{
		$now = new \DateTime('now', new \DateTimezone('UTC'));
		$message = new Message;
		$message->setAttributes(array(
			'created_on'	=> $now->format('Y-m-d H:i:s'),
			'sender_id'		=> Yii::$app->hasComponent('user') ? Yii::$app->user->getId() : null,
			'body'			=> $body,
		));
		return $this->formatMessage($message);
	}

	/**
	 * Formats the body of a queue message. This method may be overriden in extending classes.
	 * @param Message $message
	 * @return Message $message
	 */
	protected function formatMessage($message)
	{
		return $message;
	}

	/**
	 * @inheritdoc
	 */
	public function put($message) {
		$queueMessage = $this->createMessage($message);

        if ($this->beforePut($queueMessage) !== true) {
			Yii::info(Yii::t('app', "Not putting message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }

		$success = msg_send($this->getQueue(), 1, $queueMessage, true, false, $errorcode);
        if (!$success) {
			Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
			if ($errorcode === MSG_EAGAIN) {
				Yii::error(Yii::t('app', "Queue {queue_label} is full.", array('{queue_label}' => $this->label)), __METHOD__);
			}
            return false;
        }

        $this->afterPut($queueMessage);

		Yii::info(Yii::t('app', "Put message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($limit=-1, $status=Message::AVAILABLE)
	{
		throw new NotSupportedException('Not implemented. System V queues does not support peeking. Use the pull() method.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function pull($limit=-1, $timeout=null, $blocking=false)
	{
		if ($timeout !== null) {
			throw new NotSupportedException('Not implemented. System V queues does not support reserving messages.');
		}
		$flags = $blocking ? 0 : MSG_IPC_NOWAIT;
		$messages = array();
		$count = 0;
		while (($limit == -1 || $count < $limit) && (msg_receive($this->getQueue(), 0, $msgtype, self::MSG_MAXSIZE, $message, true, $flags, $errorcode))) {
			$message->status = Message::AVAILABLE;
			$messages[] = $message;
			$count++;
		}

		return $messages;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id)
	{
		throw new NotSupportedException('Not implemented. System V queues does not support reserving messages.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id)
	{
		throw new NotSupportedException('Not implemented. System V queues does not support reserving messages.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function releaseTimedout()
	{
		throw new NotSupportedException('Not implemented. System V queues does not support reserving messages.');
	}
}
