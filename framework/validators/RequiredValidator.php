<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;

/**
 * RequiredValidator 校验指定的属性不为 null 值或者空值。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequiredValidator extends Validator
{
    /**
     * @var bool 当被校验值为空时，是否跳过这个校验器。
     */
    public $skipOnEmpty = false;
    /**
     * @var mixed 这个属性是否必须含有预期的值。
     * 如果它被设置为 null， 校验器将会校验指定的属性值非空。
     * 如果它被设置为一个非 null 值，
     * 校验器将会校验指定的属性值和这个成员的值相同。
     * 默认为 null。
     * @see strict
     */
    public $requiredValue;
    /**
     * @var bool 属性值和 [[requiredValue]] 的比较是否是严格的。
     * 当它被设置为 true 时，属性值和 [[requiredValue]] 的值和型必须匹配。
     * 默认是 false ，意味着只需要值匹配。
     * 注意当 [[requiredValue]] 为 null 时，
     * 如果这个值为 true 时，校验器将会检查属性值是否是 null；
     * 如果这个成员值是 false，校验器将会调用 [[isEmpty]] 来检查这个属性值是否为空。
     */
    public $strict = false;
    /**
     * @var string 用户自定义错误消息。
     * 它可以包含如下的占位符，将根据具体的校验器作替换：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     * - `{requiredValue}`: [[requiredValue]] 的值
     */
    public $message;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->requiredValue === null ? Yii::t('yii', '{attribute} cannot be blank.')
                : Yii::t('yii', '{attribute} must be "{requiredValue}".');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->requiredValue === null) {
            if ($this->strict && $value !== null || !$this->strict && !$this->isEmpty(is_string($value) ? trim($value) : $value)) {
                return null;
            }
        } elseif (!$this->strict && $value == $this->requiredValue || $this->strict && $value === $this->requiredValue) {
            return null;
        }
        if ($this->requiredValue === null) {
            return [$this->message, []];
        }

        return [$this->message, [
            'requiredValue' => $this->requiredValue,
        ]];
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.required(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $options = [];
        if ($this->requiredValue !== null) {
            $options['message'] = $this->formatMessage($this->message, [
                'requiredValue' => $this->requiredValue,
            ]);
            $options['requiredValue'] = $this->requiredValue;
        } else {
            $options['message'] = $this->message;
        }
        if ($this->strict) {
            $options['strict'] = 1;
        }

        $options['message'] = $this->formatMessage($options['message'], [
            'attribute' => $model->getAttributeLabel($attribute),
        ]);

        return $options;
    }
}
