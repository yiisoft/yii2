<?php

namespace yiiunit\data\ar\redis;

use yiiunit\extensions\redis\ActiveRecordTest;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public $status2;

	public static function attributes()
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

	public static function active($query)
	{
		$query->andWhere(['status' => 1]);
	}

	public function afterSave($insert)
	{
		ActiveRecordTest::$afterSaveInsert = $insert;
		ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
		parent::afterSave($insert);
	}
}