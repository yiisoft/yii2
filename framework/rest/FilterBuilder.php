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

/**
 * FilterBuilder
 *
 * @property mixed $filter filter value.
 * @property Model $searchModel model to be used for filter attributes validation.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class FilterBuilder extends Model
{
    /**
     * @var string name of the attribute, which should handle filter value.
     */
    public $filterAttributeName = 'filter';
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
     * @var mixed raw filter specification.
     */
    private $_filter;
    /**
     * @var Model|array|string|callable model to be used for filter attributes validation.
     */
    private $_searchModel;


    /**
     * @return mixed raw filter value.
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param mixed $filter raw filter value.
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
    }

    /**
     * @return Model model instance.
     * @throws InvalidConfigException on invalid configuration.
     */
    public function getSearchModel()
    {
        if (!is_object($this->_searchModel) || $this->_searchModel instanceof \Closure) {
            $model = Yii::createObject($this->_searchModel);
            if (!$model instanceof Model) {
                throw new InvalidConfigException('`' . get_class($this) . '::model` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
            }
            $this->_searchModel = $model;
        }
        return $this->_searchModel;
    }

    /**
     * @param Model|array|string|callable $model model instance or its DI compatible configuration.
     * @throws InvalidConfigException on invalid configuration.
     */
    public function setSearchModel($model)
    {
        if (is_object($model)) {
            if (!$model instanceof Model && !$model instanceof \Closure) {
                throw new InvalidConfigException('`' . get_class($this) . '::model` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
            }
        }
        $this->_searchModel = $model;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            $this->filterAttributeName
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [$this->filterAttributeName, 'validateFilter', 'skipOnEmpty' => false]
        ];
    }

    // Validation :

    /**
     * Validates filter attribute value to match filer condition specification.
     * @param string $attribute filter attribute name.
     * @param array $params validation parameters.
     */
    public function validateFilter($attribute, $params)
    {
        $value = $this->{$attribute};
        if ($value !== null) {
            $this->validateCondition($value);
        }
    }

    /**
     * Validates filter condition.
     * @param mixed $condition raw filter condition.
     */
    protected function validateCondition($condition)
    {
        if (!is_array($condition)) {
            $this->addError($this->filterAttributeName, Yii::t('yii', 'The format of {attribute} is invalid.', ['attribute' => $this->getAttributeLabel($this->filterAttributeName)]));
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
                            $this->addError($this->filterAttributeName, Yii::t('yii', 'Condition for {attribute} is invalid.', ['attribute' => $attribute]));
                            continue 2;
                        }

                        $this->validateOperatorCondition($operatorKeyword, $attribute, $value[$operatorKeyword]);
                        continue 2;
                    }
                }
            }

            if (($error = $this->validateAttributeValue($attribute, $value)) !== null) {
                $this->addError($this->filterAttributeName, $error);
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
                $this->addError($this->filterAttributeName, Yii::t('yii', 'Operator {operator} requires multiple operands.', ['operator' => $operator]));
                return;
            }
            foreach ($value as $v) {
                if (($error = $this->validateAttributeValue($attribute, $v)) !== null) {
                    $this->addError($this->filterAttributeName, $error);
                }
            }
        } else {
            if (($error = $this->validateAttributeValue($attribute, $value)) !== null) {
                $this->addError($this->filterAttributeName, $error);
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
        $model = $this->getSearchModel();
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

    /**
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before building the filter. Defaults to `true`. If the validation fails, the exception
     * will be thrown.
     * @return mixed built actual filter value.
     */
    public function build($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        return $this->buildInternal();
    }

    /**
     * Performs actual filter build.
     * By default this method returns value of the [[filter]] as it is.
     * The child class may override this method providing more specific implementation.
     * @return mixed built actual filter value.
     */
    protected function buildInternal()
    {
        return $this->getFilter();
    }

    // Property access :

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if ($name === $this->filterAttributeName) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if ($name === $this->filterAttributeName) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($name === $this->filterAttributeName) {
            return $this->getFilter();
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($name === $this->filterAttributeName) {
            $this->setFilter($value);
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if ($name === $this->filterAttributeName) {
            return $this->getFilter() !== null;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        if ($name === $this->filterAttributeName) {
            $this->setFilter(null);
        } else {
            parent::__unset($name);
        }
    }
}