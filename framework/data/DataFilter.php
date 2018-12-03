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
 * DataFilter是用于处理查询过滤规范的特殊[[Model]]。
 * 它可以通过请求验证并建立一个过滤条件。
 *
 * 过滤器示例:
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
 * 在实际请求中，过滤器要指定一个和[[filterAttributeName]]一致的键名。如，实际的HTTP请求body
 * 如下:
 *
 * ```json
 * {
 *     "filter": {"or": {...}},
 *     "page": 2,
 *     ...
 * }
 * ```
 *
 * 原始过滤器值应该被分配到model的属性[[filter]]。
 * 可以通过 [[load()]] 方法来填充DataFilter:
 *
 * ```php
 * use yii\data\DataFilter;
 *
 * $dataFilter = new DataFilter();
 * $dataFilter->load(Yii::$app->request->getBodyParams());
 * ```
 *
 * 要使用DataFilter，我们需要通过 [[searchModel]] 来指定搜索model。 这个搜索model应该声明
 * 所有可用的搜索属性和这些属性的验证规则。 例如:
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
 * 为了减少类数量, 我们可以使用 [[\yii\base\DynamicModel]] 实例作为 [[searchModel]]。
 * 在这里，我们可以使用PHP callable作为 [[searchModel]] :
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
 * 我们可以使用 [[validate()]] 方法来校验过滤值是否可用。 如果校验失败，我们可以使用
 * [[getErrors()]] 方法来获取真实的错误信息.
 *
 * 我们可以使用 [[build()]] 方法来获取合适的获取数据的过滤条件。
 *
 * > 注意: 该类是个基类。 该类的 [[build()]] 方法的简单实现返回了标准的 [[filter]] 值。
 * 我们应该使用恰当的实现了 [[buildInternal()]] 方法的子类来将过滤器转化为特定的
 * 格式。
 *
 * @see ActiveDataFilter
 *
 * @property array $errorMessages  `[errorKey => message]` 格式的错误信息。注意这个属性的类型
 * 与getter和setter中的不一样。 有关详细信息，请参见 [[getErrorMessages()]] 和 [[setErrorMessages()]] 。
 * @property mixed $filter 原始过滤器值。
 * @property array $searchAttributeTypes 搜索属性类型映射。 注意这个属性的类型
 * 与getter和setter中的不一样。 有关详细信息，请参见 [[getSearchAttributeTypes()]] 和 [[setSearchAttributeTypes()]] 。
 * @property Model $searchModel Model实例。注意这个属性的类型
 * 与getter和setter中的不一样。有关详细信息，请参见 [[getSearchModel()]] 和 [[setSearchModel()]]。
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
     * @var string 处理过滤器值的属性名称。
     * 这个名字用于通过 [[load()]] 方法加载数据。
     */
    public $filterAttributeName = 'filter';
    /**
     * @var string 通过 [[filterAttributeName]] 指定的过滤器属性标签.
     * 将在错误消息合成中使用。
     */
    public $filterAttributeLabel;
    /**
     * @var array 在过滤器中可能用到的关键字和表达式。
     * 数组键是从用户请求中获取的在原始过滤器值中使用的表达式。
     * 数组值是在该类方法中使用的内部构建的关键字。
     *
     * 任何未指定的关键字将不被识别为过滤器控件，同时都将被视为
     * 属性名。因此我们应该避免过滤器控件关键字和属性名称之间的冲突。
     * 如:我们指定了过滤器控件关键字'like'，同时也有一个属性叫做'like',类似于这种属性指定条件是
     * 不会生效的。
     *
     * 我们可以为同一个过滤器构建关键字指定一些关键字，创建多个别名。 例如:
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
     * > 注意: 在指定过滤器控件时，请记住API使用的实际数据交换格式。
     * > 确保每一个指定的控件关键字的格式是合法的。 如, 在XML标签名字中只能
     * > 以字母字符开头, 因此，像 `>`, '=' 或者 `$gt` 控件将破坏XML模式规范。
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
     * @var array 过滤器条件关键字和校验方法的映射。
     * 这些方法被 [[validateCondition()]]方法使用以校验原始过滤条件。
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
     * @var array 指定每个操作符支持的搜索属性类型的列表。
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
     * @var array|\Closure 响应无效过滤器结构的错误消息列表, 格式如：`[errorKey => message]`.
     */
    private $_errorMessages;
    /**
     * @var mixed 原始过滤器规范。
     */
    private $_filter;
    /**
     * @var Model|array|string|callable 用于过滤属性校验的model
     */
    private $_searchModel;
    /**
     * @var array 搜索属性类型列表，格式如：attributeName => type
     */
    private $_searchAttributeTypes;


    /**
     * @return mixed 原始过滤器值
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param mixed $filter 原始过滤器值。
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
    }

    /**
     * @return Model model实例。
     * @throws InvalidConfigException 在配置校验中。
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
     * @return array 属性类型映射。
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
     * 按照传入的validator检测属性类型
     *
     * @param Validator validator 从这个validator中检测属性类型
     * @return string|null 检测出来的属性类型
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
     * @return array 错误信息，格式如：`[errorKey => message]`。
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

    // 指定 Model:

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

    // 校验:

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
     * 递归形式整合标准过滤器。
     * @param array $filter 原始过滤器。
     * @return array 标准过滤器。
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
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    // 属性访问:

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
