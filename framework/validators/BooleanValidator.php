<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;

/**
 * BooleanValidator checks if the attribute value is a boolean value.
 *
 * Possible boolean values can be configured via the [[trueValue]] and [[falseValue]] properties.
 * And the comparison can be either [[strict]] or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BooleanValidator extends Validator
{
    /**
     * @var mixed the value representing true status. Defaults to '1'.
     */
    public $trueValue = '1';
    /**
     * @var mixed the value representing false status. Defaults to '0'.
     */
    public $falseValue = '0';
    /**
     * @var boolean whether the comparison to [[trueValue]] and [[falseValue]] is strict.
     * When this is true, the attribute value and type must both match those of [[trueValue]] or [[falseValue]].
     * Defaults to false, meaning only the value needs to be matched.
     */
    public $strict = false;
    /**
     * @var string the typecast of the attribute value.
     * When this is not null, the attribute value will typecast to boolean or integer:
     * - 'int' means typecast to integer
     * - 'bool' means typecast to boolean
     * @see constants {{TYPECAST_INT}} or {{TYPECAST_BOOL}}.
     */
    public $typecast;

    /** The parameter value that means typecast attribute value to integer. */
    const TYPECAST_INT = 'int';
    /** The parameter value that means typecast attribute value to boolean */
    const TYPECAST_BOOL = 'bool';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be either "{true}" or "{false}".');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        $result = $this->validateValue($model->$attribute);
        if (null !== $result) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
        else {
            if (null !== $this->typecast) {
                $model->$attribute = (bool)$model->$attribute;

                if (static::TYPECAST_INT === $this->typecast) {
                    $model->$attribute = (false === $model->$attribute ? 0 : 1);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $valid = !$this->strict && ($value == $this->trueValue || $value == $this->falseValue)
                 || $this->strict && ($value === $this->trueValue || $value === $this->falseValue);

        if (!$valid) {
            return [$this->message, [
                'true' => $this->trueValue === true ? 'true' : $this->trueValue,
                'false' => $this->falseValue === false ? 'false' : $this->falseValue,
            ]];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $options = [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'true' => $this->trueValue === true ? 'true' : $this->trueValue,
                'false' => $this->falseValue === false ? 'false' : $this->falseValue,
            ], Yii::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->strict) {
            $options['strict'] = 1;
        }

        ValidationAsset::register($view);

        return 'yii.validation.boolean(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}
