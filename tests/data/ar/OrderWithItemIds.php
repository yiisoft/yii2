<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\ArrayExpression;

/**
 * Class OrderWithItemIds.
 *
 * Order whose items are referenced through a PostgreSQL array column instead of a junction table.
 *
 * @property int $id
 * @property ArrayExpression|null $item_ids
 *
 * @property-read Item[] $items
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 */
class OrderWithItemIds extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_with_item_ids';
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_ids']);
    }
}
