<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\BooleanValidator;
use yii\validators\EachValidator;
use yii\validators\NumberValidator;
use yii\validators\StringValidator;
use yii\validators\DateValidator;
use yii\validators\Validator;

/**
 * DataFilter is a special [[Model]] for processing query filtering specification.
 * It allows validating and building a filter condition passed via request.
 *
 * Filter example:
 *
 * ```json
 * {
 *     "or": [
 *         {
 *             "and": [
 *                 {
 *                     "name": "some name",
 *                 },
 *                 {
 *                     "price": "25",
 *                 }
 *             ]
 *         },
 *         {
 *             "id": {"in": [2, 5, 9]},
 *             "price": {
 *                 "gt": 10,
 *                 "lt": 50
 *             }
 *         }
 *     ]
 * }
 * ```
 *
 * In the request the filter should be specified using a key name equal to [[filterAttributeName]]. Thus actual HTTP request body
 * will look like following:
 *
 * ```json
 * {
 *     "filter": {"or": {...}},
 *     "page": 2,
 *     ...
 * }
 * ```
 *
 * Raw filter value should be assigned to [[filter]] property of the model.
 * You may populate it from request data via [[load()]] method:
 *
 * ```php
 * use yii\data\DataFilter;
 *
 * $dataFilter = new DataFilter();
 * $dataFilter->load(Yii::$app->request->getBodyParams());
 * ```
 *
 * In order to function this class requires a search model specified via [[searchModel]]. This search model should declare
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
 * In order to reduce amount of classes, you may use [[\yii\base\DynamicModel]] instance as a [[searchModel]].
 * In this case you should specify [[searchModel]] using a PHP callable:
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
 * You can use [[validate()]] method to check if filter value is valid. If validation fails you can use
 * [[getErrors()]] to get actual error messages.
 *
 * In order to acquire filter condition suitable for fetching data use [[build()]] method.
 *
 * > Note: This is a base class. Its implementation of [[build()]] simply returns normalized [[filter]] value.
 * In order to convert filter to particular format you should use descendant of this class that implements
 * [[buildInternal()]] method accordingly.
 *
 * @see ActiveDataFilter
 *
 * @property array $errorMessages Error messages in format `[errorKey => message]`. Note that the type of this
 * property differs in getter and setter. See [[getErrorMessages()]] and [[setErrorMessages()]] for details.
 * @property mixed $filter Raw filter value.
 * @property array $searchAttributeTypes Search attribute type map. Note that the type of this property
 * differs in getter and setter. See [[getSearchAttributeTypes()]] and [[setSearchAttributeTypes()]] for details.
 * @property Model $searchModel Model instance. Note that the type of this property differs in getter and
 * setter. See [[getSearchModel()]] and [[setSearchModel()]] for details.
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
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';

    /**
     * @var string name of the attribute that handles filter value.
     * The name is used to load data via [[load()]] method.
     */
    public $filterAttributeName = 'filter';
    /**
     * @var string label for the filter attribute specified via [[filterAttributeName]].
     * It will be used during error messages composition.
     */
    public $filterAttributeLabel;
    /**
     * @var array keywords or expressions that could be used in a filter.
     * Array keys are the expressions used in raw filter value obtained from user request.
     * Array values are internal build keys used in this class methods.
     *
     * Any unspecified keyword will not be recognized as a filter control and will be treated as
     * an attribute name. Thus you should avoid conflicts between control keywords and attribute names.
     * For example: in case you have control keyword 'like' and an attribute named 'like', specifying condition
     * for such attribute will be impossible.
     *
     * You may specify several keywords for the same filter build key, creating multiple aliases. For example:
     *
     * ```php
     * [
     *     'eq' => '=',
     *     '=' => '=',
     *     '==' => '=',
     *     '===' => '=',
     *     // ...
     * ]
     * ```
     *
     * > Note: while specifying filter controls take actual data exchange format, which your API uses, in mind.
     * > Make sure each specified control keyword is valid for the format. For example, in XML tag name can start
     * > only with a letter character, thus controls like `>`, '=' or `$gt` will break the XML schema.
     */
    public $filterControls = [
        'and' => 'AND',
        'or' => 'OR',
        'not' => 'NOT',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>=',
        'eq' => '=',
        'neq' => '!=',
        'in' => 'IN',
        'nin' => 'NOT IN',
        'like' => 'LIKE',
    ];
    /**
     * @var array maps filter condition keywords to validation methods.
     * These methods are used by [[validateCondition()]] to validate raw filter conditions.
     */
    public $conditionValidators = [
        'AND' => 'validateConjunctionCondition',
        'OR' => 'validateConjunctionCondition',
        'NOT' => 'validateBlockCondition',
        '<' => 'validateOperatorCondition',
        '>' => 'validateOperatorCondition',
        '<=' => 'validateOperatorCondition',
        '>=' => 'validateOperatorCondition',
        '=' => 'validateOperatorCondition',
        '!=' => 'validateOperatorCondition',
        'IN' => 'validateOperatorCondition',
        'NOT IN' => 'validateOperatorCondition',
        'LIKE' => 'validateOperatorCondition',
    ];
    /**
     * @var array specifies the list of supported search attribute types per each operator.
     * This field should be in format: 'operatorKeyword' => ['type1', 'type2' ...].
     * Supported types list can be specified as `*`, which indicates that operator supports all types available.
     * Any unspecified keyword will not be considered as a valid operator.
     */
    public $operatorTypes = [
        '<' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '>' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '<=' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '>=' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '=' => '*',
        '!=' => '*',
        'IN' => '*',
        'NOT IN' => '*',
        'LIKE' => [self::TYPE_STRING],
    ];
    /**
     * @var array list of operators keywords, which should accept multiple values.
     */
    public $multiValueOperators = [
        'IN',
        'NOT IN',
    ];
    /**
     * @var array actual attribute names to be used in searched condition, in format: [filterAttribute => actualAttribute].
     * For example, in case of using table joins in the search query, attribute map may look like the following:
     *
     * ```php
     * [
     *     'authorName' => '{{author}}.[[name]]'
     * ]
     * ```
     *
     * Attribute map will be applied to filter condition in [[normalize()]] method.
     */
    public $attributeMap = [];
    /**
     * @var string representation of `null` instead of literal `null` in case the latter cannot be used.
     * @since 2.0.40
     */
    public $nullValue = 'NULL';

    /**
     * @var array|\Closure list of error messages responding to invalid filter structure, in format: `[errorKey => message]`.
     */
    private $_errorMessages;
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
                throw new InvalidConfigException('`' . get_class($this) . '::$searchModel` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
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
        if (is_object($model) && !$model instanceof Model && !$model instanceof \Closure) {
            throw new InvalidConfigException('`' . get_class($this) . '::$searchModel` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
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
            $type = $this->detectSearchAttributeType($validator);

            if ($type !== null) {
                foreach ((array) $validator->attributes as $attribute) {
                    $attributeTypes[$attribute] = $type;
                }
            }
        }

        return $attributeTypes;
    }

    /**
     * Detect attribute type from given validator.
     *
     * @param Validator $validator validator from which to detect attribute type.
     * @return string|null detected attribute type.
     * @since 2.0.14
     */
    protected function detectSearchAttributeType(Validator $validator)
    {
        if ($validator instanceof BooleanValidator) {
            return self::TYPE_BOOLEAN;
        }

        if ($validator instanceof NumberValidator) {
            return $validator->integerOnly ? self::TYPE_INTEGER : self::TYPE_FLOAT;
        }

        if ($validator instanceof StringValidator) {
            return self::TYPE_STRING;
        }

        if ($validator instanceof EachValidator) {
            return self::TYPE_ARRAY;
        }

        if ($validator instanceof DateValidator) {
            if ($validator->type == DateValidator::TYPE_DATETIME) {
                return self::TYPE_DATETIME;
            }

            if ($validator->type == DateValidator::TYPE_TIME) {
                return self::TYPE_TIME;
            }
            return self::TYPE_DATE;
        }
    }

    /**
     * @return array error messages in format `[errorKey => message]`.
     */
    public function getErrorMessages()
    {
        if (!is_array($this->_errorMessages)) {
            if ($this->_errorMessages === null) {
                $this->_errorMessages = $this->defaultErrorMessages();
            } else {
                $this->_errorMessages = array_merge(
                    $this->defaultErrorMessages(),
                    call_user_func($this->_errorMessages)
                );
            }
        }
        return $this->_errorMessages;
    }

    /**
     * Sets the list of error messages responding to invalid filter structure, in format: `[errorKey => message]`.
     * Message may contain placeholders that will be populated depending on the message context.
     * For each message a `{filter}` placeholder is available referring to the label for [[filterAttributeName]] attribute.
     * @param array|\Closure $errorMessages error messages in `[errorKey => message]` format, or a PHP callback returning them.
     */
    public function setErrorMessages($errorMessages)
    {
        if (is_array($errorMessages)) {
            $errorMessages = array_merge($this->defaultErrorMessages(), $errorMessages);
        }
        $this->_errorMessages = $errorMessages;
    }

    /**
     * Returns default values for [[errorMessages]].
     * @return array default error messages in `[errorKey => message]` format.
     */
    protected function defaultErrorMessages()
    {
        return [
            'invalidFilter' => Yii::t('yii', 'The format of {filter} is invalid.'),
            'operatorRequireMultipleOperands' => Yii::t('yii', 'Operator "{operator}" requires multiple operands.'),
            'unknownAttribute' => Yii::t('yii', 'Unknown filter attribute "{attribute}"'),
            'invalidAttributeValueFormat' => Yii::t('yii', 'Condition for "{attribute}" should be either a value or valid operator specification.'),
            'operatorRequireAttribute' => Yii::t('yii', 'Operator "{operator}" must be used with a search attribute.'),
            'unsupportedOperatorType' => Yii::t('yii', '"{attribute}" does not support operator "{operator}".'),
        ];
    }

    /**
     * Parses content of the message from [[errorMessages]], specified by message key.
     * @param string $messageKey message key.
     * @param array $params params to be parsed into the message.
     * @return string composed message string.
     */
    protected function parseErrorMessage($messageKey, $params = [])
    {
        $messages = $this->getErrorMessages();
        if (isset($messages[$messageKey])) {
            $message = $messages[$messageKey];
        } else {
            $message = Yii::t('yii', 'The format of {filter} is invalid.');
        }

        $params = array_merge(
            [
                'filter' => $this->getAttributeLabel($this->filterAttributeName),
            ],
            $params
        );

        return Yii::$app->getI18n()->format($message, $params, Yii::$app->language);
    }

    // Model specific:

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            $this->filterAttributeName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [$this->filterAttributeName, 'validateFilter', 'skipOnEmpty' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            $this->filterAttributeName => $this->filterAttributeLabel,
        ];
    }

    // Validation:

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
            $method = 'validateAttributeCondition';
            if (isset($this->filterControls[$key])) {
                $controlKey = $this->filterControls[$key];
                if (isset($this->conditionValidators[$controlKey])) {
                    $method = $this->conditionValidators[$controlKey];
                }
            }
            $this->$method($key, $value);
        }
    }

    /**
     * Validates conjunction condition that consists of multiple independent ones.
     * This covers such operators as `and` and `or`.
     * @param string $operator raw operator control keyword.
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
     * Validates block condition that consists of a single condition.
     * This covers such operators as `not`.
     * @param string $operator raw operator control keyword.
     * @param mixed $condition raw condition.
     */
    protected function validateBlockCondition($operator, $condition)
    {
        $this->validateCondition($condition);
    }

    /**
     * Validates search condition for a particular attribute.
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
            foreach ($condition as $rawOperator => $value) {
                if (isset($this->filterControls[$rawOperator])) {
                    $operator = $this->filterControls[$rawOperator];
                    if (isset($this->operatorTypes[$operator])) {
                        $operatorCount++;
                        $this->validateOperatorCondition($rawOperator, $value, $attribute);
                    }
                }
            }

            if ($operatorCount > 0) {
                if ($operatorCount < count($condition)) {
                    $this->addError($this->filterAttributeName, $this->parseErrorMessage('invalidAttributeValueFormat', ['attribute' => $attribute]));
                }
            } else {
                // attribute may allow array value:
                $this->validateAttributeValue($attribute, $condition);
            }
        } else {
            $this->validateAttributeValue($attribute, $condition);
        }
    }

    /**
     * Validates operator condition.
     * @param string $operator raw operator control keyword.
     * @param mixed $condition attribute condition.
     * @param string $attribute attribute name.
     */
    protected function validateOperatorCondition($operator, $condition, $attribute = null)
    {
        if ($attribute === null) {
            // absence of an attribute indicates that operator has been placed in a wrong position
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireAttribute', ['operator' => $operator]));
            return;
        }

        $internalOperator = $this->filterControls[$operator];

        // check operator type :
        $operatorTypes = $this->operatorTypes[$internalOperator];
        if ($operatorTypes !== '*') {
            $attributeTypes = $this->getSearchAttributeTypes();
            $attributeType = $attributeTypes[$attribute];
            if (!in_array($attributeType, $operatorTypes, true)) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('unsupportedOperatorType', ['attribute' => $attribute, 'operator' => $operator]));
                return;
            }
        }

        if (in_array($internalOperator, $this->multiValueOperators, true)) {
            // multi-value operator:
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

        $model->{$attribute} = $value === $this->nullValue ? null : $value;
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

    // Build:

    /**
     * Builds actual filter specification form [[filter]] value.
     * @param bool $runValidation whether to perform validation (calling [[validate()]])
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
     * By default this method returns result of [[normalize()]].
     * The child class may override this method providing more specific implementation.
     * @return mixed built actual filter value.
     */
    protected function buildInternal()
    {
        return $this->normalize(false);
    }

    /**
     * Normalizes filter value, replacing raw keys according to [[filterControls]] and [[attributeMap]].
     * @param bool $runValidation whether to perform validation (calling [[validate()]])
     * before normalizing the filter. Defaults to `true`. If the validation fails, no filter will
     * be processed and this method will return `false`.
     * @return array|bool normalized filter value, or `false` if validation fails.
     */
    public function normalize($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }

        $filter = $this->getFilter();
        if (!is_array($filter) || empty($filter)) {
            return [];
        }

        return $this->normalizeComplexFilter($filter);
    }

    /**
     * Normalizes complex filter recursively.
     * @param array $filter raw filter.
     * @return array normalized filter.
     */
    private function normalizeComplexFilter(array $filter)
    {
        $result = [];
        foreach ($filter as $key => $value) {
            if (isset($this->filterControls[$key])) {
                $key = $this->filterControls[$key];
            } elseif (isset($this->attributeMap[$key])) {
                $key = $this->attributeMap[$key];
            }
            if (is_array($value)) {
                $result[$key] = $this->normalizeComplexFilter($value);
            } elseif ($value === $this->nullValue) {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    // Property access:

    /**
     * {@inheritdoc}
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if ($name === $this->filterAttributeName) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * {@inheritdoc}
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if ($name === $this->filterAttributeName) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($name === $this->filterAttributeName) {
            return $this->getFilter();
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        if ($name === $this->filterAttributeName) {
            return $this->getFilter() !== null;
        }

        return parent::__isset($name);
    }

    /**
     * {@inheritdoc}
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
