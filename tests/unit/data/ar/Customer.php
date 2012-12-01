<?php

namespace yiiunit\data\ar;
use yii\db\ar\ActiveQuery;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public function tableName()
	{
		return 'tbl_customer';
	}

	public function orders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'));
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function active($query)
	{
		return $query->andWhere('`status` = ' . self::STATUS_ACTIVE);
	}
}