<?php

namespace yiiunit\data\validators\models;


use yii\db\ActiveRecord;

class ExistValidatorRefModel extends ActiveRecord
{

	public $test_val = 2;
	public $test_val_fail = 99;
	public static function tableName()
	{
		return 'tbl_validator_exist_ref';
	}

	public function getMain()
	{
		return $this->hasOne(ExistValidatorMainModel::className(), array('id' => 'ref'));
	}
}