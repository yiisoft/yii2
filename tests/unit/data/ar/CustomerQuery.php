<?php
namespace yiiunit\data\ar;
use yii\db\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
	public static function active($query)
	{
		$query->andWhere('status=1');
	}
}
 