<?php

namespace yiiunit\data\ar\sphinx;

class ItemIndex extends ActiveRecord
{
    public static function indexName()
    {
        return 'yii2_test_item_index';
    }
}
