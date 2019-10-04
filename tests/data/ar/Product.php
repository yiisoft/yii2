<?php


namespace yiiunit\data\ar;


/**
 * Class Product
 * @package yiiunit\data\ar
 *
 * @property string $sku
 * @property string $title
 * @property ProductAttribute[] $productAttributes
 */
class Product extends ActiveRecord
{
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @return ProductAttribute[]|\yii\db\ActiveQuery
     */
    public function getProductAttributes()
    {
        return $this->hasMany(ProductAttribute::className(), ['product_sku' => 'sku']);
    }
}
