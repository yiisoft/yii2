<?php

namespace yii\mq\db;

use Yii;
use yii\mq;
use yii\mq\db\models;
use yii\base\Exception;
use yii\base\NotSupportedException;

/**
 * Saves sent messages and tracks subscriptions in a database.
 */
class DbQueue extends Queue
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
		if (!($this->db instanceof yii\db\Connection)) {
			throw new InvalidConfigException('The db property must contain a name or a valid yii\db\Connection component.');
		}
		models\DbMessage::$db = models\DbSubscription::$db = models\DbSubscriptionCategory::$db = $this->db;
	}

	/**
	 * Creates an instance of DbMessage model. The passed message body may be modified, @see formatMessage().
	 * This method may be overriden in extending classes.
	 * @param string $body message body
	 * @return DbMessage
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
	 * @param DbMessage $message
	 * @return DbMessage $message
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
			Yii::info(Yii::t('app', "Not sending message '{msg}' to queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return;
        }

		$success = true;

		$subscriptions = models\DbSubscription::find()->current()->withQueue($this->id)->matchingCategory($category)->all();
        
        $trx = $queueMessage->getDb()->transaction !== null ? null : $queueMessage->getDb()->beginTransaction();
        
		// empty($subscriptions) && 
        if (!$queueMessage->save()) {
			Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
            return false;
        }

		foreach($subscriptions as $subscription) {
			$subscriptionMessage = clone $queueMessage;
			$subscriptionMessage->subscription_id = $subscription->id;
			$subscriptionMessage->message_id = $queueMessage->id;
            if ($this->beforeSendSubscription($subscriptionMessage, $subscription->subscriber_id) !== true) {
                continue;
            }

			if (!$subscriptionMessage->save()) {
				Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label} for the subscription {subscription_id}.", array(
					'{msg}' => $queueMessage->body,
					'{queue_label}' => $this->label,
					'{subscription_id}' => $subscription->id,
				)), __METHOD__);
				$success = false;
			}
            
            $this->afterSendSubscription($subscriptionMessage, $subscription->subscriber_id);
		}

        $this->afterSend($queueMessage);

		if ($trx !== null) {
			$trx->commit();
		}

		Yii::info(Yii::t('app', "Sent message '{msg}' to queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);

		return $success;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException(Yii::t('app', 'DbQueue does not support blocking.'));
		}
		$pk = models\DbMessage::primaryKey();
		$messages = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->withStatus($status)->limit($limit)->indexBy($pk[0])->all();
		return models\DbMessage::createMessages($messages);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false)
	{
		if ($blocking) {
			throw new NotSupportedException(Yii::t('app', 'DbQueue does not support blocking.'));
		}
		$pk = models\DbMessage::primaryKey();
		$trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$messages = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->available()->limit($limit)->indexBy($pk[0])->all();
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

	/**
	 * @inheritdoc
	 */
	public function delete($message_id, $subscriber_id=null)
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->reserved()->select($pk)->andWhere(['in',$pk,$message_id])->column();
		$now = new \DateTime('now', new \DateTimezone('UTC'));
		models\DbMessage::updateAll(array('status'=>Message::DELETED, 'deleted_on'=>$now->format('Y-m-d H:i:s')), ['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}

	/**
	 * @inheritdoc
	 */
	public function release($message_id, $subscriber_id=null)
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->reserved()->select($pk)->andWhere(['in',$pk,$message_id])->column();
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
	 * @inheritdoc
	 */
	public function subscribe($subscriber_id, $label=null, $categories=null, $exceptions=null)
	{
		$trx = models\DbSubscription::getDb()->transaction !== null ? null : models\DbSubscription::getDb()->beginTransaction();
        $subscription = models\DbSubscription::find()->withQueue($this->id)->withSubscriber($subscriber_id)->one();
		if ($subscription === null) {
			$subscription = new models\DbSubscription;
			$subscription->setAttributes(array(
				'queue_id' => $this->id,
				'subscriber_id' => $subscriber_id,
				'label' => $label,
			));
		} else {
			$subscription->is_deleted = 0;
			models\DbSubscriptionCategory::deleteAll('subscription_id=:subscription_id', [':subscription_id'=>$subscription->primaryKey]);
		}
		if (!$subscription->save())
			throw new Exception(Yii::t('app', 'Failed to subscribe {subscriber_id} to {queue_label}', array('{subscriber_id}'=>$subscriber_id, '{queue_label}'=>$this->label)));
		$this->saveSubscriptionCategories($categories, $subscription->primaryKey, false);
		$this->saveSubscriptionCategories($exceptions, $subscription->primaryKey, true);
		if ($trx !== null) {
			$trx->commit();
		}
		return true;
	}

	protected function saveSubscriptionCategories($categories, $subscription_id, $are_exceptions=false)
	{
		if ($categories === null)
			return true;
		if (!is_array($categories))
			$categories = array($categories);
		foreach($categories as $category) {
			$subscriptionCategory = new models\DbSubscriptionCategory;
			$subscriptionCategory->setAttributes(array(
				'subscription_id'	=> $subscription_id,
				'category'			=> str_replace('*', '%', $category),
				'is_exception'		=> $are_exceptions ? 1 : 0,
			));
			if (!$subscriptionCategory->save())
				throw new Exception(Yii::t('app', 'Failed to save category {category} for subscription {subscription_id}', array('{category}'=>$category, '{subscription_id}'=>$subscription_id)));
		}
		return true;
	}

	/**
	 * @inheritdoc
	 * @param boolean @permanent if false, the subscription will only be marked as removed and the messages will remain in the storage; if true, everything is removed permanently
	 */
	public function unsubscribe($subscriber_id, $permanent=true)
	{
		$trx = models\DbSubscription::getDb()->transaction !== null ? null : models\DbSubscription::getDb()->beginTransaction();
        $subscription = models\DbSubscription::find()->withQueue($this->id)->withSubscriber($subscriber_id)->one();
		if ($subscription !== null) {
			if ($permanent) {
				$subscription->delete();
			} else {
				$subscription->is_deleted = 1;
				$subscription->update(true, ['is_deleted']);
			}
		}
		if ($trx !== null) {
			$trx->commit();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function isSubscribed($subscriber_id)
	{
        $subscription = models\DbSubscription::find()->current()->withQueue($this->id)->withSubscriber($subscriber_id)->one();
        return $subscription !== null;
	}

	/**
	 * @param mixed $subscriber_id
	 * @return array|models\DbSubscription
	 */
	public function getSubscriptions($subscriber_id=null)
	{
		/** @var $query ActiveQuery */
		$query = models\DbSubscription::find()->current()->withQueue($this->id)->with(['categories']);
		if ($subscriber_id!==null) {
			$dbSubscriptions = $query->andWhere('subscriber_id=:subscriber_id', [':subscriber_id'=>$subscriber_id]);
		}
		$dbSubscriptions = $query->all();
		return models\DbSubscription::createSubscriptions($dbSubscriptions);
	}

	/**
	 * Removes deleted messages from the storage.
	 * @return array of removed message ids
	 */
	public function removeDeleted()
	{
        $trx = models\DbMessage::getDb()->transaction !== null ? null : models\DbMessage::getDb()->beginTransaction();
		$pk = models\DbMessage::primaryKey();
		$message_ids = models\DbMessage::find()->withQueue($this->id)->deleted()->select($pk)->andWhere(['in', $pk, $message_id])->column();
		models\DbMessage::deleteAll(['in', $pk, $message_ids]);
		if ($trx !== null) {
			$trx->commit();
		}
		return $message_ids;
	}
}
