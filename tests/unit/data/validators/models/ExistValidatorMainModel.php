<?php

namespace yiiunit\data\validators\models;


use yii\db\ActiveRecord;

class ExistValidatorMainModel extends ActiveRecord
{
	public $testMainVal = 1;
	public static function tableName()
	{
		return 'tbl_validator_exist_main';
	}

	public function getReferences()
	{
		return $this->hasMany(ExistValidatorRefModel::className(), array('ref' => 'id'));
	}
}