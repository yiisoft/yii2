<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * RangeValidator 校验属性值是否在指定列表区间内。
 *
 * 你可以通过 [[range]] 属性指定区间。
 * 如果 [[not]] 属性被设置为 true ，
 * 校验器会确保属性值不在指定区间内。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RangeValidator extends Validator
{
    /**
     * @var array|\Traversable|\Closure 合法值列表，属性值必须为列表元素之一；或者一个匿名函数返回这样的一个列表。
     * 函数的签名如下：
     *
     * ```php
     * function($model, $attribute) {
     *     // compute range
     *     return $range;
     * }
     * ```
     */
    public $range;
    /**
     * @var bool 是否比较是严格的（值和型都必须一样）
     */
    public $strict = false;
    /**
     * @var bool 是否对校验结果取反。默认为 false。如果设置为 true，
     * 属性的值必须不能在 [[range]] 定义的列表值中。
     */
    public $not = false;
    /**
     * @var bool 是否允许数组类型的属性。
     */
    public $allowArray = false;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (!is_array($this->range)
            && !($this->range instanceof \Closure)
            && !($this->range instanceof \Traversable)
        ) {
            throw new InvalidConfigException('The "range" property must be set.');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $in = false;

        if ($this->allowArray
            && ($value instanceof \Traversable || is_array($value))
            && ArrayHelper::isSubset($value, $this->range, $this->strict)
        ) {
            $in = true;
        }

        if (!$in && ArrayHelper::isIn($value, $this->range, $this->strict)) {
            $in = true;
        }

        return $this->not !== $in ? null : [$this->message, []];
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->range instanceof \Closure) {
            $this->range = call_user_func($this->range, $model, $attribute);
        }
        parent::validateAttribute($model, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->range instanceof \Closure) {
            $this->range = call_user_func($this->range, $model, $attribute);
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.range(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $range = [];
        foreach ($this->range as $value) {
            $range[] = (string) $value;
        }
        $options = [
            'range' => $range,
            'not' => $this->not,
            'message' => $this->formatMessage($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->allowArray) {
            $options['allowArray'] = 1;
        }

        return $options;
    }
}
