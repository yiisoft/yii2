<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Json;

/**
 * RequiredValidator validates that the specified attribute does not have null or empty value.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequiredValidator extends Validator
{
    /**
     * @var bool whether to skip this validator if the value being validated is empty.
     */
    public $skipOnEmpty = false;
    /**
     * @var mixed the desired value that the attribute must have.
     * If this is null, the validator will validate that the specified attribute is not empty.
     * If this is set as a value that is not null, the validator will validate that
     * the attribute has a value that is the same as this property value.
     * Defaults to null.
     * @see strict
     */
    public $requiredValue;
    /**
     * @var bool whether the comparison between the attribute value and [[requiredValue]] is strict.
     * When this is true, both the values and types must match.
     * Defaults to false, meaning only the values need to match.
     *
     * Note that behavior for when [[requiredValue]] is null is the following:
     *
     * - In strict mode, the validator will check if the attribute value is null
     * - In non-strict mode validation will fail
     */
    public $strict = false;
    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * - `{requiredValue}`: the value of [[requiredValue]]
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

        return 'yii.validation.required(value, messages, ' . Json::htmlEncode($options) . ');';
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
