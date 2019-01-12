<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use yii\base\InvalidConfigException;

/**
 * FilterValidator converts the attribute value according to a filter.
 * FilterValidator 通过一个过滤器函数来转换属性值为要求的形式。
 *
 * FilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter callback to process the attribute value
 * and save the processed value back to the attribute. The filter must be
 * a valid PHP callback with the following signature:
 * FilterValidator 实际上并不是一个校验器，而是一个数据处理器。
 * 它调用指定的过滤器函数来处理属性值，并将处理后的值写回属性。过滤器必须是一个符合如下声明的PHP回调函数：
 *
 * ```php
 * function foo($value) {
 *     // compute $newValue here
 *     return $newValue;
 * }
 * ```
 *
 * Many PHP functions qualify this signature (e.g. `trim()`).
 * 许多PHP内置函数符合这个声明（例如：`trim()`）。
 *
 * To specify the filter, set [[filter]] property to be the callback.
 * 通过设置 [[filter]] 属性为一个函数回调来指定这个过滤器。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FilterValidator extends Validator
{
    /**
     * @var callable the filter. This can be a global function name, anonymous function, etc.
     * The function signature must be as follows,
     * @var 过滤器函数调用。它可以是一个全局函数名，匿名函数，等。
     * 函数签名类似如下：
     *
     * ```php
     * function foo($value) {
     *     // compute $newValue here
     *     return $newValue;
     * }
     * ```
     */
    public $filter;
    /**
     * @var bool whether the filter should be skipped if an array input is given.
     * If true and an array input is given, the filter will not be applied.
     * @var bool 是否当输入为数组时，是否跳过这个过滤器。
     * 如果是 true ，输入为数组，那么过滤器将会被跳过。
     */
    public $skipOnArray = false;
    /**
     * @var bool this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     * @var bool 这个属性被重写为 false，这样，这个校验器当值为空的时候也会被应用。
     */
    public $skipOnEmpty = false;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->filter === null) {
            throw new InvalidConfigException('The "filter" property must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!$this->skipOnArray || !is_array($value)) {
            $model->$attribute = call_user_func($this->filter, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->filter !== 'trim') {
            return null;
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'value = yii.validation.trim($form, attribute, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', value);';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $options = [];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
