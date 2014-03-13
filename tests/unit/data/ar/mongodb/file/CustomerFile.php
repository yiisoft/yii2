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

    public static function createQuery($config = [])
    {
        $config['modelClass'] = get_called_class();

        return new CustomerFileQuery($config);
    }
}
