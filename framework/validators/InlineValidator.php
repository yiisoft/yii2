<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * InlineValidator 代表一个被定义为待校验对象的某个方法的校验器。
 *
 * 这个校验方法必须符合如下函数声明：
 *
 * ```php
 * function foo($attribute, $params, $validator)
 * ```
 *
 * 其中，`$attribute` 代表要校验的属性名字，`$params` 是一个数组，用于提供补充的校验规则。
 * `$validator` 指向关联的 [[InlineValidator]] 对象，
 * 并且，这个属性从 2.0.11 版本才开始支持。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineValidator extends Validator
{
    /**
     * @var string|\Closure 匿名函数，或者模型类方法名，用于执行实际的校验过程。
     * 方法的签名必须类似如下：
     *
     * ```php
     * function foo($attribute, $params, $validator)
     * ```
     *
     * - `$attribute` is the name of the attribute to be validated;
     * - `$params` contains the value of [[params]] that you specify when declaring the inline validation rule;
     * - `$validator` is a reference to related [[InlineValidator]] object. This parameter is available since version 2.0.11.
     */
    public $method;
    /**
     * @var mixed 传递给校验方法的额外参数。
     */
    public $params;
    /**
     * @var string|\Closure 一个匿名函数或者模型类方法名，用于返回客户端校验代码。
     * 方法的签名必须类似如下：
     *
     * ```php
     * function foo($attribute, $params, $validator)
     * {
     *     return "javascript";
     * }
     * ```
     *
     * 其中 `$attribute` 指代被校验的属性名称。
     *
     * 关于如何返回客户端校验代码的更多详情，请参考 [[clientValidateAttribute()]]。
     */
    public $clientValidate;


    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        }
        call_user_func($method, $attribute, $this->params, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->clientValidate !== null) {
            $method = $this->clientValidate;
            if (is_string($method)) {
                $method = [$model, $method];
            }

            return call_user_func($method, $attribute, $this->params, $this);
        }

        return null;
    }
}
