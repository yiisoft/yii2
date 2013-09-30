<?php
namespace yiiunit\data\ar\elasticsearch;

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

	public static function columns()
	{
		return array(
			'id' => 'integer',
			'name' => 'string',
			'email' => 'string',
			'address' => 'string',
			'status' => 'integer',
		);
	}

	public function getOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'))->orderBy('id');
	}

	public static function active($query)
	{
		$query->andWhere('status=1');
	}
}
