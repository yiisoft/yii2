<?php

namespace yii\redis;

use Yii;
use yii\mq;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Saves sent messages and tracks subscriptions using a redis component.
 *
 * Subscriptions are tracked in one hash
 * and their message delivery is handled by creating an extra list for every subscription.
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
	public function put($message, $category=null) {
		$queueMessage = $this->createMessage($message);

        if ($this->beforePut($queueMessage) !== true) {
			Yii::info(Yii::t('app', "Not putting message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }

		$this->putToList($queueMessage, $category);

        $this->afterPut($queueMessage);

		Yii::info(Yii::t('app', "Put message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
	}

	private function putToList($queueMessage, $category)
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
            if ($this->beforePutSubscription($subscriptionMessage, $subscription->subscriber_id) !== true) {
                continue;
            }

			$this->redis->lpush($this->id.self::SUBSCRIPTION_LIST_PREFIX.$subscription->subscriber_id, serialize($subscriptionMessage));
            
            $this->afterPutSubscription($subscriptionMessage, $subscription->subscriber_id);
		}

		$this->redis->exec();
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException('When in blocking mode peeking is not available. Use the pull() method.');
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
	 * The result does not include reserved but timed-out messages. @see releaseTimedout().
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false)
	{
		$messages = array();
		$count = 0;
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
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
			$message->subscriber_id = $subscriber_id;
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

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id, $subscriber_id=null)
	{
		$this->releaseInternal($message_id, true);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id, $subscriber_id=null)
	{
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

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function subscribe($subscriber_id, $label=null, $categories=null, $exceptions=null)
	{
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
		$this->redis->hdel($this->id.self::SUBSCRIPTIONS_HASH, $subscriber_id);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function isSubscribed($subscriber_id)
	{
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
