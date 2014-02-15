<?php

namespace yii\mq\db;

use Yii;
use yii\mq;
use yii\mq\db\models;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;

/**
 * Saves sent messages in a database.
 */
class DbQueue extends Queue
{
	use DbQueueTrait;

	/**
	 * @inheritdoc
	 */
	public function put($message) {
		return $this->putInternal($message);
	}

	/**
	 * Saves message in the database.
	 * @param DbMessage $queueMessage
	 * @return boolean
	 */
	private function saveMessage(DbMessage $queueMessage)
	{
        if (!$queueMessage->save()) {
			Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
			return false;
		}
		return true;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->withStatus($status)->limit($limit);
		return $this->peekInternal($query, $blocking);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function pull($limit=-1, $timeout=null, $blocking=false)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->available()->limit($limit);
		return $this->pullInternal($query, $timeout, $blocking);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($message_id)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->reserved();
		return $this->deleteInternal($query, $message_id);
	}

	/**
	 * @inheritdoc
	 */
	public function release($message_id)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->reserved();
		return $this->releaseInternal($query, $message_id);
	}
}
