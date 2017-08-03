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
use yii\helpers\ArrayHelper;
use yii\validators\BooleanValidator;
use yii\validators\EachValidator;
use yii\validators\NumberValidator;
use yii\validators\StringValidator;

/**
 * DataFilter is a special kind of [[Model]] dedicated to the processing of the query filter specification.
 * It allows validation and building of the filter condition passed via request.
 *
 * The used filter format has been designed to be suitable for JSON request types.
 *
 * Filter example:
 *
 * ```json
 * {
 *     "$or": [
 *         {
 *             "$and": [
 *                 {
 *                     "name": "some name",
 *                     "price": "25",
 *                 }
 *             ]
 *         },
 *         {
 *             "id": {"$in": [2, 5, 9]},
 *             "price": {
 *                 "$gt": 10,
 *                 "$lt": 50
 *             }
 *         }
 *     ]
 * }
 * ```
 *
 * Filter should be specified in request by key equal to [[filterAttributeName]] value. Thus actual HTTP request body
 * will look like following:
 *
 * ```json
 * {
 *     "filter": {"$or": {...}},
 *     "page": 2,
 *     ...
 * }
 * ```
 *
 * Raw filter value should be assigned to [[filter]] property, which is considered as this model attribute.
 * Thus you may populate its from request data via [[load()]] method:
 *
 * ```php
 * use yii\rest\DataFilter;
 *
 * $dataFilter = new DataFilter();
 * $dataFilter->load(Yii::$app->request->getBodyParams());
 * ```
 *
 * In order to function this class requires a 'slave' model specified via [[searchModel]]. This model should declare
 * all available search attributes and their validation rules. For example:
 *
 * ```php
 * class SearchModel extends \yii\base\Model
 * {
 *     public $id;
 *     public $name;
 *
 *     public function rules()
 *     {
 *         return [
 *             [['id', 'name'], 'trim'],
 *             ['id', 'integer'],
 *             ['name', 'string'],
 *         ];
 *     }
 * }
 * ```
 *
 * In order to reduce amount of classes, you may use [[\yii\base\DynamicModel]] instance as [[searchModel]].
 * In this case you should specify [[searchModel]] using PHP callable:
 *
 * ```php
 * function () {
 *     return (new \yii\base\DynamicModel(['id' => null, 'name' => null]))
 *         ->addRule(['id', 'name'], 'trim')
 *         ->addRule('id', 'integer')
 *         ->addRule('name', 'string');
 * }
 * ```
 *
 * You can use [[validate()]] method to check if filter value is valid or not. If validation fails you can use
 * [[getErrors()]] to get actual error messages.
 *
 * In order to acquire actual filter value suitable for data fetching use [[build()]] method.
 *
 * > Note: this is a base class its implementation of [[build()]] simply returns [[filter]] value as it is.
 * In order to convert filter in particular format you should use descendant of this class, which implements
 * [[buildInternal()]] method accordingly.
 *
 * @see ActiveDataFilter
 *
 * @property mixed $filter filter value.
 * @property Model $searchModel model to be used for filter attributes validation.
 * @property array $searchAttributeTypes search attribute type map.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13
 */
class DataFilter extends Model
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';

    /**
     * @var string name of the attribute, which should handle filter value.
     * This field defines the filter attribute name, which will be used to load data via [[load()]] method.
     */
    public $filterAttributeName = 'filter';
    /**
     * @var string label for the filter attribute specified via [[filterAttributeName]].
     * It will be used during error messages composition.
     */
    public $filterAttributeLabel;
    /**
     * @var array map of filter condition keywords to validation methods.
     * These methods are used by [[validateCondition]] to validate raw filter conditions.
     */
    public $conditionValidators = [
        '$and' => 'validateConjunctionCondition',
        '$or' => 'validateConjunctionCondition',
        '$not' => 'validateBlockCondition',
        '$lt' => 'validateOperatorCondition',
        '$gt' => 'validateOperatorCondition',
        '$lte' => 'validateOperatorCondition',
        '$gte' => 'validateOperatorCondition',
        '$eq' => 'validateOperatorCondition',
        '$neq' => 'validateOperatorCondition',
        '$in' => 'validateOperatorCondition',
        '$nin' => 'validateOperatorCondition',
    ];
    /**
     * @var array specifies the list of supported search attribute type per each operator.
     * This field should be in format: 'operatorKeyword' => ['type1', 'type2' ...].
     * Supported types list can be specified as `*`, which indicates operator supports all available types.
     * Any keyword, which is not present in this specification will not be considered as valid operator.
     */
    public $operatorTypes = [
        '$lt' => [self::TYPE_INTEGER, self::TYPE_FLOAT],
        '$gt' => [self::TYPE_INTEGER, self::TYPE_FLOAT],
        '$lte' => [self::TYPE_INTEGER, self::TYPE_FLOAT],
        '$gte' => [self::TYPE_INTEGER, self::TYPE_FLOAT],
        '$eq' => '*',
        '$neq' => '*',
        '$in' => '*',
        '$nin' => '*',
    ];
    /**
     * @var array list of operators keywords, which should accept multiple values.
     */
    public $multiValueOperators = [
        '$in',
        '$nin',
    ];
    /**
     * @var array list of error messages responding to invalid filter structure, in format: messageKey => messageContent.
     * Message may contain placeholders, which will be populated depending on message context.
     * For each message placeholder `{filter}` is available referring to the label for [[filterAttributeName]] attribute.
     */
    public $errorMessages = [
        'invalidFilter' => 'The format of {filter} is invalid.',
        'operatorRequireMultipleOperands' => 'Operator {operator} requires multiple operands.',
        'unknownAttribute' => 'Unknown filter attribute {attribute}',
        'invalidAttributeValueFormat' => 'Condition for {attribute} should be either a value or valid operator specification.',
        'operatorRequireAttribute' => 'Operator {operator} must be used with search attribute.',
        'unsupportedOperatorType' => '{attribute} does not support operator {operator}.',
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
     * @var array list of search attribute types in format: attributeName => type
     */
    private $_searchAttributeTypes;


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
     * @return array search attribute type map.
     */
    public function getSearchAttributeTypes()
    {
        if ($this->_searchAttributeTypes === null) {
            $this->_searchAttributeTypes = $this->detectSearchAttributeTypes();
        }
        return $this->_searchAttributeTypes;
    }

    /**
     * @param array|null $searchAttributeTypes search attribute type map.
     */
    public function setSearchAttributeTypes($searchAttributeTypes)
    {
        $this->_searchAttributeTypes = $searchAttributeTypes;
    }

    /**
     * Composes default value for [[searchAttributeTypes]] from the [[searchModel]] validation rules.
     * @return array attribute type map.
     */
    protected function detectSearchAttributeTypes()
    {
        $model = $this->getSearchModel();

        $attributeTypes = [];
        foreach ($model->activeAttributes() as $attribute) {
            $attributeTypes[$attribute] = self::TYPE_STRING;
        }

        foreach ($model->getValidators() as $validator) {
            $type = null;
            if ($validator instanceof BooleanValidator) {
                $type = self::TYPE_BOOLEAN;
            } elseif ($validator instanceof NumberValidator) {
                $type = $validator->integerOnly ? self::TYPE_INTEGER : self::TYPE_FLOAT;
            } elseif ($validator instanceof StringValidator) {
                $type = self::TYPE_STRING;
            } elseif ($validator instanceof EachValidator) {
                $type = self::TYPE_ARRAY;
            }

            if ($type !== null) {
                foreach ((array)$validator->attributes as $attribute) {
                    $attributeTypes[$attribute] = $type;
                }
            }
        }
        return $attributeTypes;
    }

    /**
     * Parses content of the message from [[errorMessages]], specified by message key.
     * @param string $messageKey message key.
     * @param array $params params to be parsed into the message.
     * @return string composed message string.
     */
    protected function parseErrorMessage($messageKey, $params = [])
    {
        if (isset($this->errorMessages[$messageKey])) {
            $message = $this->errorMessages[$messageKey];
        } else {
            $message = 'The format of {filter} is invalid.';
        }

        $params = array_merge(
            [
                'filter' => $this->getAttributeLabel($this->filterAttributeName)
            ],
            $params
        );

        return Yii::$app->getI18n()->format($message, $params, Yii::$app->language);
    }

    // Model specific :

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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            $this->filterAttributeName => $this->filterAttributeLabel,
        ];
    }

    // Validation :

    /**
     * Validates filter attribute value to match filer condition specification.
     */
    public function validateFilter()
    {
        $value = $this->getFilter();
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
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('invalidFilter'));
            return;
        }

        if (empty($condition)) {
            return;
        }

        foreach ($condition as $key => $value) {
            if (isset($this->conditionValidators[$key])) {
                $method = $this->conditionValidators[$key];
            } else {
                $method = 'validateAttributeCondition';
            }
            $this->$method($key, $value);
        }
    }

    /**
     * Validates conjunction condition, which consist of multiple independent ones.
     * This covers such operators like `$and` and `$or`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     */
    protected function validateConjunctionCondition($operator, $condition)
    {
        if (!is_array($condition) || !ArrayHelper::isIndexed($condition)) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireMultipleOperands', ['operator' => $operator]));
            return;
        }

        foreach ($condition as $part) {
            $this->validateCondition($part);
        }
    }

    /**
     * Validates block condition, which consist of single condition.
     * This covers such operators like `$not`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     */
    protected function validateBlockCondition($operator, $condition)
    {
        $this->validateCondition($condition);
    }

    /**
     * Validates search condition for particular attribute.
     * @param string $attribute search attribute name.
     * @param mixed $condition search condition.
     */
    protected function validateAttributeCondition($attribute, $condition)
    {
        $attributeTypes = $this->getSearchAttributeTypes();
        if (!isset($attributeTypes[$attribute])) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute]));
            return;
        }

        if (is_array($condition)) {
            $operatorCount = 0;
            foreach ($condition as $operator => $value) {
                if (isset($this->operatorTypes[$operator])) {
                    $operatorCount++;
                    $this->validateOperatorCondition($operator, $value, $attribute);
                }
            }

            if ($operatorCount > 0) {
                if ($operatorCount < count($condition)) {
                    $this->addError($this->filterAttributeName, $this->parseErrorMessage('invalidAttributeValueFormat', ['attribute' => $attribute]));
                }
            } else {
                // attribute may allow array value :
                $this->validateAttributeValue($attribute, $condition);
            }
        } else {
            $this->validateAttributeValue($attribute, $condition);
        }
    }

    /**
     * Validates operator condition.
     * @param string $operator operator keyword.
     * @param mixed $condition attribute condition.
     * @param string $attribute attribute name.
     */
    protected function validateOperatorCondition($operator, $condition, $attribute = null)
    {
        if ($attribute === null) {
            // absence of attribute indicates operator has been placed in wrong position
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireAttribute', ['operator' => $operator]));
            return;
        }

        // check operator type :
        $operatorTypes = $this->operatorTypes[$operator];
        if ($operatorTypes !== '*') {
            $attributeTypes = $this->getSearchAttributeTypes();
            $attributeType = $attributeTypes[$attribute];
            if (!in_array($attributeType, $operatorTypes, true)) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('unsupportedOperatorType', ['attribute' => $attribute, 'operator' => $operator]));
                return;
            }
        }

        if (in_array($operator, $this->multiValueOperators, true)) {
            // multi-value operator :
            if (!is_array($condition)) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireMultipleOperands', ['operator' => $operator]));
            } else {
                foreach ($condition as $v) {
                    $this->validateAttributeValue($attribute, $v);
                }
            }
        } else {
            // single-value operator :
            $this->validateAttributeValue($attribute, $condition);
        }
    }

    /**
     * Validates attribute value in the scope of [[model]].
     * @param string $attribute attribute name.
     * @param mixed $value attribute value.
     */
    protected function validateAttributeValue($attribute, $value)
    {
        $model = $this->getSearchModel();
        if (!$model->isAttributeSafe($attribute)) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute]));
            return;
        }

        $model->{$attribute} = $value;
        if (!$model->validate([$attribute])) {
            $this->addError($this->filterAttributeName, $model->getFirstError($attribute));
            return;
        }
    }

    /**
     * Validates attribute value in the scope of [[searchModel]], applying attribute value filters if any.
     * @param string $attribute attribute name.
     * @param mixed $value attribute value.
     * @return mixed filtered attribute value.
     */
    protected function filterAttributeValue($attribute, $value)
    {
        $model = $this->getSearchModel();
        if (!$model->isAttributeSafe($attribute)) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute]));
            return $value;
        }
        $model->{$attribute} = $value;
        if (!$model->validate([$attribute])) {
            $this->addError($this->filterAttributeName, $model->getFirstError($attribute));
            return $value;
        }

        return $model->{$attribute};
    }

    // Build :

    /**
     * Builds actual filter specification form [[filter]] value.
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before building the filter. Defaults to `true`. If the validation fails, no filter will
     * be built and this method will return `false`.
     * @return mixed|false built actual filter value, or `false` if validation fails.
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