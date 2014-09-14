<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Category.
 *
 * @property integer $id
 * @property string $name
 */
class Category extends ActiveRecord
{
    public static function tableName()
    {
        return 'category';
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['category_id' => 'id']);
    }

    public function getLimitedItems()
    {
        return $this->hasMany(Item::className(), ['category_id' => 'id'])
            ->onCondition(['item.id' => [1, 2, 3]]);
    }
}
