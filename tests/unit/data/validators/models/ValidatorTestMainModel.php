<?php

namespace yiiunit\data\validators\models;


use yii\db\ActiveRecord;

class ValidatorTestMainModel extends ActiveRecord
{
	public $testMainVal = 1;

	public static function tableName()
	{
		return 'tbl_validator_main';
	}

	public function getReferences()
	{
		return $this->hasMany(ValidatorTestRefModel::className(), array('ref' => 'id'));
	}
}