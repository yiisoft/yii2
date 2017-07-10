<?php

namespace yiiunit\data\ar;

/**
 * Class Profile
 *
 * @property int $id
 * @property string $description
 *
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

    public static function instantiate($row = [])
    {
        return (new \ReflectionClass(static::className()))->newInstanceWithoutConstructor();
    }
}
