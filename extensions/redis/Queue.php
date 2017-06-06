<?php

namespace yii\redis;

use Yii;
use yii\mq\Message;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Saves sent messages using a redis component.
 */
class Queue extends yii\mq\Queue
{
	use QueueTrait;

	/**
	 * @inheritdoc
	 */
	public function put($message)
	{
		$queueMessage = $this->createMessage($message);

        if ($this->beforePut($queueMessage) !== true) {
			Yii::info(Yii::t('app', "Not putting message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }

		$this->redis->lpush($this->id, serialize($queueMessage));

        $this->afterPut($queueMessage);

		Yii::info(Yii::t('app', "Put message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		return peekInternal($this->id, $limit, $status, $blocking);
	}

	/**
	 * @inheritdoc
	 * The result does not include reserved but timed-out messages. @see releaseTimedout().
	 */
	public function pull($limit=-1, $timeout=null, $blocking=false)
	{
		return $this->pullInternal($this->id, $limit, $timeout, $blocking);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function delete($message_id)
	{
		$this->releaseInternal($message_id, $this->id, true);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function release($message_id)
	{
		$this->releaseInternal($message_id, $this->id, false);
	}

	/**
	 * @inheritdoc
	 */
	public function releaseTimedout()
	{
		$keys = $this->redis->keys($this->id.self::RESERVED_LIST);
		return $this->releaseTimedoutInternal($keys);
	}
}
