<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\Expression;

use function bin2hex;
use function is_string;

/**
 * Class ColumnSchema for MSSQL database
 *
 * @since 2.0.23
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var bool whether this column is a computed column
     * @since 2.0.39
     */
    public $isComputed;

    /**
     * {@inheritdoc}
     *
     * Converts string values for `varbinary` columns to explicit `CONVERT(VARBINARY(MAX), 0x...)` expressions to avoid
     * implicit `varchar` to `varbinary` conversion errors in SQL Server, particularly under `INSERT ... OUTPUT INTO`
     * and `UPDATE`.
     *
     * @see https://github.com/yiisoft/yii2/issues/12599
     */
    public function dbTypecast($value)
    {
        if ($this->type === Schema::TYPE_BINARY && $this->dbType === 'varbinary') {
            if (is_string($value)) {
                return new Expression('CONVERT(VARBINARY(MAX), 0x' . bin2hex($value) . ')');
            }

            if ($value === null && $this->allowNull) {
                return new Expression('CAST(NULL AS VARBINARY(MAX))');
            }
        }

        return parent::dbTypecast($value);
    }

    /**
     * Prepares default value and converts it according to [[phpType]]
     * @param mixed $value default value
     * @return mixed converted value
     * @since 2.0.24
     */
    public function defaultPhpTypecast($value)
    {
        if ($value !== null) {
            // convert from MSSQL column_default format, e.g. ('1') -> 1, ('string') -> string
            $value = substr(substr($value, 2), 0, -2);
        }

        return parent::phpTypecast($value);
    }
}
