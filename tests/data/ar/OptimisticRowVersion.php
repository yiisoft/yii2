<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * ActiveRecord stub backed by an MSSQL `rowversion` optimistic lock column.
 *
 * @property int $id
 * @property string $name
 * @property int $rv rowversion token used as the optimistic lock attribute.
 */
final class OptimisticRowVersion extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'test_optimistic_rowversion';
    }

    public function optimisticLock(): string
    {
        return 'rv';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 128],
        ];
    }
}
