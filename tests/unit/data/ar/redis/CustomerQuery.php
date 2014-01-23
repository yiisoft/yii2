<?php
namespace yiiunit\data\ar\redis;
use yii\redis\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
	public function active()
	{
		$this->andWhere(['status' => 1]);
		return $this;
	}
}
 