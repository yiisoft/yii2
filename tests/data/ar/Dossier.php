<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;

/**
 * Class Dossier
 *
 * @property int $id
 * @property int $department_id
 * @property int $employee_id
 * @property string $summary
 *
 * @property Employee $employee
 *
 * @author Kolyunya <OleynikovNY@mail.ru>
 * @since 2.0.12
 */
class Dossier extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dossier';
    }

    /**
     * Returns dossier employee.
     *
     * @return ActiveQuery
     */
    public function getEmployee()
    {
        return $this
            ->hasOne(Employee::className(), [
                'department_id' => 'department_id',
                'id' => 'employee_id',
            ])
            ->inverseOf('dossier')
        ;
    }
}
