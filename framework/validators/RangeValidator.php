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
     * ```
     * function($model, $attribute) {
     *     // compute range
     *     return $range;
     * }
     * ```
     *
     * The list may contain enum cases (PHP 8.1+), e.g. `Status::cases()`. In this case both the enum case
     * itself and its scalar representation (the backing value of a backed enum or the case name of a pure enum)
     * are considered valid, so that a value submitted by a form is accepted before it is typecast to the enum.
     * The scalar representation is also used for the client-side validation.
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
        if (
            !is_array($this->range)
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

        $range = $this->normalizeRange($this->materializeRange());

        if (
            $this->allowArray
            && ($value instanceof \Traversable || is_array($value))
            && ArrayHelper::isSubset($value, $range, $this->strict)
        ) {
            $in = true;
        }

        if (!$in && ArrayHelper::isIn($value, $range, $this->strict)) {
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

        foreach ($this->materializeRange() as $value) {
            $range[] = (string) $this->enumToScalar($value);
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

    /**
     * Converts an enum case to its scalar representation.
     *
     * The backing value of a backed enum or the case name of a pure enum is returned.
     * Any other value is returned as is.
     *
     * @param mixed $value the value to be converted.
     * @return mixed the scalar representation of the enum case, or the original value.
     */
    private function enumToScalar($value)
    {
        if (PHP_VERSION_ID >= 80100) {
            if ($value instanceof \BackedEnum) {
                return $value->value;
            }
            if ($value instanceof \UnitEnum) {
                return $value->name;
            }
        }

        return $value;
    }

    /**
     * Materializes a traversable [[range]] into an array and returns the resulting range.
     *
     * The array replaces the traversable in [[range]], so that a one-shot traversable such as a generator
     * can be used for more than one validation and for the client options.
     *
     * @return mixed the materialized range, or the original value of [[range]] when it is not traversable.
     */
    private function materializeRange()
    {
        if ($this->range instanceof \Traversable) {
            $this->range = iterator_to_array($this->range, false);
        }

        return $this->range;
    }

    /**
     * Adds the scalar representation of each enum case found in the range.
     *
     * The enum cases are kept in the range, so that an enum case is only matched by itself (not by a case
     * of another enum with the same backing value), while its scalar representation matches the value
     * submitted by a form.
     *
     * @param mixed $range the materialized range, see [[materializeRange()]].
     * @return mixed the range with the scalar representation of the enum cases added, or the original
     * value when it is not an array.
     */
    private function normalizeRange($range)
    {
        if (!is_array($range)) {
            return $range;
        }

        $normalized = [];

        foreach ($range as $value) {
            $normalized[] = $value;
            if (PHP_VERSION_ID >= 80100 && $value instanceof \UnitEnum) {
                $normalized[] = $this->enumToScalar($value);
            }
        }

        return $normalized;
    }
}
