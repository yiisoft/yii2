<?php
namespace yiiunit\data\ar;

use yii\db\BaseActiveRecord;


class TestAR extends BaseActiveRecord
{
    public function attributes()
    {
        return ['v'];
    }

    public function insert($runValidation = true, $attributes = null)
    {
    }

    public static function find()
    {
    }

    public static function getDb()
    {
    }

    public static function primaryKey()
    {
    }
}