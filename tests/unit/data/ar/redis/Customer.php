<?php

namespace yiiunit\data\ar\redis;

use yii\redis\ActiveQuery;
use yiiunit\extensions\redis\ActiveRecordTest;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public $status2;

	public function attributes()
	{
		return ['id', 'email', 'name', 'address', 'status'];
	}

	/**
	 * @return \yii\redis\ActiveRelation
	 */
	public function getOrders()
	{
		return $this->hasMany(Order::className(), ['customer_id' => 'id']);
	}

	public function afterSave($insert)
	{
		ActiveRecordTest::$afterSaveInsert = $insert;
		ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
		parent::afterSave($insert);
	}

	public static function createQuery()
	{
		return new CustomerQuery(['modelClass' => get_called_class()]);
	}
}