<?php

namespace yii\redis;

use Yii;
use yii\mq\Message;
use yii\base\InvalidConfigException;

/**
 * The yii\redis\QueueTrait represents the minimum method set of a Redis Queue.
 *
 * It is supposed to be used in a class that implements the [[QueueInterface]] or the [[pubsub\QueueInterface]].
 *
 * @author Jan Was <janek.jan@gmail.com>
 * @since 2.0
 */
trait QueueTrait
{
	const MESSAGE_ID = ':message_id';
	const RESERVED_LIST = ':reserved';
	/**
	 * @var string|yii\redis\Connection Name or a redis connection component to use as storage.
	 */
	public $redis = 'redis';

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->redis)) {
			$this->redis = Yii::$app->getComponent($this->redis);
		} elseif (is_array($this->redis)) {
		}
		if (!($this->redis instanceof Connection)) {
			throw new InvalidConfigException('The redis property must contain a name or a valid yii\redis\Connection component.');
		}
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
			'id'			=> $this->redis->incr($this->id.self::MESSAGE_ID),
			'status'		=> Message::AVAILABLE,
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

	private function peekInternal($list_id, $limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException('When in blocking mode peeking is not available. Use the pull() method.');
		}
		//! @todo implement peeking at other lists, joining, sorting results by date and limiting, remember about settings status after unserialize
		$messages = array();
		foreach($this->redis->lrange($list_id, 0, $limit) as $rawMessage) {
			$message = unserialize($rawMessage);
			$message->status = Message::AVAILABLE;
			$messages[] = $message;
		}
		return $messages;
	}

	public function pullInternal($list_id, $limit=-1, $timeout=null, $blocking=false)
	{
		$messages = array();
		$count = 0;
		if ($timeout !== null) {
			$reserved_list_id = $list_id.self::RESERVED_LIST;
			$now = new \DateTime('', new \DateTimezone('UTC'));
			$future = new \DateTime("+$timeout seconds", new \DateTimezone('UTC'));
		}
		$this->redis->multi();
		while (($limit == -1 || $count < $limit)) {
			if ($blocking!==false) {
				$response = $this->redis->brpop($list_id, (int)$blocking);
				array_shift($response); // brpop will return key name that returned a value
				$rawMessage = array_shift($response);
			} else {
				$rawMessage = $this->redis->rpop($list_id);
			}
			if ($rawMessage === null) {
				break;
			}
			$message = unserialize($rawMessage);
			if ($timeout !== null) {
				$message->reserved_on = $now->format('Y-m-d H:i:s');
				$message->times_out_on = $future->format('Y-m-d H:i:s');
				$message->status = Message::RESERVED;
				$this->redis->lpush($reserved_list_id, serialize($message));
			} else {
				$message->status = Message::AVAILABLE;
			}
			$messages[] = $message;
			$count++;
			//! @todo implement moving messages to :deleted queue (optionally, if configured)
		}
		$this->redis->exec();

		return $messages;
	}

	private function releaseInternal($message_id, $list_id, $delete=false)
	{
		if (!is_array($message_id)) {
			$message_id = array($message_id);
		}
		$message_id = array_flip($message_id);
		$this->redis->multi();
		$reserved_list_id = $list_id.self::RESERVED_LIST;
		$messages = array_reverse($this->redis->lrange($reserved_list_id, 0, -1));
		foreach($messages as $rawMessage) {
			$message = unserialize($rawMessage);
			if (isset($message_id[$message->id])) {
				$this->redis->lrem($reserved_list_id, $rawMessage, -1);
				if (!$delete) {
					$this->redis->lpush($list_id, $rawMessage);
				} else {
					//! @todo implement moving messages to :deleted queue (optionally, if configured)
				}
			}
		}
		$this->redis->exec();
	}

	private function releaseTimedoutInternal($keys)
	{
		$message_ids = array();
		$this->redis->multi();
		foreach($keys as $reserved_list_id) {
			$list_id = substr($reserved_list_id, 0, -strlen(self::RESERVED_LIST));
			$messages = array_reverse($this->redis->lrange($reserved_list_id, 0, -1));
			$now = new \DateTime;
			foreach($messages as $rawMessage) {
				$message = unserialize($rawMessage);
				$times_out_on = new \DateTime($message->times_out_on);
				if ($times_out_on <= $now) {
					$this->redis->lrem($reserved_list_id, $rawMessage, -1);
					$this->redis->lpush($list_id, $rawMessage);
					$message_ids[] = $message->id;
				}
			}
		}
		$this->redis->exec();
		return $message_ids;
	}
}
