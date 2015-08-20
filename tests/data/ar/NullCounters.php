<?php
namespace yiiunit\data\ar;

use yii\db\ActiveQuery;
use yiiunit\framework\db\ActiveRecordTest;

class NullCounters extends ActiveRecord
{
    public static function tableName()
    {
        return 'null_counters';
    }

}
