<?php

namespace yii\redis;

use Yii;
use yii\mq\Message;
use yii\base\NotSupportedException;

/**
 * Saves sent messages and tracks subscriptions using a redis component.
 *
 * Subscriptions are handled by using SUBSCRIBE/UNSUBSCRIBE commands and message are sent
 * using PUBLISH command instead of using separate lists.
 * Peeking and locking is disabled and the pull() method becomes always blocking.
 * Before/after put subscription events are not raised.
 */
class PubSubQueue extends ListsPubSubQueue
{
	/**
	 * @inheritdoc
	 */
	public function put($message, $category=null) {
		$queueMessage = $this->createMessage($message);

        if ($this->beforePut($queueMessage) !== true) {
			Yii::info(Yii::t('app', "Not putting message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }

		$this->redis->publish($category, serialize($queueMessage));

        $this->afterPut($queueMessage);

		Yii::info(Yii::t('app', "Put message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		throw new NotSupportedException('Peeking not available. Use the pull() method.');
	}

	/**
	 * @inheritdoc
	 * The result does not include reserved but timed-out messages. @see releaseTimedout().
	 * @throws NotSupportedException
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false)
	{
		if ($blocking==false) {
			throw new NotSupportedException('Non-blocking mode not supported. Set the $blocking argument to true.');
		}
		if ($timeout!==null) {
			throw new NotSupportedException('When in PubSub mode reserving is not available.');
		}
		$messages = array();
		$response = $this->redis->parseResponse('', true);
		if (is_array($response)) {
			$type = array_shift($reponse);
			if ($type == 'message') {
				// channel
				array_shift($response);
				$messages[] = array_shift($response);
			} elseif ($type == 'pmessage') {
				// pattern
				array_shift($response);
				// channel
				array_shift($response);
				$messages[] = array_shift($response);
			}
		}
		return $messages;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id, $subscriber_id=null)
	{
		throw new NotSupportedException('When in PubSub mode reserving is not available.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id, $subscriber_id=null)
	{
		throw new NotSupportedException('When in PubSub mode reserving is not available.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function releaseTimedout()
	{
		throw new NotSupportedException('When in PubSub mode reserving is not available.');
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function subscribe($subscriber_id, $label=null, $categories=null, $exceptions=null)
	{
		if ($exceptions !== null) {
			throw new NotSupportedException('Redis queues does not support pattern exceptions in PubSub mode.');
		}
		foreach($categories as $category) {
			if (($c=rtrim($category,'*'))!==$category) {
				$this->redis->psubscribe($category);
			} else {
				$this->redis->subscribe($category);
			}
		}
		// call the parent method to track subscriptions
		return parent::subscribe($subscriber_id, $label, $categories, $exceptions);
	}

	/**
	 * @inheritdoc
	 */
	public function unsubscribe($subscriber_id, $permanent=true)
	{
		$this->redis->punsubscribe();
		$this->redis->unsubscribe();
		// call the parent method to track subscriptions
		return parent::unsubscribe($subscriber_id, $permanent);
	}
}
