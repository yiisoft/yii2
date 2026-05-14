<?php

namespace yiiunit\data\ar;

class CustomerWithAlias extends Customer
{
    public $status2;
    public $sumTotal;

    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     * @return CustomerQuery<static>
     */
    public static function find()
    {
        $activeQuery = new CustomerQuery(static::class);
        $activeQuery->alias('csr');
        return $activeQuery;
    }
}
