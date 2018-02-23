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
 * Class ColumnSchema
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14.1
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * Temporary property to indicate, whether the column schema should OMIT using new JSON support feature.
     * You can temporary use this property to make upgrade to the next framework version easier.
     * Default to `false`, meaning JSON support is enabled.
     *
     * @var bool
     * @since 2.0.14.1
     * @deprecated Since 2.0.14.1 and will be removed in 2.1.
     */
    public $disableJsonSupport = false;

    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
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
