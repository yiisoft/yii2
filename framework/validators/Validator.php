<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * Validator 是所有校验器的基类。
 *
 * 子类需要重写 [[validateValue()]] 或者/和 [[validateAttribute()]] 方法，
 * 来提供实际的数据校验逻辑。
 * 子类也可以重写 [[clientValidateAttribute()]] 方法来提供客户端校验功能支持。
 *
 * Validator 声明了一系列内建校验器 [[builtInValidators|built-in validators]] 可以使用短名称来引用。
 * 它们列表如下：
 *
 * - `boolean`: [[BooleanValidator]]
 * - `captcha`: [[\yii\captcha\CaptchaValidator]]
 * - `compare`: [[CompareValidator]]
 * - `date`: [[DateValidator]]
 * - `datetime`: [[DateValidator]]
 * - `time`: [[DateValidator]]
 * - `default`: [[DefaultValueValidator]]
 * - `double`: [[NumberValidator]]
 * - `each`: [[EachValidator]]
 * - `email`: [[EmailValidator]]
 * - `exist`: [[ExistValidator]]
 * - `file`: [[FileValidator]]
 * - `filter`: [[FilterValidator]]
 * - `image`: [[ImageValidator]]
 * - `in`: [[RangeValidator]]
 * - `integer`: [[NumberValidator]]
 * - `match`: [[RegularExpressionValidator]]
 * - `required`: [[RequiredValidator]]
 * - `safe`: [[SafeValidator]]
 * - `string`: [[StringValidator]]
 * - `trim`: [[FilterValidator]]
 * - `unique`: [[UniqueValidator]]
 * - `url`: [[UrlValidator]]
 * - `ip`: [[IpValidator]]
 *
 * 关于校验器的更多细节和使用详情，参阅 [guide article on validators](guide:input-validation)。
 *
 * @property array $attributeNames Attribute names. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Validator extends Component
{
    /**
     * @var array list of built-in validators (name => class or configuration)
     */
    public static $builtInValidators = [
        'boolean' => 'yii\validators\BooleanValidator',
        'captcha' => 'yii\captcha\CaptchaValidator',
        'compare' => 'yii\validators\CompareValidator',
        'date' => 'yii\validators\DateValidator',
        'datetime' => [
            'class' => 'yii\validators\DateValidator',
            'type' => DateValidator::TYPE_DATETIME,
        ],
        'time' => [
            'class' => 'yii\validators\DateValidator',
            'type' => DateValidator::TYPE_TIME,
        ],
        'default' => 'yii\validators\DefaultValueValidator',
        'double' => 'yii\validators\NumberValidator',
        'each' => 'yii\validators\EachValidator',
        'email' => 'yii\validators\EmailValidator',
        'exist' => 'yii\validators\ExistValidator',
        'file' => 'yii\validators\FileValidator',
        'filter' => 'yii\validators\FilterValidator',
        'image' => 'yii\validators\ImageValidator',
        'in' => 'yii\validators\RangeValidator',
        'integer' => [
            'class' => 'yii\validators\NumberValidator',
            'integerOnly' => true,
        ],
        'match' => 'yii\validators\RegularExpressionValidator',
        'number' => 'yii\validators\NumberValidator',
        'required' => 'yii\validators\RequiredValidator',
        'safe' => 'yii\validators\SafeValidator',
        'string' => 'yii\validators\StringValidator',
        'trim' => [
            'class' => 'yii\validators\FilterValidator',
            'filter' => 'trim',
            'skipOnArray' => true,
        ],
        'unique' => 'yii\validators\UniqueValidator',
        'url' => 'yii\validators\UrlValidator',
        'ip' => 'yii\validators\IpValidator',
    ];
    /**
     * @var array|string 将要被这个校验器校验的属性名，或者列表。如果多属性，请通过一个数组设置它们。
     * 对于单属性，你可以使用一个字符串，也可以使用一个数组指定。
     */
    public $attributes = [];
    /**
     * @var string 用户定义的错误消息。
     * 它可以使用如下的占位符，并将会相应地被校验器替换：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * 注意：有一些校验器会引入其他的属性用于指定的校验条件未满足时的错误消息。
     * 关于这些属性的具体详情，请参考具体的类 API 文档。
     * 通常，
     * 这些属性代表最重要的校验规则未满足时的所触发的主要错误消息。
     */
    public $message;
    /**
     * @var array|string 校验器被应用的情景。
     * 对于多情景，请以一个数组的形式指定它们。对于单情景，你可以使用一个字符串或者一个数组。
     */
    public $on = [];
    /**
     * @var array|string 校验器不应该应用的情景。
     * 对于多情景，请以一个数组的形式指定它们。对于单情景，你可以使用一个字符串或者一个数组。
     */
    public $except = [];
    /**
     * @var bool 当被校验的属性根据之前的校验规则已经有一些校验错误时，这个校验规则是否应该被跳过。
     * 默认为 true。
     */
    public $skipOnError = true;
    /**
     * @var bool 当被校验的属性值为 null 或者空字符串时，
     * 是否该跳过这个校验规则。
     */
    public $skipOnEmpty = true;
    /**
     * @var bool 是否启用这个校验器的客户端校验。
     * 实际的校验过程是通过 [[clientValidateAttribute()]] 返回的 JS 代码来执行。
     * 如果这个方法返回 null，
     * 即使这个属性值为 true ，也不会执行任何客户端校验。
     */
    public $enableClientValidation = true;
    /**
     * @var callable 用于替换默认的 [[isEmpty()]] 空值校验方法，
     * 如果没有设置，将会使用 [[isEmpty()]] 做空值校验。
     * 这个函数的声明应该为 `function ($value)`，
     * 它的返回值为一个代表这个值是否为空的布尔值。
     */
    public $isEmpty;
    /**
     * @var callable 一个PHP函数调用，它的返回值将会决定这个校验器是否被应用。
     * 这个函数的声明应该为 `function ($model, $attribute)` ，其中 `$model` 和 `$attribute` 代表被校验的模型和属性。
     * 这个函数应该返回一个布尔值。
     *
     * 这个属性主要用于支持服务端条件校验。
     * 如果这个属性没有被设置，这个校验器将会总是在服务端执行校验。
     *
     * 以下是一个示例，只有当前选择的国家为 USA 时，才会在服务端执行此校验器：
     *
     * ```php
     * function ($model) {
     *     return $model->country == Country::USA;
     * }
     * ```
     *
     * @see whenClient
     */
    public $when;
    /**
     * @var string 这是一个 JS 函数，它的返回值将会决定校验器是否在客户端执行。
     * 这个函数的声明应该为 `function (attribute, value)` ，
     * 其中 `attribute` 代表被要被校验的属性对象（参考： [[clientValidateAttribute()]]），
     * `value` 是属性当前值。
     *
     * 这个属性主要用于支持客户端条件校验。
     * 如果这个属性没有被设置，这个校验器将会总是在客户端执行校验。
     *
     * 以下是一个示例，只有当前选择的国家为 USA 时，才会在客户端执行此校验器：
     *
     * ```javascript
     * function (attribute, value) {
     *     return $('#country').val() === 'USA';
     * }
     * ```
     *
     * @see when
     */
    public $whenClient;


    /**
     * 创建校验器对象。
     * @param string|\Closure $type 校验器类型，可以为:
     *  * [[builtInValidators]] 列表中的内建校验器名称;
     *  * 模型类的方法名;
     *  * 匿名函数;
     *  * 校验器类名。
     * @param \yii\base\Model $model 被校验的数据模型。
     * @param array|string $attributes 被校验的属性。它可以是一个属性名称数组，
     * 也可以是逗号分隔的属性名称字符串。
     * @param array $params 校验器属性的初始值。
     * @return Validator the validator
     */
    public static function createValidator($type, $model, $attributes, $params = [])
    {
        $params['attributes'] = $attributes;

        if ($type instanceof \Closure || ($model->hasMethod($type) && !isset(static::$builtInValidators[$type]))) {
            // method-based validator
            $params['class'] = __NAMESPACE__ . '\InlineValidator';
            $params['method'] = $type;
        } else {
            if (isset(static::$builtInValidators[$type])) {
                $type = static::$builtInValidators[$type];
            }
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }
        }

        return Yii::createObject($params);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->attributes = (array) $this->attributes;
        $this->on = (array) $this->on;
        $this->except = (array) $this->except;
    }

    /**
     * 校验指定的对象。
     * @param \yii\base\Model $model 被校验的数据模型
     * @param array|string|null $attributes 被校验的属性列表
     * 注意，如果一个属性没有和校验器关联，它会被忽略。
     * 如果这个参数为 null，[[attributes]] 里面的每个属性都会被校验。
     */
    public function validateAttributes($model, $attributes = null)
    {
        $attributes = $this->getValidationAttributes($attributes);

        foreach ($attributes as $attribute) {
            $skip = $this->skipOnError && $model->hasErrors($attribute)
                || $this->skipOnEmpty && $this->isEmpty($model->$attribute);
            if (!$skip) {
                if ($this->when === null || call_user_func($this->when, $model, $attribute)) {
                    $this->validateAttribute($model, $attribute);
                }
            }
        }
    }

    public function getValidationAttributes($attributes = null)
    {
        if ($attributes === null) {
            return $this->getAttributeNames();
        }

        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        $newAttributes = [];
        $attributeNames = $this->getAttributeNames();
        foreach ($attributes as $attribute) {
            if (in_array($attribute, $attributeNames, true)) {
                $newAttributes[] = $attribute;
            }
        }
        return $newAttributes;
    }

    /**
     * 校验单个属性。
     * 子类必须实现这个方法以实现具体的校验逻辑。
     * @param \yii\base\Model $model 被校验的数据模型对象。
     * @param string $attribute 被校验的属性名称。
     */
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * 校验一个指定的值。
     * 你可以使用这个方法在数据模型上下文之外的地方校验一个值。
     * @param mixed $value 被校验的数据值。
     * @param string $error 如果校验失败，被返回的错误消息
     * @return bool 数据是否合法
     */
    public function validate($value, &$error = null)
    {
        $result = $this->validateValue($value);
        if (empty($result)) {
            return true;
        }

        list($message, $params) = $result;
        $params['attribute'] = Yii::t('yii', 'the input value');
        if (is_array($value)) {
            $params['value'] = 'array()';
        } elseif (is_object($value)) {
            $params['value'] = 'object';
        } else {
            $params['value'] = $value;
        }
        $error = $this->formatMessage($message, $params);

        return false;
    }

    /**
     * 校验一个值。
     * 一个校验类可以实现这个方法，以支持在数据模型上下文之外的地方支持数据校验。
     * @param mixed $value 被校验的数据值。
     * @return array|null 错误消息，和可用于替换错误消息中占位符的参数。
     * ```php
     * if (!$valid) {
     *     return [$this->message, [
     *         'param1' => $this->param1,
     *         'formattedLimit' => Yii::$app->formatter->asShortSize($this->getSizeLimit()),
     *         'mimeTypes' => implode(', ', $this->mimeTypes),
     *         'param4' => 'etc...',
     *     ]];
     * }
     *
     * return null;
     * ```
     * 对于这个例子 `message` 模板可以包含 `{param1}`, `{formattedLimit}`, `{mimeTypes}`, `{param4}`
     *
     * 如果数据是合法的，返回 null。
     * @throws NotSupportedException 如果校验器不支持模型外数据校验。
     */
    protected function validateValue($value)
    {
        throw new NotSupportedException(get_class($this) . ' does not support validateValue().');
    }

    /**
     * 返回可用于客户端校验的 JS 代码。
     *
     * 调用 [[getClientOptions()]] 来生成客户端校验数组。
     *
     * 如果这个校验器可以支持客户端校验的话，
     * 你可以重写这个方法来返回 JS 校验代码。
     *
     * 如下预定义 JS  变量可以用于校验代码中：
     *
     * - `attribute`: 描述被校验属性的对象
     * - `value`: 被校验的值
     * - `messages`: 保存属性校验错误消息的数组
     * - `deferred`: 保存 deferred 对象的数组用于异步执行校验
     * - `$form`: jQuery 对象用于保存表单元素
     *
     * `attribute` 包含如下属性：
     * - `id`: 唯一 ID 用于在表单中标识这个属性（例如： "loginform-username"）
     * - `name`: 属性名称或表达式（例如：表单输入时，可以为 "[0]content" ）
     * - `container`: 输入容器的 jQuery 选择器
     * - `input`: 表单上下文输入字段的 jQuery 选择器
     * - `error`: 容器上下文错误标签的 jQuery 选择器
     * - `status`: 输入字段的状态，0：空的，没有输入，1：校验过了，2：待校验，3：校验中
     *
     * @param \yii\base\Model $model 被校验的数据模型。
     * @param string $attribute 待校验的属性名称
     * @param \yii\web\View $view 将要被用于渲染视图或者视图文件的视图对象
     * 包含应用校验器的表单模型。
     * @return string|null 客户端校验脚本。如果校验器不支持的话，返回 null。
     * 客户端校验。
     * @see getClientOptions()
     * @see \yii\widgets\ActiveForm::enableClientValidation
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }

    /**
     * 返回客户端校验参数。
     * 这个方法通常在 [[clientValidateAttribute()]] 中调用。
     * 你可以改写这个方法用于修改传给客户端校验的参数。
     * @param \yii\base\Model $model 被校验的模型。
     * @param string $attribute 被校验的属性名称。
     * @return array 客户端校验参数。
     * @since 2.0.11
     */
    public function getClientOptions($model, $attribute)
    {
        return [];
    }

    /**
     * 返回一个值代表当前校验器在当前情景和属性下是否是激活状态。
     *
     * 一个校验器是激活状态，如果：
     *
     * - 这个校验器的 `on` 属性是空的，或者
     * - 这个校验器的 `on` 属性包含指定的情景。
     *
     * @param string $scenario 情景名称
     * @return bool 是否这个校验器可以在该情景下应用。
     */
    public function isActive($scenario)
    {
        return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
    }

    /**
     * 添加指定属性的错误到模型对象中。
     * 这是一个帮助方法用于执行消息的选择和国际化。
     * @param \yii\base\Model $model 被校验的数据模型
     * @param string $attribute 被校验的属性
     * @param string $message 错误消息
     * @param array $params 用于替换错误消息中占位符的变量
     */
    public function addError($model, $attribute, $message, $params = [])
    {
        $params['attribute'] = $model->getAttributeLabel($attribute);
        if (!isset($params['value'])) {
            $value = $model->$attribute;
            if (is_array($value)) {
                $params['value'] = 'array()';
            } elseif (is_object($value) && !method_exists($value, '__toString')) {
                $params['value'] = '(object)';
            } else {
                $params['value'] = $value;
            }
        }
        $model->addError($attribute, $this->formatMessage($message, $params));
    }

    /**
     * 检测指定的值是否为空。
     * 如果它是一个 null，一个空的数组，或者一个空字符串，那么它会被认为是一个空值。
     * 注意这个方法和 PHP 的 empty() 函数不同，当值为 0 时，它会返还 false 。（非空值）
     * @param mixed $value 待检测的值
     * @return bool 值是否为空
     */
    public function isEmpty($value)
    {
        if ($this->isEmpty !== null) {
            return call_user_func($this->isEmpty, $value);
        }

        return $value === null || $value === [] || $value === '';
    }

    /**
     * 使用 I18N 格式化消息，或者只是简单的 strtr ，如果 `\Yii::$app` 不可用的话。
     * @param string $message
     * @param array $params
     * @since 2.0.12
     * @return string
     */
    protected function formatMessage($message, $params)
    {
        if (Yii::$app !== null) {
            return \Yii::$app->getI18n()->format($message, $params, Yii::$app->language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

    /**
     * 返回去除开头 `!` 的属性名称。
     * @return array 属性名称列表.
     * @since 2.0.12
     */
    public function getAttributeNames()
    {
        return array_map(function ($attribute) {
            return ltrim($attribute, '!');
        }, $this->attributes);
    }
}
