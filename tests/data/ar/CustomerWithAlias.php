<?php

namespace yiiunit\data\ar;

class CustomerWithAlias extends Customer
{
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
