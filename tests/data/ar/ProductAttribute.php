<?php


namespace yiiunit\data\ar;


/**
 * Class Product
 * @package yiiunit\data\ar
 *
 * @property int $id
 * @property string $product_sku
 * @property string $value
 * @property Product $product
 */
class ProductAttribute extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_attribute';
    }

    /**
     * @return Product|\yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['sku' => 'product_sku'])->inverseOf('productAttributes');
    }

}
