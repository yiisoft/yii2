<?php

namespace yiiunit\data\ar;

/**
 * Class Item
 *
 * @property integer $id
 * @property string $name
 * @property integer $category_id
 */
class Item extends ActiveRecord
{
    public static function tableName()
    {
        return 'item';
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
}
