<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * ProfileWithConstructor.
 *
 * @property int $id
 * @property string $description
 */
class ProfileWithConstructor extends ActiveRecord
{
    public static function tableName()
    {
        return 'profile';
    }

    public function __construct($description)
    {
        $this->description = $description;
        parent::__construct();
    }

    public static function instance($refresh = false)
    {
        return self::instantiate([]);
    }

    public static function instantiate($row)
    {
        return (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();
    }
}
