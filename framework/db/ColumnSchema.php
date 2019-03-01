<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;
use yii\helpers\StringHelper;

/**
 * ColumnSchema 类描述了数据库表中的列的元数据。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends BaseObject
{
    /**
     * @var string 此列的名称（不带引号）。
     */
    public $name;
    /**
     * @var bool 此列是否可以为空。
     */
    public $allowNull;
    /**
     * @var string 此列的抽象类型。可能的抽象类型包括：
     * char，string，text，boolean，smallint，integer，bigint，float，decimal，datetime，
     * timestamp，time，date，binary 和 money。
     */
    public $type;
    /**
     * @var string 此列的 PHP 类型。可能的 PHP 类型包括：
     * `string`，`boolean`，`integer`，`double`，`array`。
     */
    public $phpType;
    /**
     * @var string 此列的 DB 类型。可能的 DB 类型因 DBMS 的类型而异。
     */
    public $dbType;
    /**
     * @var mixed 列的默认值
     */
    public $defaultValue;
    /**
     * @var array 可枚举值。仅当列声明为可枚举类型时才设置此值。
     */
    public $enumValues;
    /**
     * @var int 列的显示大小。
     */
    public $size;
    /**
     * @var int 列数据的精度，如果为数字。
     */
    public $precision;
    /**
     * @var int 列数据的小数位数，如果为数字。
     */
    public $scale;
    /**
     * @var bool 此列是否为主键
     */
    public $isPrimaryKey;
    /**
     * @var bool 此列是否自增长
     */
    public $autoIncrement = false;
    /**
     * @var bool 列是否为无符号。
     * 仅在 [[type]] 为 `smallint`，`integer` 或 `bigint`　有效。
     */
    public $unsigned;
    /**
     * @var string 此列的注释。并非所有的 DBMS 都支持。
     */
    public $comment;


    /**
     * 从数据库检索后，根据　[[phpType]] 转换为输入值。
     * 如果值为空或为 [[Expression]]，则不会转换。
     * @param mixed $value 输入值
     * @return mixed 转换后的值
     */
    public function phpTypecast($value)
    {
        return $this->typecast($value);
    }

    /**
     * 根据 [[type]] 和 [[dbType]] 转换为输入值，以便在数据库查询中使用。
     * 如果值为 null 或为 [[Expression]]，则不会转换。
     * @param mixed $value 输入值
     * @return mixed 转换的值。这也可以是一个数组，其中值作为第一个元素，
     * PDO 类型作为第二个元素。
     */
    public function dbTypecast($value)
    {
        // 默认的实现与 PHP 强制转换相同，
        // 但是可以使用显式 PDO 类型的注释来覆盖它。
        return $this->typecast($value);
    }

    /**
     * 从数据库检索后，根据 [[phpType]] 转换为输入值。
     * 如果值为 null 或 [[Expression]]，则不会转换。
     * @param mixed $value 输入值
     * @return mixed 转换的值
     * @since 2.0.3
     */
    protected function typecast($value)
    {
        if ($value === ''
            && !in_array(
                $this->type,
                [
                    Schema::TYPE_TEXT,
                    Schema::TYPE_STRING,
                    Schema::TYPE_BINARY,
                    Schema::TYPE_CHAR
                ],
                true)
        ) {
            return null;
        }

        if ($value === null
            || gettype($value) === $this->phpType
            || $value instanceof ExpressionInterface
            || $value instanceof Query
        ) {
            return $value;
        }

        if (is_array($value)
            && count($value) === 2
            && isset($value[1])
            && in_array($value[1], $this->getPdoParamTypes(), true)
        ) {
            return new PdoValue($value[0], $value[1]);
        }

        switch ($this->phpType) {
            case 'resource':
            case 'string':
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    return StringHelper::floatToString($value);
                }
                return (string) $value;
            case 'integer':
                return (int) $value;
            case 'boolean':
                // treating a 0 bit value as false too
                // https://github.com/yiisoft/yii2/issues/9006
                return (bool) $value && $value !== "\0";
            case 'double':
                return (float) $value;
        }

        return $value;
    }

    /**
     * @return int[] 表示可能的 PDO 参数类型的数字数组
     */
    private function getPdoParamTypes()
    {
        return [\PDO::PARAM_BOOL, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_LOB, \PDO::PARAM_NULL, \PDO::PARAM_STMT];
    }
}
