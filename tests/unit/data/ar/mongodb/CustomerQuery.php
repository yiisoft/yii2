<?php
namespace yiiunit\data\ar\mongodb;
use yii\mongodb\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
	public static function activeOnly($query)
	{
		$query->andWhere(['status' => 2]);
	}
}
 