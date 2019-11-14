<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

/**
 * PostgreSQL 数据库的 ColumnSchema 类。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var int 数组的维度。默认为 0，表示此列不是数组。
     */
    public $dimension = 0;
    /**
     * @var bool 列结构是否应该使用 JSON 支持功能 OMIT。
     * 你可以是使用此属性使升级到 Yii 2.0.14 更容易。
     * 默认为 `false`，表示支持 JSON。
     *
     * @since 2.0.14.1
     * @deprecated 自 2.0.14 开始支持，并将在 2.1 以后移除。
     */
    public $disableJsonSupport = false;
    /**
     * @var bool 列结构是否应该使用 PgSQL Arrays 支持功能 OMIT。
     * 你可以是使用此属性使升级到 Yii 2.0.14 更容易。
     * 默认为 `false`，表示支持 Arrays。
     *
     * @since 2.0.14.1
     * @deprecated 自 2.0.14 开始支持，并将在 2.1 以后移除。
     */
    public $disableArraySupport = false;
    /**
     * @var bool 是否应该将 Array 列值反序列化为 [[ArrayExpression]] 对象。
     * 你可以是使用此属性使升级到 Yii 2.0.14 更容易。
     * 默认为 `true`，表示数组未序列化为 [[ArrayExpression]] 对象。
     *
     * @since 2.0.14.1
     * @deprecated 自 2.0.14 开始支持，并将在 2.1 以后移除。
     */
    public $deserializeArrayColumnToArrayExpression = true;


    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if ($this->dimension > 0) {
            return $this->disableArraySupport
                ? (string) $value
                : new ArrayExpression($value, $this->dbType, $this->dimension);
        }
        if (!$this->disableJsonSupport && in_array($this->dbType, [Schema::TYPE_JSON, Schema::TYPE_JSONB], true)) {
            return new JsonExpression($value, $this->dbType);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if ($this->disableArraySupport) {
                return $value;
            }
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            } elseif ($value === null) {
                return null;
            }

            return $this->deserializeArrayColumnToArrayExpression
                ? new ArrayExpression($value, $this->dbType, $this->dimension)
                : $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * 从 DBMS 检索到 PHP 表示后，转为为 $value。
     *
     * @param string|null $value
     * @return bool|mixed|null
     */
    protected function phpTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
                switch (strtolower($value)) {
                    case 't':
                    case 'true':
                        return true;
                    case 'f':
                    case 'false':
                        return false;
                }
                return (bool) $value;
            case Schema::TYPE_JSON:
                return $this->disableJsonSupport ? $value : json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }

    /**
     * 创建 ArrayParser 的实例
     *
     * @return ArrayParser
     */
    protected function getArrayParser()
    {
        static $parser = null;

        if ($parser === null) {
            $parser = new ArrayParser();
        }

        return $parser;
    }
}
