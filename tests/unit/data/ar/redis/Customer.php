<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public $status2;

	/**
	 * @return \yii\redis\ActiveRelation
	 */
	public function getOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'));
	}

	public static function active($query)
	{
		$query->andWhere(array('status' => 1));
	}

	public static function getRecordSchema()
	{
		return new RecordSchema(array(
			'name' => 'customer',
			'primaryKey' => array('id'),
			'columns' => array(
				'id' => 'integer',
				'email' => 'string',
				'name' => 'string',
				'address' => 'string',
				'status' => 'integer'
			)
		));
	}
}