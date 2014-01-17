<?php
namespace yiiunit\data\ar;

use yii\db\ActiveQuery;
use yiiunit\framework\db\ActiveRecordTest;

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

	public static function tableName()
	{
		return 'tbl_customer';
	}

	public function getOrders()
	{
		return $this->hasMany(Order::className(), ['customer_id' => 'id'])->orderBy('id');
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
