<?php

namespace yiiunit\framework\base\stubs;

use yii\base\Model;

/**
 * Description of Product
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class Product extends Model
{
    private static $_names = [
        '1' => 'Product satu',
        '2' => 'Product dua',
        '3' => 'Product tiga',
        '4' => 'Product empat',
        '5' => 'Product lima',
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
