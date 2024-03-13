<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * RangeValidator validates that the attribute value is among a list of values.
 *
 * The range can be specified via the [[range]] property.
 * If the [[not]] property is set true, the validator will ensure the attribute value
 * is NOT among the specified range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RangeValidator extends Validator
{
    /**
     * @var array|\Traversable|\Closure a list of valid values that the attribute value should be among or an anonymous function that returns
     * such a list. The signature of the anonymous function should be as follows,
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
     * @var bool whether the comparison is strict (both type and value must be the same)
     */
    public $strict = false;
    /**
     * @var bool whether to invert the validation logic. Defaults to false. If set to true,
     * the attribute value should NOT be among the list of values defined via [[range]].
     */
    public $not = false;
    /**
     * @var bool whether to allow array type attribute.
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

        return 'yii.validation.range(value, messages, ' . Json::htmlEncode($options) . ');';
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
