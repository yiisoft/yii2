<?php
namespace yiiunit\data\ar;

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

	public static function active($query)
	{
		$query->andWhere('status=1');
	}
}
