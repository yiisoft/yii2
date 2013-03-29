<?php
namespace yiiunit\data\base;
use yii\base\Model;

/**
 * InvalidRulesModel
 */
class InvalidRulesModel extends Model
{
	public function rules()
	{
		return array(
			array('test'),
		);
	}

}
