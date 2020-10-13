<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Animal.
 *
 * @author Jose Lorente <jose.lorente.martin@gmail.com>
 * @property int $id
 * @property string $type
 */
class Animal extends ActiveRecord
{
    public $does;

    public static function tableName()
    {
        return 'animal';
    }

    public function init()
    {
        parent::init();
        $this->type = \get_called_class();
    }

    public function getDoes()
    {
        return $this->does;
    }

    /**
     * @param type $row
     * @return \yiiunit\data\ar\Animal
     */
    public static function instantiate($row)
    {
        $class = $row['type'];
        return new $class();
    }
}
