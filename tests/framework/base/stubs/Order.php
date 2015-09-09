<?php

namespace yiiunit\framework\base\stubs;

use yii\base\Model;

/**
 * Description of Order
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class Order extends Model
{
    public $id;
    public $customer_id;
    private $_customer;
    private $_items = [];

    public function getCustomer()
    {
        if ($this->_customer === null) {
            $this->_customer = new Customer(['id' => $this->customer_id]);
        }
        return $this->_customer;
    }

    public function addItem($item)
    {
        array_push($this->_items, new OrderItem($item));
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function extraFields()
    {
        return [
            'customer',
            'items',
        ];
    }
}
