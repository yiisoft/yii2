<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class OrderItem.
 *
 * @property int $order_id
 * @property int $item_id
 * @property int $quantity
 * @property string $subtotal
 */
class OrderItem extends ActiveRecord
{
    public static $tableName;

    public static function tableName()
    {
        return static::$tableName ?: 'order_item';
    }

    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'attributeTypes' => [
                    'order_id' => AttributeTypecastBehavior::TYPE_STRING,
                ],
                'typecastAfterValidate' => false,
                'typecastAfterFind' => true,
                'typecastAfterSave' => false,
                'typecastBeforeSave' => false,
            ],
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    // relations used by ::testFindCompositeWithJoin()
    public function getOrderItemCompositeWithJoin()
    {
        return $this->hasOne(self::className(), ['item_id' => 'item_id', 'order_id' => 'order_id'])
            ->joinWith('item');
    }
    public function getOrderItemCompositeNoJoin()
    {
        return $this->hasOne(self::className(), ['item_id' => 'item_id', 'order_id' => 'order_id']);
    }

    public function getCustom()
    {
        return Order::find();
    }
}
