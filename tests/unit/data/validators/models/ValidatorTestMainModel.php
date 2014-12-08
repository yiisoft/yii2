<?php

namespace yiiunit\data\validators\models;

use yiiunit\data\ar\ActiveRecord;

class ValidatorTestMainModel extends ActiveRecord
{
    public $testMainVal = 1;

    public static function tableName()
    {
        return 'validator_main';
    }

    public function getReferences()
    {
        return $this->hasMany(ValidatorTestRefModel::className(), ['ref' => 'id']);
    }
}
