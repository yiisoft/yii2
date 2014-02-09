<?php

namespace yii\redis;

use Yii;
use yii\mq;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Saves sent messages and tracks subscriptions using a redis component.
 *
 * When in non-blocking (default) mode, subscriptions are tracked in one hash
 * and their message delivery is handled by creating an extra list for every subscription.
 *
 * In blocking mode, subscriptions are handled by using SUBSCRIBE/UNSUBSCRIBE commands and message are sent
 * using PUBLISH command instead of using separate lists.
 * Peeking and locking is disabled and the receive() method becomes blocking.
 * Before/after send subscription events are not raised.
 */
class Queue extends yii\mq\Queue
{
	const MESSAGE_ID = ':message_id';
	const RESERVED_LIST = ':reserved';
	const SUBSCRIPTIONS_HASH = ':subscriptions';
	const SUBSCRIPTION_LIST_PREFIX = ':subscription:';
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

	/**
	 * @inheritdoc
	 */
	public function send($message, $category=null) {
		$queueMessage = $this->createMessage($message);

        if ($this->beforeSend($queueMessage) !== true) {
			Yii::info(Yii::t('app', "Not sending message '{msg}' to queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), 'nfy');
            return;
        }

		if ($this->blocking) {
			$this->redis->publish($category, serialize($queueMessage));
		} else {
			$this->sendToList($queueMessage, $category);
		}

        $this->afterSend($queueMessage);

		Yii::info(Yii::t('app', "Sent message '{msg}' to queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), 'nfy');
	}

	private function sendToList($queueMessage, $category)
	{
		$subscriptions = $this->redis->hvals($this->id.self::SUBSCRIPTIONS_HASH);

		$this->redis->multi();

		$this->redis->lpush($this->id, serialize($queueMessage));

		foreach($subscriptions as $rawSubscription) {
			$subscription = unserialize($rawSubscription);
			if ($category !== null && !$subscription->matchCategory($category)) {
				continue;
			}
			$subscriptionMessage = clone $queueMessage;
            if ($this->beforeSendSubscription($subscriptionMessage, $subscription->subscriber_id) !== true) {
                continue;
            }

			$this->redis->lpush($this->id.self::SUBSCRIPTION_LIST_PREFIX.$subscription->subscriber_id, serialize($subscriptionMessage));
            
            $this->afterSendSubscription($subscriptionMessage, $subscription->subscriber_id);
		}

		$this->redis->exec();
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE)
	{
		if ($this->blocking) {
			throw new NotSupportedException('When in blocking mode peeking is not available. Use the receive() method.');
		}
		//! @todo implement peeking at other lists, joining, sorting results by date and limiting, remember about settings status after unserialize
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		$messages = array();
		foreach($this->redis->lrange($list_id, 0, $limit) as $rawMessage) {
			$message = unserialize($rawMessage);
			$message->subscriber_id = $subscriber_id;
			$message->status = Message::AVAILABLE;
			$messages[] = $message;
		}
		return $messages;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function reserve($subscriber_id=null, $limit=-1)
	{
		if ($this->blocking) {
			throw new NotSupportedException('When in blocking mode reserving is not available. Use the receive() method.');
		}

		$messages = array();
		$count = 0;
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		$reserved_list_id = $list_id.self::RESERVED_LIST;
		$now = new \DateTime;
		$this->redis->multi();
		while (($limit == -1 || $count < $limit)) {
			if (($rawMessage=$this->redis->rpop($list_id)) === null) {
				break;
			}
			$message = unserialize($rawMessage);
			$message->setAttributes(array(
				'status' => Message::RESERVED,
				'reserved_on' => $now->format('Y-m-d H:i:s'),
				'subscriber_id' => $subscriber_id,
			));
			$this->redis->lpush($reserved_list_id, serialize($message));
			$messages[] = $message;
			$count++;
		}
		$this->redis->exec();

		return $messages;
	}

	/**
	 * @inheritdoc
	 * The result does not include reserved but timed-out messages. @see releaseTimedout().
	 */
	public function receive($subscriber_id=null, $limit=-1)
	{
		$messages = array();
		$count = 0;
		if ($this->blocking) {
			$response = $this->redis->parseResponse('', true);
			if (is_array($response)) {
				$type = array_shift($reponse);
				if ($type == 'message') {
					$channel = array_shift($response);
					$message = array_shift($response);
				} elseif ($type == 'pmessage') {
					$pattern = array_shift($response);
					$channel = array_shift($response);
					$message = array_shift($response);
				}
				if (isset($message)) {
					$messages[] = $message;
				}
			}
			return $messages;
		}
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		while (($limit == -1 || $count < $limit) && ($message=$this->redis->rpop($list_id)) !== null) {
			$message = unserialize($rawMessage);
			$message->subscriber_id = $subscriber_id;
			$message->status = Message::AVAILABLE;
			$messages[] = $message;
			$count++;
			//! @todo implement moving messages to :deleted queue (optionally, if configured)
		}

		return $messages;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id, $subscriber_id=null)
	{
		if ($this->blocking) {
			throw new NotSupportedException('When in blocking mode reserving is not available. Use the receive() method.');
		}
		$this->releaseInternal($message_id, true);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id, $subscriber_id=null)
	{
		if ($this->blocking) {
			throw new NotSupportedException('When in blocking mode reserving is not available. Use the receive() method.');
		}
		$this->releaseInternal($message_id, false);
	}

	private function releaseInternal($message_id, $delete=false)
	{
		if (!is_array($message_id)) {
			$message_id = array($message_id);
		}
		$message_id = array_flip($message_id);
		$this->redis->multi();
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
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

	/**
	 * @inheritdoc
	 */
	public function releaseTimedout()
	{
		$keys = array_merge($this->redis->keys($this->id.self::RESERVED_LIST), $this->redis->keys($this->id.self::SUBSCRIPTION_LIST_PREFIX.'*'.self::RESERVED_LIST));
		$message_ids = array();

		$this->redis->multi();
		foreach($keys as $reserved_list_id) {
			$list_id = substr($reserved_list_id, 0, -strlen(self::RESERVED_LIST));
			$messages = array_reverse($this->redis->lrange($reserved_list_id, 0, -1));
			$now = new \DateTime;
			foreach($messages as $rawMessage) {
				$message = unserialize($rawMessage);
				$reserved_on = new \DateTime($message->reserved_on);
				if ($reserved_on->add(new DateInterval('PT'.$message->timeout.'S')) <= $now) {
					$this->redis->lrem($reserved_list_id, $rawMessage, -1);
					$this->redis->lpush($list_id, $rawMessage);
					$message_ids[] = $message->id;
				}
			}
		}
		$this->redis->exec();
		return $message_ids;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function subscribe($subscriber_id, $label=null, $categories=null, $exceptions=null)
	{
		if ($this->blocking) {
			if ($exceptions !== null) {
				throw new NotSupportedException('Redis queues does not support pattern exceptions in blocking (pubsub) mode.');
			}
			foreach($categories as $category) {
				if (($c=rtrim($category,'*'))!==$category) {
					$this->redis->psubscribe($category);
				} else {
					$this->redis->subscribe($category);
				}
			}
			return;
		}
		$now = new \DateTime('now', new \DateTimezone('UTC'));
		$subscription = new Subscription;
		$subscription->setAttributes(array(
			'subscriber_id'=>$subscriber_id,
			'label'=>$label,
			'categories'=>$categories,
			'exceptions'=>$exceptions !== null ? $exceptions : array(),
			'created_on'=>$now->format('Y-m-d H:i:s'),
		));
		$this->redis->hset($this->id.self::SUBSCRIPTIONS_HASH, $subscriber_id, serialize($subscription));
	}

	/**
	 * @inheritdoc
	 */
	public function unsubscribe($subscriber_id, $permanent=true)
	{
		if ($this->blocking) {
			$this->redis->punsubscribe();
			$this->redis->unsubscribe();
			return;
		}
		$this->redis->hdel($this->id.self::SUBSCRIPTIONS_HASH, $subscriber_id);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function isSubscribed($subscriber_id)
	{
		if ($this->blocking) {
			throw new NotSupportedException('In blocking mode it is not possible to track subscribers.');
			return;
		}
		return $this->redis->hexists($this->id.self::SUBSCRIPTIONS_HASH, $subscriber_id);
	}

	/**
	 * @inheritdoc
	 */
	public function getSubscriptions($subscriber_id=null)
	{
		$subscriptions = array();
		foreach($this->redis->hvals($this->id.self::SUBSCRIPTIONS_HASH) as $rawSubscription) {
			$subscriptions[] = unserialize($rawSubscription);
		}
		return $subscriptions;
	}
}
