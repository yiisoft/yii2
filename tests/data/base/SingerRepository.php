<?php

namespace yiiunit\data\base;

use yii\base\Component;

class SingerRepository extends Component implements SingerRepositoryInterface
{
    public static function dataModelClass()
    {
        return Singer::className();
    }
}
