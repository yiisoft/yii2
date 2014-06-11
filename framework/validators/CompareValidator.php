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
 * CompareValidator compares the specified attribute value with another value.
 *
 * The value being compared with can be another attribute value
 * (specified via [[compareAttribute]]) or a constant (specified via
 * [[compareValue]]. When both are specified, the latter takes
 * precedence. If neither is specified, the attribute will be compared
 * with another attribute whose name is by appending "_repeat" to the source
 * attribute name.
 *
 * CompareValidator supports different comparison operators, specified
 * via the [[operator]] property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CompareValidator extends Validator
{
    /**
     * @var string the name of the attribute to be compared with. When both this property
     * and [[compareValue]] are set, the latter takes precedence. If neither is set,
     * it assumes the comparison is against another attribute whose name is formed by
     * appending '_repeat' to the attribute being validated. For example, if 'password' is
     * being validated, then the attribute to be compared would be 'password_repeat'.
     * @see compareValue
     */
    public $compareAttribute;
    /**
     * @var mixed the constant value to be compared with. When both this property
     * and [[compareAttribute]] are set, this property takes precedence.
     * @see compareAttribute
     */
    public $compareValue;
    /**
     * @var string the operator for comparison. The following operators are supported:
     *
     * - `==`: check if two values are equal. The comparison is done is non-strict mode.
     * - `===`: check if two values are equal. The comparison is done is strict mode.
     * - `!=`: check if two values are NOT equal. The comparison is done is non-strict mode.
     * - `!==`: check if two values are NOT equal. The comparison is done is strict mode.
     * - `>`: check if value being validated is greater than the value being compared with.
     * - `>=`: check if value being validated is greater than or equal to the value being compared with.
     * - `<`: check if value being validated is less than the value being compared with.
     * - `<=`: check if value being validated is less than or equal to the value being compared with.
     */
    public $operator = '==';
    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * - `{compareValue}`: the value or the attribute label to be compared with
     * - `{compareAttribute}`: the label of the attribute to be compared with
     */
    public $message;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            switch ($this->operator) {
                case '==':
                    $this->message = Yii::t('yii', '{attribute} must be repeated exactly.');
                    break;
                case '===':
                    $this->message = Yii::t('yii', '{attribute} must be repeated exactly.');
                    break;
                case '!=':
                    $this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValue}".');
                    break;
                case '!==':
                    $this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValue}".');
                    break;
                case '>':
                    $this->message = Yii::t('yii', '{attribute} must be greater than "{compareValue}".');
                    break;
                case '>=':
                    $this->message = Yii::t('yii', '{attribute} must be greater than or equal to "{compareValue}".');
                    break;
                case '<':
                    $this->message = Yii::t('yii', '{attribute} must be less than "{compareValue}".');
                    break;
                case '<=':
                    $this->message = Yii::t('yii', '{attribute} must be less than or equal to "{compareValue}".');
                    break;
                default:
                    throw new InvalidConfigException("Unknown operator: {$this->operator}");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if (is_array($value)) {
            $this->addError($object, $attribute, Yii::t('yii', '{attribute} is invalid.'));

            return;
        }
        if ($this->compareValue !== null) {
            $compareLabel = $compareValue = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $object->$compareAttribute;
            $compareLabel = $object->getAttributeLabel($compareAttribute);
        }

        if (!$this->compareValues($this->operator, $value, $compareValue)) {
            $this->addError($object, $attribute, $this->message, [
                'compareAttribute' => $compareLabel,
                'compareValue' => $compareValue,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if ($this->compareValue === null) {
            throw new InvalidConfigException('CompareValidator::compareValue must be set.');
        }
        if (!$this->compareValues($this->operator, $value, $this->compareValue)) {
            return [$this->message, [
                'compareAttribute' => $this->compareValue,
                'compareValue' => $this->compareValue,
            ]];
        } else {
            return null;
        }
    }

    /**
     * Compares two values with the specified operator.
     * @param string $operator the comparison operator
     * @param mixed $value the value being compared
     * @param mixed $compareValue another value being compared
     * @return boolean whether the comparison using the specified operator is true.
     */
    protected function compareValues($operator, $value, $compareValue)
    {
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
     * @inheritdoc
     */
    public function clientValidateAttribute($object, $attribute, $view)
    {
        $options = ['operator' => $this->operator];

        if ($this->compareValue !== null) {
            $options['compareValue'] = $this->compareValue;
            $compareValue = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $object->getAttributeLabel($compareAttribute);
            $options['compareAttribute'] = Html::getInputId($object, $compareAttribute);
        }

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        $options['message'] = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $object->getAttributeLabel($attribute),
            'compareAttribute' => $compareValue,
            'compareValue' => $compareValue,
        ], Yii::$app->language);

        ValidationAsset::register($view);

        return 'yii.validation.compare(value, messages, ' . json_encode($options) . ');';
    }
}
