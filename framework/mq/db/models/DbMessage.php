<?php

namespace yii\mq\db\models;

use yii\mq;

/**
 * This is the model class for table "{{tbl_messages}}".
 *
 * @property integer $id
 * @property integer $queue_id
 * @property string $created_on
 * @property integer $sender_id
 * @property integer $message_id
 * @property integer $subscription_id
 * @property integer $status
 * @property integer $times_out_on
 * @property string $reserved_on
 * @property string $deleted_on
 * @property string $mimetype
 * @property string $body
 *
 * The followings are the available model relations:
 * @property DbMessage $mainMessage
 * @property DbMessage[] $subscriptionMessages
 * @property DbSubscription $subscription
 * @property Users $sender
 */
class DbMessage extends \yii\db\ActiveRecord
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
		return '{{%tbl_messages}}';
	}

	public static function createQuery()
	{
		return new DbMessageQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['queue_id', 'sender_id', 'body'], 'required', 'except'=>'search'],
			[['sender_id', 'subscription_id'], 'number', 'integerOnly'=>true],
			[['message_id', 'subscription_id'], 'number', 'integerOnly'=>true, 'on'=>'search'],
			['status', 'number', 'integerOnly'=>true, 'on'=>'search'],
			['mimetype', 'safe', 'on'=>'search'],
		];
	}

	public function getMainMessage()
	{
		return $this->hasOne(DbMessage::className(), ['id' => 'message_id']);
	}

	public function getSender()
	{
		//! @todo how to resolve this?
		return $this->hasOne(Yii::$app->getModule('nfy')->userClass, ['id' => 'sender_id']);
	}

	public function getSubscription()
	{
		return $this->hasOne(DbSubscription::className(), ['id' => 'subscription_id']);
	}

	public function getSubscriptionMessages()
	{
		return $this->hasMany(DbMessage::className(), [self::tableName().'.message_id' => 'id']);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('models', 'ID'),
			'queue_id' => Yii::t('models', 'Queue ID'),
			'created_on' => Yii::t('models', 'Created On'),
			'sender_id' => Yii::t('models', 'Sender ID'),
			'message_id' => Yii::t('models', 'Message ID'),
			'subscription_id' => Yii::t('models', 'Subscription ID'),
			'status' => Yii::t('models', 'Status'),
			'times_out_on' => Yii::t('models', 'Times Out On'),
			'reserved_on' => Yii::t('models', 'Reserved On'),
			'deleted_on' => Yii::t('models', 'Deleted On'),
			'mimetype' => Yii::t('models', 'MIME Type'),
			'body' => Yii::t('models', 'Message Body'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if ($insert && $this->created_on === null) {
			$now = new \DateTime('now', new \DateTimezone('UTC'));
			$this->created_on = $now->format('Y-m-d H:i:s');
		}
		return parent::beforeSave($insert);
	}

	public function __clone()
	{
		$this->id = null;
		$this->subscription_id = null;
		$this->isNewRecord = true;
	}

	/**
	 * Creates an array of Message objects from DbMessage objects.
	 * @param DbMessage|array $dbMessages one or more DbMessage objects
	 * @return array of Message objects
	 */
	public static function createMessages($dbMessages)
	{
		if (!is_array($dbMessages)) {
			$dbMessages = [$dbMessages];
		}
		$result = [];
		foreach($dbMessages as $dbMessage) {
			$attributes = $dbMessage->getAttributes();
			unset($attributes['queue_id']);
			unset($attributes['subscription_id']);
			unset($attributes['mimetype']);
			$message = new components\Message;
			$message->setAttributes($attributes);
			$result[] = $message;
		}
		return $result;
	}
}
