<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;

/**
 * StringValidator 校验属性值是否满足规定的长度。
 *
 * 注意，这个校验器只用于字符串类型的属性。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StringValidator extends Validator
{
    /**
     * @var int|array 指定被校验的值的长度限制
     * 这个属性可以是如下的格式：
     *
     * - an integer: 这个值的确切长度
     * - an array of one element: 这个值的最小长度。如：`[8]`。
     *   这样会重写 [[min]] 的值。
     * - an array of two elements: 这个值的最小和最大长度。
     *   例如：`[8, 128]`。这样会同时重写 [[min]] 和 [[max]]。
     * @see tooShort for the customized message for a too short string.
     * @see tooLong for the customized message for a too long string.
     * @see notEqual for the customized message for a string that does not match desired length.
     */
    public $length;
    /**
     * @var int 最大长度，如果没有设置，它意味着没有最大长度限制。
     * @see 参考 tooLong 自定义超长时的错误消息。
     */
    public $max;
    /**
     * @var int 最小长度。如果未设置，它意味着没有最小长度限制。
     * @see tooShort 参考 tooShort 自定义太短时的错误消息。
     */
    public $min;
    /**
     * @var string 当值不是字符串时的用户自定义错误消息。
     */
    public $message;
    /**
     * @var string 当长度短于 [[min]] 时的自定义错误消息。
     */
    public $tooShort;
    /**
     * @var string 当长度大于 [[max]] 时的自定义错误消息。
     */
    public $tooLong;
    /**
     * @var string 当长度不等于 [[length]] 时的自定义错误消息。
     */
    public $notEqual;
    /**
     * @var string 待校验值的字符编码（例如：'UTF-8'）。
     * 如果这个属性没有设置，默认使用 [[\yii\base\Application::charset]]。
     */
    public $encoding;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->encoding === null) {
            $this->encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be a string.');
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = Yii::t('yii', '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.');
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = Yii::t('yii', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.');
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual = Yii::t('yii', '{attribute} should contain {length, number} {length, plural, one{character} other{characters}}.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, $this->tooLong, ['max' => $this->max]);
        }
        if ($this->length !== null && $length !== $this->length) {
            $this->addError($model, $attribute, $this->notEqual, ['length' => $this->length]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }
        if ($this->max !== null && $length > $this->max) {
            return [$this->tooLong, ['max' => $this->max]];
        }
        if ($this->length !== null && $length !== $this->length) {
            return [$this->notEqual, ['length' => $this->length]];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => $this->formatMessage($this->message, [
                'attribute' => $label,
            ]),
        ];

        if ($this->min !== null) {
            $options['min'] = $this->min;
            $options['tooShort'] = $this->formatMessage($this->tooShort, [
                'attribute' => $label,
                'min' => $this->min,
            ]);
        }
        if ($this->max !== null) {
            $options['max'] = $this->max;
            $options['tooLong'] = $this->formatMessage($this->tooLong, [
                'attribute' => $label,
                'max' => $this->max,
            ]);
        }
        if ($this->length !== null) {
            $options['is'] = $this->length;
            $options['notEqual'] = $this->formatMessage($this->notEqual, [
                'attribute' => $label,
                'length' => $this->length,
            ]);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
