<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;

/**
 * Class Employee
 *
 * @property int $id
 * @property int $department_id
 * @property string $first_name
 * @property string $last_name
 *
 * @property string $fullName
 * @property Department $department
 * @property Dossier $dossier
 *
 * @author Kolyunya <OleynikovNY@mail.ru>
 * @since 2.0.12
 */
class Employee extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee';
    }

    /**
     * Returns employee full name.
     *
     * @return string
     */
    public function getFullName()
    {
        $fullName = $this->first_name . ' ' . $this->last_name;

        return $fullName;
    }

    /**
     * Returns employee department.
     *
     * @return ActiveQuery
     */
    public function getDepartment()
    {
        return $this
            ->hasOne(Department::class, [
                'id' => 'department_id',
            ])
            ->inverseOf('employees')
        ;
    }

    /**
     * Returns employee department.
     *
     * @return ActiveQuery
     */
    public function getDossier()
    {
        return $this
            ->hasOne(Dossier::class, [
                'department_id' => 'department_id',
                'employee_id' => 'id',
            ])
            ->inverseOf('employee')
        ;
    }
}
