<?php

namespace yii\mq\db\models;

use Yii;
use yii\mq;

/**
 * This is the model class for table "{{tbl_subscriptions}}".
 *
 * @property integer $id
 * @property integer $queue_id
 * @property string $label
 * @property integer $subscriber_id
 * @property string $created_on
 * @property boolean $is_deleted
 *
 * The followings are the available model relations:
 * @property DbMessage[] $messages
 * @property Users $subscriber
 * @property DbSubscriptionCategory[] $categories
 */
class DbSubscription extends \yii\db\ActiveRecord
{
	/**
	 * @var yii\db\Connection allows overriding the default connection used
	 */
	public static $db;

	/**
	 * @inheritdoc
	 */
	public static function getDb()
	{
		if (self::$db === null) {
			self::$db = \Yii::$app->getDb();
		}
		return self::$db;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%tbl_subscriptions}}';
	}

	public static function createQuery()
	{
		return new DbSubscriptionQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['queue_id', 'subscriber_id'], 'required', 'except'=>'search'],
			['subscriber_id', 'number', 'integerOnly'=>true],
			['is_deleted', 'boolean'],
			['label', 'safe'],
		];
	}

	public function getMessages()
	{
		return $this->hasMany(DbMessage::className(), ['id' => 'subscription_id']);
	}

	public function getSubscriber()
	{
		//! @todo how to resolve this?
		$userClass = Yii::$app->getModule('nfy')->userClass;
		return $this->hasOne($userClass, ['id' => 'subscriber_id'])->from($userClass::tableName().' subscriber');
	}

	public function getCategories()
	{
		return $this->hasMany(DbSubscriptionCategory::className(), ['subscription_id' => 'id']);
	}

	public function getMessagesCount()
	{
		return 0; //! @todo implement
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('models', 'ID'),
			'queue_id' => Yii::t('models', 'Queue ID'),
			'label' => Yii::t('models', 'Label'),
			'subscriber_id' => Yii::t('models', 'Subscriber ID'),
			'created_on' => Yii::t('models', 'Created On'),
			'is_deleted' => Yii::t('models', 'Is Deleted'),
		];
	}

	public function beforeSave($insert) {
		if ($insert && $this->created_on === null) {
			$now = new \DateTime('now', new \DateTimezone('UTC'));
			$this->created_on = $now->format('Y-m-d H:i:s');
		}
		return parent::beforeSave($insert);
	}

	/**
	 * Creates an array of Subscription objects from DbSubscription objects.
	 * @param DbSubscription|array $dbSubscriptions one or more DbSubscription objects
	 * @return array of Subscription objects
	 */
	public static function createSubscriptions($dbSubscriptions)
	{
		if (!is_array($dbSubscriptions)) {
			$dbSubscriptions = [$dbSubscriptions];
		}
		$result = [];
		foreach($dbSubscriptions as $dbSubscription) {
			$attributes = $dbSubscription->getAttributes();
			unset($attributes['id']);
			unset($attributes['queue_id']);
			unset($attributes['is_deleted']);
			$subscription = new components\Subscription;
			$subscription->setAttributes($attributes);
			foreach($dbSubscription->categories as $category) {
				if ($category->is_exception) {
					$subscription->categories[] = $category->category;
				} else {
					$subscription->exceptions[] = $category->category;
				}
			}
			$result[] = $subscription;
		}
		return $result;
	}
}
