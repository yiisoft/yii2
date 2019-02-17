<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\JsExpression;

/**
 * NumberValidator 校验属性的值是否是数字。
 *
 * 数字的格式必须符合 [[integerPattern]]
 * 或者 [[numberPattern]] 所指定的正则表达式。
 * 另外，你可以配置 [[max]] 和 [[min]] 属性来确保数值是在指定的区间内。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NumberValidator extends Validator
{
    /**
     * @var bool 是否属性的值只能为整数。默认为 false。
     */
    public $integerOnly = false;
    /**
     * @var int|float 数值的上限。默认为 null， 意味着没有上限。
     * @see tooBig 参考 tooBig 自定义超过上限时的错误消息。
     */
    public $max;
    /**
     * @var int|float 数值的下限。默认为 null，意味着没有下限。
     * @see tooSmall 参考 tooSmall 自定义低于下限时的错误消息。
     */
    public $min;
    /**
     * @var string 用户自定义错误消息用于值超过 [[max]]。
     */
    public $tooBig;
    /**
     * @var string 用户自定义错误消息用于值低于 [[min]]。
     */
    public $tooSmall;
    /**
     * @var string 用于匹配整数的正则表达式。
     */
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string 用于匹配数字的正则表达式。
     * 默认为一个匹配包含对数部分的浮点数字的正则表达式。
     */
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->integerOnly ? Yii::t('yii', '{attribute} must be an integer.')
                : Yii::t('yii', '{attribute} must be a number.');
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = Yii::t('yii', '{attribute} must be no less than {min}.');
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = Yii::t('yii', '{attribute} must be no greater than {max}.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->isNotNumber($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;

        if (!preg_match($pattern, StringHelper::normalizeNumber($value))) {
            $this->addError($model, $attribute, $this->message);
        }
        if ($this->min !== null && $value < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->min]);
        }
        if ($this->max !== null && $value > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->max]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->isNotNumber($value)) {
            return [Yii::t('yii', '{attribute} is invalid.'), []];
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, StringHelper::normalizeNumber($value))) {
            return [$this->message, []];
        } elseif ($this->min !== null && $value < $this->min) {
            return [$this->tooSmall, ['min' => $this->min]];
        } elseif ($this->max !== null && $value > $this->max) {
            return [$this->tooBig, ['max' => $this->max]];
        }

        return null;
    }

    /*
     * @param mixed $value the data value to be checked.
     */
    private function isNotNumber($value)
    {
        return is_array($value)
            || is_bool($value)
            || (is_object($value) && !method_exists($value, '__toString'))
            || (!is_object($value) && !is_scalar($value) && $value !== null);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'pattern' => new JsExpression($this->integerOnly ? $this->integerPattern : $this->numberPattern),
            'message' => $this->formatMessage($this->message, [
                'attribute' => $label,
            ]),
        ];

        if ($this->min !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/yiisoft/yii2/issues/3118
            $options['min'] = is_string($this->min) ? (float) $this->min : $this->min;
            $options['tooSmall'] = $this->formatMessage($this->tooSmall, [
                'attribute' => $label,
                'min' => $this->min,
            ]);
        }
        if ($this->max !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/yiisoft/yii2/issues/3118
            $options['max'] = is_string($this->max) ? (float) $this->max : $this->max;
            $options['tooBig'] = $this->formatMessage($this->tooBig, [
                'attribute' => $label,
                'max' => $this->max,
            ]);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
