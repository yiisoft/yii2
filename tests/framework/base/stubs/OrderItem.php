<?php

namespace yiiunit\framework\base\stubs;

use yii\base\Model;
/**
 * Description of OrderItem
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class OrderItem extends Model
{
    public $product_id;
    public $qty;

    private $_product;
    public function getProduct()
    {
        if($this->_product === null){
            $this->_product = new Product(['id' => $this->product_id]);
        }
        return $this->_product;
    }

    public function extraFields()
    {
        return[
            'product'
        ];
    }
}
