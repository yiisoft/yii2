<?php

namespace yii\mq\db;

use Yii;
use yii\mq;
use yii\mq\pubsub;
use yii\mq\db\models;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;

/**
 * Saves sent messages and tracks subscriptions in a database.
 */
class DbPubSubQueue extends pubsub\Queue
{
	use DbQueueTrait;

	/**
	 * @inheritdoc
	 */
	public function put($message, $category=null) {
		return $this->putInternal($message, $category);
	}

	/**
	 * Saves message in the database.
	 * @param DbMessage $queueMessage
	 * @return boolean
	 */
	private function saveMessage(DbMessage $queueMessage, $category=null)
	{
        if (!$queueMessage->save()) {
			Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label}.", array('{msg}' => $queueMessage->body, '{queue_label}' => $this->label)), __METHOD__);
			return false;
		}
		return $this->saveSubscriptionsMessage($queueMessage, $category);
	}

	/**
	 * Saves message the database for all subscriptions.
	 * @param DbMessage $queueMessage
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 * @return boolean
	 */
	private function saveSubscriptionsMessage(DbMessage $queueMessage, $category)
	{
		$subscriptions = models\DbSubscription::find()
			->current()
			->withQueue($this->id)
			->matchingCategory($category)
			->all();
		foreach($subscriptions as $subscription) {
			$subscriptionMessage = clone $queueMessage;
			$subscriptionMessage->subscription_id = $subscription->id;
			$subscriptionMessage->message_id = $queueMessage->id;
            if ($this->beforePutSubscription($subscriptionMessage, $subscription->subscriber_id) !== true) {
                continue;
            }

			if (!$subscriptionMessage->save()) {
				Yii::error(Yii::t('app', "Failed to save message '{msg}' in queue {queue_label} for the subscription {subscription_id}.", array(
					'{msg}' => $queueMessage->body,
					'{queue_label}' => $this->label,
					'{subscription_id}' => $subscription->id,
				)), __METHOD__);
				return false;
			}
            
            $this->afterPutSubscription($subscriptionMessage, $subscription->subscriber_id);
		}
		return true;
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE, $blocking=false)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->withStatus($status)->limit($limit);
		return $this->peekInternal($query, $blocking);
	}

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->available()->limit($limit);
		return $this->pullInternal($query, $timeout, $blocking);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($message_id, $subscriber_id=null)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->reserved(;)
		return $this->deleteInternal($query, $message_id);
	}

	/**
	 * @inheritdoc
	 */
	public function release($message_id, $subscriber_id=null)
	{
		$query = models\DbMessage::find()->withQueue($this->id)->withSubscriber($subscriber_id)->reserved();
		return $this->releaseInternal($query, $message_id);
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
}
