<?php

namespace yiiunit\data\ar;
use yii\db\ar\ActiveQuery;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public static function tableName()
	{
		return 'tbl_customer';
	}

	public static function relations()
	{
		return array(
			'orders:Order[]' => array(
				'link' => array('customer_id' => 'id'),
			),
		);
	}

	public function orders()
	{
		return $this->hasMany('Order', array('id' => 'customer_id'));
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function active($query)
	{
		return $query->andWhere('@.`status` = ' . self::STATUS_ACTIVE);
	}
}