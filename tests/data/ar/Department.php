<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;

/**
 * Class Department
 *
 * @property int $id
 * @property string $title
 *
 * @property Employee[] $employees
 *
 * @author Kolyunya <OleynikovNY@mail.ru>
 * @since 2.0.12
 */
class Department extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * Returns department employees.
     *
     * @return ActiveQuery
     */
    public function getEmployees()
    {
        return $this
            ->hasMany(Employee::class, [
                'department_id' => 'id',
            ])
            ->inverseOf('department')
        ;
    }
}
