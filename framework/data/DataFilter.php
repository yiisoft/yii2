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
 * DataFilter 是用于处理查询过滤规范的特殊 [[Model]]。
 * 它可以通过请求验证并建立一个过滤条件。
 *
 * 过滤器示例：
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
 * 在实际请求中，过滤器要指定一个和 [[filterAttributeName]] 一致的键名。如，实际的 HTTP 请求 body
 * 如下：
 *
 * ```json
 * {
 *     "filter": {"or": {...}},
 *     "page": 2,
 *     ...
 * }
 * ```
 *
 * 原始过滤器值应该被分配到模型的属性 [[filter]]。
 * 可以通过 [[load()]] 方法来填充 DataFilter:
 *
 * ```php
 * use yii\data\DataFilter;
 *
 * $dataFilter = new DataFilter();
 * $dataFilter->load(Yii::$app->request->getBodyParams());
 * ```
 *
 * 要使用 DataFilter，我们需要通过 [[searchModel]] 来指定搜索 model。这个搜索模型应该声明
 * 所有可用的搜索属性和这些属性的验证规则。例如:
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
 * 为了减少类数量，我们可以使用 [[\yii\base\DynamicModel]] 实例作为 [[searchModel]]。
 * 在这里，我们可以使用 PHP callable 作为 [[searchModel]]：
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
 * 我们可以使用 [[validate()]] 方法来校验过滤值是否可用。如果校验失败，我们可以使用
 * [[getErrors()]] 方法来获取真实的错误信息。
 *
 * 我们可以使用 [[build()]] 方法来获取合适的获取数据的过滤条件。
 *
 * > Note: 该类是个基类。该类的 [[build()]] 方法的简单实现返回了标准的 [[filter]] 值。
 * 我们应该使用恰当的实现了 [[buildInternal()]] 方法的子类来将过滤器转化为特定的
 * 格式。
 *
 * @see ActiveDataFilter
 *
 * @property array $errorMessages `[errorKey => message]` 格式的错误信息。注意这个属性的类型与
 * getter 和 setter 中的不一样。有关详细信息，请参见 [[getErrorMessages()]] 和 [[setErrorMessages()]]。
 * @property mixed $filter 原始过滤器值。
 * @property array $searchAttributeTypes 搜索属性类型映射。注意这个属性的类型与
 * getter 和 setter 中的不一样。有关详细信息，请参见 [[getSearchAttributeTypes()]] 和 [[setSearchAttributeTypes()]]。
 * @property Model $searchModel 模型实例。注意这个属性的类型与
 * getter 和 setter 中的不一样。有关详细信息，请参见 [[getSearchModel()]] 和 [[setSearchModel()]]。
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
     * @var string 通过 [[filterAttributeName]] 指定的过滤器属性标签。
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
     * 如：我们指定了过滤器控件关键字 'like'，同时也有一个属性叫做 'like'，类似于这种属性指定条件是
     * 不会生效的。
     *
     * 我们可以为同一个过滤器构建关键字指定一些关键字，创建多个别名。例如：
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
     * > Note: 在指定过滤器控件时，请记住 API 使用的实际数据交换格式。
     * > 确保每一个指定的控件关键字的格式是合法的。如，在 XML 标签名字中只能
     * > 以字母字符开头，因此，像 `>`，'=' 或者 `$gt` 控件将破坏 XML 模式规范。
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
     * 这些方法被 [[validateCondition()]] 方法使用以校验原始过滤条件。
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
     * 这个字段应该使用这种格式：'operatorKeyword' => ['type1', 'type2' ...]。
     * 支持的类型列表被指定为 `*` 时，表示操作支持所有类型。
     * 任何未指定的关键字都不会被认为是验证操作符。
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
     * @var array 可接受多个值的操作符关键字列表。
     */
    public $multiValueOperators = [
        'IN',
        'NOT IN',
    ];
    /**
     * @var array 在搜索条件中实际使用的属性名称，格式如：[filterAttribute => actualAttribute]。
     * 例如，在使用表连接搜索查询的案例中，属性映射可以像下面这样：
     *
     * ```php
     * [
     *     'authorName' => '{{author}}.[[name]]'
     * ]
     * ```
     *
     * 属性映射将在 [[normalize()]] 方法中被提交到过滤器条件。
     */
    public $attributeMap = [];

    /**
     * @var array|\Closure 响应无效过滤器结构的错误消息列表，格式如：`[errorKey => message]`。
     */
    private $_errorMessages;
    /**
     * @var mixed 原始过滤器规范。
     */
    private $_filter;
    /**
     * @var Model|array|string|callable 用于过滤属性校验的模型
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
     * @return Model 模型实例。
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
     * @param Model|array|string|callable $model 模型实例或它的 di 兼容配置。
     * @throws InvalidConfigException 在验证配置的时候。
     */
    public function setSearchModel($model)
    {
        if (is_object($model) && !$model instanceof Model && !$model instanceof \Closure) {
            throw new InvalidConfigException('`' . get_class($this) . '::$searchModel` should be an instance of `' . Model::className() . '` or its DI compatible configuration.');
        }
        $this->_searchModel = $model;
    }

    /**
     * @return array 搜索属性类型映射。
     */
    public function getSearchAttributeTypes()
    {
        if ($this->_searchAttributeTypes === null) {
            $this->_searchAttributeTypes = $this->detectSearchAttributeTypes();
        }
        return $this->_searchAttributeTypes;
    }

    /**
     * @param array|null $searchAttributeTypes 搜索属性类型映射。
     */
    public function setSearchAttributeTypes($searchAttributeTypes)
    {
        $this->_searchAttributeTypes = $searchAttributeTypes;
    }

    /**
     * 从 [[searchModel]] 验证规则中为 [[searchAttributeTypes]] 合成默认值。
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
     * 按照传入的 validator 检测属性类型
     *
     * @param Validator validator 从这个 validator 中检测属性类型
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
     * 设置验证结构过滤的错误信息响应列表，格式如：`[errorKey => message]`。
     * 消息包含根据消息上下文填充的占位符。
     * 对于每条消息，`{filter}` 占位符是可用的，参见 [[filterAttributeName]] 属性标签
     * @param array|\Closure $errorMessages `[errorKey => message]` 格式的错误新，或者一个返回相同格式的 PHP callback。
     */
    public function setErrorMessages($errorMessages)
    {
        if (is_array($errorMessages)) {
            $errorMessages = array_merge($this->defaultErrorMessages(), $errorMessages);
        }
        $this->_errorMessages = $errorMessages;
    }

    /**
     * [[errorMessages]] 返回的默认值。
     * @return array `[errorKey => message]` 格式的默认错误信息。
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
     * 从 [[errorMessages]] 中解析消息内容，特别是消息关键字。
     * @param string $messageKey 消息关键字。
     * @param array $params 解析成消息的参数。
     * @return string 合成的消息字符串。
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
     * 验证过滤器属性值以匹配过滤器条件规范。
     */
    public function validateFilter()
    {
        $value = $this->getFilter();
        if ($value !== null) {
            $this->validateCondition($value);
        }
    }

    /**
     * 验证过滤器条件。
     * @param mixed $condition 原始过滤器条件。
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
     * 验证包含多个独立条件的连接条件。
     * 包括 `and` 和 `or` 这样的操作符。
     * @param string $operator 原始操作符控制关键字。
     * @param mixed $condition 原始条件。
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
     * 验证包含单个条件的块条件。
     * 包括 `not` 这样的操作符。
     * @param string $operator 原始操作符控制关键字。
     * @param mixed $condition 原始条件。
     */
    protected function validateBlockCondition($operator, $condition)
    {
        $this->validateCondition($condition);
    }

    /**
     * 验证特定属性的搜索条件。
     * @param string $attribute 搜索属性名称。
     * @param mixed $condition 搜索条件。
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
     * 验证操作符条件。
     * @param string $operator 原始操作符控制关键字。
     * @param mixed $condition 属性条件。
     * @param string $attribute 属性名字。
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
     * 在 [[model]] 中验证属性值。
     * @param string $attribute 属性名字。
     * @param mixed $value 属性值。
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
     * 在 [[searchModel]] 中验证属性值，如果有属性过滤器的话，可以使用属性过滤器。
     * @param string $attribute 属性名字。
     * @param mixed $value 属性值。
     * @return mixed 过滤的属性值。
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
     * 从 [[filter]] 值构建实际的过滤器规范。
     * @param bool $runValidation 是否执行验证（[[validate()]] 执行的时候）
     * 在构建过滤器之前，默认值是 true。如果验证失败，没有过滤器
     * 被构建，并且这个方法将返回 `false`。
     * @return mixed|false 以构建的实际过滤器值，验证失败返回 `false`。
     */
    public function build($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        return $this->buildInternal();
    }

    /**
     * 执行实际的过滤器构建。
     * 默认情况下，此方法返回 [[normalize()]] 方法的返回值。
     * 子类可以重写此方法，提供更具体的实现。
     * @return mixed 被构建的实际的过滤器值。
     */
    protected function buildInternal()
    {
        return $this->normalize(false);
    }

    /**
     * 格式化过滤器质，根据 [[filterControls]] 和 [[attributeMap]] 方法替换原始关键字。
     * @param bool $runValidation 是否执行验证（[[validate()]] 执行的时候）
     * 在格式化过滤器之前。默认值是 `true`。如果验证失败，没有过滤器
     * 被执行并且这个方法会返回 `false`。
     * @return array|bool 格式化过滤器值，验证失败将返回 `false`。
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
