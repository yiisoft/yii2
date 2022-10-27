<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Cat.
 *
 * @author Jose Lorente <jose.lorente.martin@gmail.com>
 * @since 2.0
 */
class Cat extends Animal
{
    /**
     * @param self $record
     * @param array $row
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->does = 'meow';
    }

    /**
     * This is to test if __isset catches the exception.
     * @throw DivisionByZeroError
     * @return float|int
     */
    public function getException()
    {
        throw new \Exception('no');
    }

    /**
     * This is to test if __isset catches the error.
     * @throw DivisionByZeroError
     * @return float|int
     */
    public function getThrowable()
    {
        return 5/0;
    }
}
