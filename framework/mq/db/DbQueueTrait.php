<?php

namespace yii\mq\db;

use yii\mq\db\models;
use yii\base\InvalidConfigException;

/**
 * The DbQueueTrait represents the minimum method set of a database Queue.
 *
 * It is supposed to be used in a class that implements the [[QueueInterface]] or the [[pubsub\QueueInterface]].
 *
 * @author Jan Was <janek.jan@gmail.com>
 * @since 2.0
 */
trait DbQueueTrait
{
	/**
	 * @var string|yii\db\Connection Name or a db connection component to use as storage.
	 */
	public $db;

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		} elseif (is_array($this->db)) {
		}
		if (!($this->db instanceof \yii\db\Connection)) {
			throw new InvalidConfigException('The db property must contain a name or a valid yii\db\Connection component.');
		}
		models\DbMessage::$db = models\DbSubscription::$db = models\DbSubscriptionCategory::$db = $this->db;
	}

	/**
	 * Creates an instance of DbMessage model. The passed message body may be modified, @see formatMessage().
	 * This method may be overriden in extending classes.
	 * @param string $body message body
	 * @return models\DbMessage
	 */
	protected function createMessage($body)
	{
		$message = new models\DbMessage;
		$message->setAttributes(array(
			'queue_id'		=> $this->id,
			'sender_id'		=> Yii::$app->hasComponent('user') ? Yii::$app->user->getId() : null,
			'status'		=> Message::AVAILABLE,
			'body'			=> $body,
		), false);
		return $this->formatMessage($message);
	}

	/**
	 * Formats the body of a queue message. This method may be overriden in extending classes.
	 * @param models\DbMessage $message
	 * @return models\DbMessage $message
	 */
	protected function formatMessage($message)
	{
		return $message;
	}

	private function putInternal($message, $category=null)
	{
		$queueMessage = $this->createMessage($message);

        $trx = $queueMessage->getDb()->transaction !== null ? null : $queueMessage->getDb()->beginTransaction();

        if ($this->beforePut($queueMessage) !== true) {
			Yii::trace(Yii::t('app', "Not putting message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }
        
        if (!$this->saveMessage($queueMessage, $category)) {
			if ($trx !== null) {
				$trx->rollback();
			}
            return false;
        }

        $this->afterPut($queueMessage);

		if ($trx !== null) {
			$trx->commit();
		}

		Yii::trace(Yii::t('app', "Put message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);

		return true;
	}

	/**
	 * @throws NotSupportedException
	 */
	private function peekInternal(models\DbMessageQuery $query, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException(Yii::t('app', 'DbQueue does not support blocking.'));
		}
		$pk = models\DbMessage::primaryKey();
		$messages = $query->indexBy($pk[0])->all();
		return models\DbMessage::createMessages($messages);
	}

	private function pullInternal(models\DbMessageQuery $query, $timeout=null, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException(Yii::t('app', 'DbQueue does not support blocking.'));
		}
		$pk = models\DbMessage::primaryKey();
		$trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$messages = $query->indexBy($pk[0])->all();
		if (!empty($messages)) {
			$now = new \DateTime('now', new \DateTimezone('UTC'));
			if ($timeout === null) {
				$attributes = array('status'=>Message::DELETED, 'deleted_on'=>$now->format('Y-m-d H:i:s'));
			} else {
				$future = new \DateTime("+$timeout seconds", new \DateTimezone('UTC'));
				$attributes = array('status'=>Message::RESERVED, 'reserved_on'=>$now->format('Y-m-d H:i:s'), 'times_out_on'=>$future->format('Y-m-d H:i:s'));
			}
			models\DbMessage::updateAll($attributes, ['in', models\DbMessage::primaryKey(), array_keys($messages)]);
		}
		if ($trx !== null) {
			$trx->commit();
		}
		return models\DbMessage::createMessages($messages);
	}

	private function deleteInternal(models\DbMessageQuery $query, $message_id)
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = $query->select($pk)->andWhere(['in',$pk,$message_id])->column();
		$now = new \DateTime('now', new \DateTimezone('UTC'));
		models\DbMessage::updateAll(array('status'=>Message::DELETED, 'deleted_on'=>$now->format('Y-m-d H:i:s')), ['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}

	private function releaseInternal(models\DbMessageQuery $query, $message_id)
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = $query->select($pk)->andWhere(['in',$pk,$message_id])->column();
		models\DbMessage::updateAll(array('status'=>Message::AVAILABLE), ['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}

	/**
	 * Releases timed-out messages.
	 * @return array of released message ids
	 */
	public function releaseTimedout()
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = models\DbMessage::find()->withQueue($this->id)->timedout()->select($pk)->andWhere(['in',$pk,$message_id])->column();
		models\DbMessage::updateAll(array('status'=>Message::AVAILABLE), ['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}

	/**
	 * Removes deleted messages from the storage.
	 * @return array of removed message ids
	 */
	public function removeDeleted()
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = models\DbMessage::find()->withQueue($this->id)->deleted()->select($pk)->column();
		models\DbMessage::deleteAll(['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}
}
