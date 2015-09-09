<?php

namespace yiiunit\framework\base\stubs;

use yii\base\Model;

/**
 * Description of Customer
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class Customer extends Model
{
    private static $_names = [
        1 => 'Misbahul',
        2 => 'D',
        3 => 'Munir',
    ];
    public $id;

    public function getName()
    {
        return isset(self::$_names[$this->id]) ? self::$_names[$this->id] : null;
    }

    public function extraFields()
    {
        return[
            'name',
        ];
    }
}
