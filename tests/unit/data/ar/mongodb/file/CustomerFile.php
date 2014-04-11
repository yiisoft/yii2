<?php

namespace yiiunit\data\ar\mongodb\file;

class CustomerFile extends ActiveRecord
{
    public static function collectionName()
    {
        return 'customer_fs';
    }

    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'tag',
                'status',
            ]
        );
    }

    public static function createQuery()
    {
        return new CustomerFileQuery(get_called_class());
    }
}
