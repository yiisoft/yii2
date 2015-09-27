<?php

namespace yiiunit\data\ar;

/**
 * Class UuidPk
 *
 * @property integer $id
 * @property string $stringcol
 */
class UuidPk extends ActiveRecord
{
    public static function tableName()
    {
        return 'uuid_pk';
    }
}
