<?php

namespace yiiunit\data\validators\models;

use yiiunit\data\ar\ActiveRecord;

class ValidatorTestRefModel extends ActiveRecord
{

    public $test_val = 2;
    public $test_val_fail = 99;

    public static function tableName()
    {
        return 'tbl_validator_ref';
    }

    public function getMain()
    {
        return $this->hasOne(ValidatorTestMainModel::className(), ['id' => 'ref']);
    }
}
