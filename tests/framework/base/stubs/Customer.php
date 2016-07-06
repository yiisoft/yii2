<?php
namespace yiiunit\framework\base\stubs;
use yii\base\Model;
/**
 * Description of Customer
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.1
 */
class Customer extends Model
{
    private static $_names = [
        1 => 'Munir',
        2 => 'Mujib',
        3 => 'Peter',
        4 => 'Hafid',
        5 => 'Henry',
        6 => 'Surya'
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