<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * CompareValidator 用于将指定的属性的值和其他值进行比较。
 *
 * 被比较的值可以是其他的属性的值（通过 [[compareAttribute]] 字段设置），
 * 也可以是一个常量（ 通过 [[compareValue]] 设置）。
 * 如果两个都设置了，后者优先。
 * 如果都没有设置，
 * 这个属性将会跟其他以当前属性名为前缀，
 * 以 "_repeat" 为后缀的属性进行比较。
 *
 * CompareValidator 支持不同的比较运算符，
 * 通过 [[operator]] 属性指定。
 *
 * 默认的比较功能是基于字符串值，即待比较的值将以字节的形式进行比较。
 * 当比较数值型时，确认已将 [[$type]] 属性的值设置为 [[TYPE_NUMBER]]
 * 以启用数值比较。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CompareValidator extends Validator
{
    /**
     * 用于指定属性 [[type]] 以字符串型比较的常量。
     * @since 2.0.11
     * @see type
     */
    const TYPE_STRING = 'string';
    /**
     * 用于指定属性 [[type]] 以数值型比较的常量。
     * @since 2.0.11
     * @see type
     */
    const TYPE_NUMBER = 'number';

    /**
     * @var string 待比较的属性值。当它和 [[compareValue]] 都被设置时，后者优先。
     * 如果都没有设置，它会跟以当前属性名后缀 "_repeat" 的属性进行比较。
     * 例如：
     * 如果当前比较的是 'password' 属性，
     * 那么它比较的属性将为 'password_repeat'
     * @see compareValue
     */
    public $compareAttribute;
    /**
     * @var mixed 待比较的常量值。
     * 当这个属性和 [[compareAttribute]] 同时设置时，这个属性优先。
     * @see compareAttribute
     */
    public $compareValue;
    /**
     * @var string 被比较的值类型名称。支持以下类型：
     *
     * - [[TYPE_STRING|string]]: 待比较的值是字符串型。在比较前不会做类型转换。
     * - [[TYPE_NUMBER|number]]: 待比较的值是数值型。字符串值在比较前会被转换为数值。
     */
    public $type = self::TYPE_STRING;
    /**
     * @var string 比较运算符，支持如下运算符：
     *
     * - `==`: 比较两个值是否相等。用于非严格模式比较。
     * - `===`: 比较两个值是否全等。用于严格模式比较。
     * - `!=`: 比较两个值是否不相等。用于非严格模式比较。
     * - `!==`: 比较两个值是否不全等。用于严格模式比较。
     * - `>`: 检测待校验的值是否大于待比较的值。
     * - `>=`: 检测待校验的值是否大于等于待比较的值。
     * - `<`: 检测待校验的值是否小于待比较的值。
     * - `<=`: 检测待校验的值是否小于等于待比较的值。
     *
     * 如果你想比较数值，确保将 [[type]] 属性设置为 `number`。
     */
    public $operator = '==';
    /**
     * @var string 用户定义错误消息。
     * 它可包含如下的占位符，并将被校验器自动的替换：
     *
     * - `{attribute}`: 待校验的属性标签名
     * - `{value}`: 待校验的属性值
     * - `{compareValue}`: 比较的值或者属性标签
     * - `{compareAttribute}`: 比较的属性标签
     * - `{compareValueOrAttribute}`: 比较的值或者属性标签
     */
    public $message;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            switch ($this->operator) {
                case '==':
                    $this->message = Yii::t('yii', '{attribute} must be equal to "{compareValueOrAttribute}".');
                    break;
                case '===':
                    $this->message = Yii::t('yii', '{attribute} must be equal to "{compareValueOrAttribute}".');
                    break;
                case '!=':
                    $this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValueOrAttribute}".');
                    break;
                case '!==':
                    $this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValueOrAttribute}".');
                    break;
                case '>':
                    $this->message = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".');
                    break;
                case '>=':
                    $this->message = Yii::t('yii', '{attribute} must be greater than or equal to "{compareValueOrAttribute}".');
                    break;
                case '<':
                    $this->message = Yii::t('yii', '{attribute} must be less than "{compareValueOrAttribute}".');
                    break;
                case '<=':
                    $this->message = Yii::t('yii', '{attribute} must be less than or equal to "{compareValueOrAttribute}".');
                    break;
                default:
                    throw new InvalidConfigException("Unknown operator: {$this->operator}");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_array($value)) {
            $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));

            return;
        }
        if ($this->compareValue !== null) {
            $compareLabel = $compareValue = $compareValueOrAttribute = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $model->$compareAttribute;
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if (!$this->compareValues($this->operator, $this->type, $value, $compareValue)) {
            $this->addError($model, $attribute, $this->message, [
                'compareAttribute' => $compareLabel,
                'compareValue' => $compareValue,
                'compareValueOrAttribute' => $compareValueOrAttribute,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->compareValue === null) {
            throw new InvalidConfigException('CompareValidator::compareValue must be set.');
        }
        if (!$this->compareValues($this->operator, $this->type, $value, $this->compareValue)) {
            return [$this->message, [
                'compareAttribute' => $this->compareValue,
                'compareValue' => $this->compareValue,
                'compareValueOrAttribute' => $this->compareValue,
            ]];
        }

        return null;
    }

    /**
     * 使用指定的比较运算符比较两个值。
     * @param string $operator 比较运算符
     * @param string $type 待比较值类型
     * @param mixed $value 待比较值
     * @param mixed $compareValue 被比较的值
     * @return bool 使用指定的运算符比较返回值是否为真
     */
    protected function compareValues($operator, $type, $value, $compareValue)
    {
        if ($type === self::TYPE_NUMBER) {
            $value = (float) $value;
            $compareValue = (float) $compareValue;
        } else {
            $value = (string) $value;
            $compareValue = (string) $compareValue;
        }
        switch ($operator) {
            case '==':
                return $value == $compareValue;
            case '===':
                return $value === $compareValue;
            case '!=':
                return $value != $compareValue;
            case '!==':
                return $value !== $compareValue;
            case '>':
                return $value > $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<':
                return $value < $compareValue;
            case '<=':
                return $value <= $compareValue;
            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.compare(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', $form);';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $options = [
            'operator' => $this->operator,
            'type' => $this->type,
        ];

        if ($this->compareValue !== null) {
            $options['compareValue'] = $this->compareValue;
            $compareLabel = $compareValue = $compareValueOrAttribute = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $model->getAttributeLabel($compareAttribute);
            $options['compareAttribute'] = Html::getInputId($model, $compareAttribute);
            $options['compareAttributeName'] = Html::getInputName($model, $compareAttribute);
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        $options['message'] = $this->formatMessage($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
            'compareAttribute' => $compareLabel,
            'compareValue' => $compareValue,
            'compareValueOrAttribute' => $compareValueOrAttribute,
        ]);

        return $options;
    }
}
