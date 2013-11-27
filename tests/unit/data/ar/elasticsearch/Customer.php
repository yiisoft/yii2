<?php
namespace yiiunit\data\ar\elasticsearch;

use yiiunit\extensions\elasticsearch\ActiveRecordTest;

/**
 * Class Customer
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property integer $status
 */
class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public $status2;

	public static function attributes()
	{
		return ['name', 'email', 'address', 'status'];
	}

	public function getOrders()
	{
		return $this->hasMany(Order::className(), array('customer_id' => ActiveRecord::PRIMARY_KEY_NAME))->orderBy('create_time');
	}

	public static function active($query)
	{
		$query->andWhere(array('status' => 1));
	}

	public function afterSave($insert)
	{
		ActiveRecordTest::$afterSaveInsert = $insert;
		ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
		parent::afterSave($insert);
	}
}
