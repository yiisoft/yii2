<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

/**
 * MySQL 数据库的 ColumnSchema 类
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14.1
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var bool 列结构是否应该使用 JSON 支持功能 OMIT 。
     * 在升级到 Yii 2.0.14 使用此属性会更加的方便容易。
     * 默认为 `false`，表示启用了 JSON 支持。
     *
     * @since 2.0.14.1
     * @deprecated 从 2.0.14.1 开始支持，并且将在 2.1 以后移除。
     */
    public $disableJsonSupport = false;


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

        if (!$this->disableJsonSupport && $this->dbType === Schema::TYPE_JSON) {
            return new JsonExpression($value, $this->type);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if (!$this->disableJsonSupport && $this->type === Schema::TYPE_JSON) {
            return json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }
}
