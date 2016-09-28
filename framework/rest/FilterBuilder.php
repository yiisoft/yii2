<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Object;

/**
 * FilterBuilder
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class FilterBuilder extends Object
{
    /**
     * @var array raw filter specification.
     */
    public $filter = [];
    /**
     * @var array list of validation errors
     */
    public $errors = [];
    /**
     * @var array
     */
    public $blockKeywords = [
        '$and',
        '$or',
        '$not',
    ];
    /**
     * @var array
     */
    public $operatorKeywords = [
        '$lt',
        '$gt',
        '$lte',
        '$gte',
        '$eq',
        '$neq',
        '$in',
        '$nin',
    ];
    /**
     * @var array list of operators name, which should accept multiple values.
     */
    public $multiValueOperators = [
        '$in',
        '$nin',
    ];

    /**
     * @var Model|array|string|callable model to be used for filter attributes validation.
     */
    private $_model;


    /**
     * @return Model model instance.
     * @throws InvalidConfigException on invalid configuration.
     */
    public function getModel()
    {
        if (!is_object($this->_model) || $this->_model instanceof \Closure) {
            $model = Yii::createObject($this->_model);
            if (!$model instanceof Model) {
                throw new InvalidConfigException('`' . get_class($this) . '::model` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
            }
            $this->_model = $model;
        }
        return $this->_model;
    }

    /**
     * @param Model|array|string|callable $model model instance or its DI compatible configuration.
     * @throws InvalidConfigException on invalid configuration.
     */
    public function setModel($model)
    {
        if (is_object($model)) {
            if (!$model instanceof Model && !$model instanceof \Closure) {
                throw new InvalidConfigException('`' . get_class($this) . '::model` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
            }
        }
        $this->_model = $model;
    }

    // Validation :

    public function validate()
    {
        $this->errors = [];

        $this->validateCondition($this->filter);

        return empty($this->errors);
    }

    /**
     * Validates filter condition.
     * @param mixed $condition raw filter condition.
     */
    protected function validateCondition($condition)
    {
        if (!is_array($condition)) {
            $this->errors[] = Yii::t('yii', 'The format of {attribute} is invalid.', ['attribute' => 'filter']);
            return;
        }

        if (empty($condition)) {
            return;
        }

        foreach ($this->blockKeywords as $keyword) {
            if (isset($condition[$keyword])) {
                $this->validateCondition($condition[$keyword]);
                return;
            }
        }

        $this->validateHashCondition($condition);
    }

    /**
     * Validates 'hash' condition, e.g. `name => value`.
     * @param array $condition condition
     */
    public function validateHashCondition($condition)
    {
        foreach ($condition as $attribute => $value) {
            if (is_array($value)) {
                foreach ($this->operatorKeywords as $operatorKeyword) {
                    if (isset($value[$operatorKeyword])) {
                        if (count($value) > 1) {
                            $this->errors[] = Yii::t('yii', 'Condition for {attribute} is invalid.', ['attribute' => $attribute]);
                            continue 2;
                        }

                        $this->validateOperatorCondition($operatorKeyword, $attribute, $value[$operatorKeyword]);
                        continue 2;
                    }
                }
            }

            if (($error = $this->validateAttributeValue($attribute, $value)) !== null) {
                $this->errors[] = $error;
            }
        }
    }

    /**
     * Validates operator condition.
     * @param string $operator operator keyword.
     * @param string $attribute attribute name.
     * @param mixed $value attribute value.
     */
    protected function validateOperatorCondition($operator, $attribute, $value)
    {
        if (in_array($operator, $this->multiValueOperators, true)) {
            if (!is_array($value)) {
                $this->errors[] = Yii::t('yii', 'Operator {operator} requires multiple operands.', ['operator' => $operator]);
                return;
            }
            foreach ($value as $v) {
                if (($error = $this->validateAttributeValue($attribute, $v)) !== null) {
                    $this->errors[] = $error;
                }
            }
        } else {
            if (($error = $this->validateAttributeValue($attribute, $value)) !== null) {
                $this->errors[] = $error;
            }
        }
    }

    /**
     * Validates attribute value in the scope of [[model]].
     * @param string $attribute attribute name.
     * @param mixed $value attribute value.
     * @return null|string error message, `null` if no error.
     */
    protected function validateAttributeValue($attribute, $value)
    {
        $model = $this->getModel();
        if (!$model->isAttributeSafe($attribute)) {
            return Yii::t('yii', 'Unknown filter attribute {attribute}', ['attribute' => $attribute]);
        }

        $model->{$attribute} = $value;
        if (!$model->validate([$attribute])) {
            return $model->getFirstError($attribute);
        }

        return null;
    }

    // Build :

    public function build()
    {
        ;
    }
}