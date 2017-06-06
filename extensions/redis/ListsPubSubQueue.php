<?php

namespace yii\redis;

use Yii;
use yii\mq\Message;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Saves sent messages and tracks subscriptions using a redis component.
 *
 * Subscriptions are tracked in one hash
 * and their message delivery is handled by creating an extra list for every subscription.
 */
class Queue extends yii\mq\pubsub\Queue
{
	use QueueTrait;

	const SUBSCRIPTIONS_HASH = ':subscriptions';
	const SUBSCRIPTION_LIST_PREFIX = ':subscription:';

	/**
	 * @inheritdoc
	 */
	public function put($message, $category=null)
	{
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
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		return peekInternal($list_id, $limit, $status, $blocking);
	}

	/**
	 * @inheritdoc
	 * The result does not include reserved but timed-out messages. @see releaseTimedout().
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false)
	{
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		return $this->pullInternal($list_id, $limit, $timeout, $blocking);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id, $subscriber_id=null)
	{
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		$this->releaseInternal($message_id, $list_id, true);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id, $subscriber_id=null)
	{
		$list_id = $this->id.($subscriber_id === null ? '' : self::SUBSCRIPTION_LIST_PREFIX.$subscriber_id);
		$this->releaseInternal($message_id, $list_id, false);
	}

	/**
	 * @inheritdoc
	 */
	public function releaseTimedout()
	{
		$keys = array_merge($this->redis->keys($this->id.self::RESERVED_LIST), $this->redis->keys($this->id.self::SUBSCRIPTION_LIST_PREFIX.'*'.self::RESERVED_LIST));
		return $this->releaseTimedoutInternal($keys);
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
