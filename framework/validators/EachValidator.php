<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * EachValidator validates an array by checking each of its elements against an embedded validation rule.
 *
 * ```php
 * class MyModel extends Model
 * {
 *     public $categoryIDs = [];
 *
 *     public function rules()
 *     {
 *         return [
 *             // checks if every category ID is an integer
 *             ['categoryIDs', 'each', 'rule' => ['integer']],
 *         ]
 *     }
 * }
 * ```
 *
 * > Note: This validator will not work with inline validation rules in case of usage outside the model scope,
 *   e.g. via [[validate()]] method.
 *
 * > Note: EachValidator is meant to be used only in basic cases, you should consider usage of tabular input,
 *   using several models for the more complex case.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.4
 */
class EachValidator extends Validator
{
    /**
     * @var array|Validator definition of the validation rule, which should be used on array values.
     * It should be specified in the same format as at [[\yii\base\Model::rules()]], except it should not
     * contain attribute list as the first element.
     * For example:
     *
     * ```php
     * ['integer']
     * ['match', 'pattern' => '/[a-z]/is']
     * ```
     *
     * Please refer to [[\yii\base\Model::rules()]] for more details.
     */
    public $rule;
    /**
     * @var bool whether to use error message composed by validator declared via [[rule]] if its validation fails.
     * If enabled, error message specified for this validator itself will appear only if attribute value is not an array.
     * If disabled, own error message value will be used always.
     */
    public $allowMessageFromRule = true;
    /**
     * @var bool whether to stop validation once first error among attribute value elements is detected.
     * When enabled validation will produce single error message on attribute, when disabled - multiple
     * error messages mya appear: one per each invalid value.
     * Note that this option will affect only [[validateAttribute()]] value, while [[validateValue()]] will
     * not be affected.
     * @since 2.0.11
     */
    public $stopOnFirstError = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * Creates validator object based on the validation rule specified in [[rule]].
     * @param Model|null $model model in which context validator should be created.
     * @param mixed|null $current value being currently validated.
     * @throws \yii\base\InvalidConfigException
     * @return Validator validator instance
     */
    private function createEmbeddedValidator($model = null, $current = null)
    {
        $rule = $this->rule;
        if ($rule instanceof Validator) {
            return $rule;
        }

        if (is_array($rule) && isset($rule[0])) { // validator type
            if (!is_object($model)) {
                $model = new Model(); // mock up context model
            }

            $params = array_slice($rule, 1);
            $params['current'] = $current;
            return Validator::createValidator($rule[0], $model, $this->attributes, $params);
        }

        throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $arrayOfValues = $model->$attribute;
        if (!is_array($arrayOfValues) && !$arrayOfValues instanceof \ArrayAccess) {
            $this->addError($model, $attribute, $this->message, []);
            return;
        }

        foreach ($arrayOfValues as $k => $v) {
            $dynamicModel = new DynamicModel($model->getAttributes());
            $dynamicModel->setAttributeLabels($model->attributeLabels());
            $dynamicModel->addRule($attribute, $this->createEmbeddedValidator($model, $v));
            $dynamicModel->defineAttribute($attribute, $v);
            $dynamicModel->validate();

            $arrayOfValues[$k] = $dynamicModel->$attribute; // filtered values like 'trim'

            if (!$dynamicModel->hasErrors($attribute)) {
                continue;
            }

            if ($this->allowMessageFromRule) {
                $validationErrors = $dynamicModel->getErrors($attribute);
                $model->addErrors([$attribute => $validationErrors]);
            } else {
                $this->addError($model, $attribute, $this->message, ['value' => $v]);
            }

            if ($this->stopOnFirstError) {
                break;
            }
        }

        $model->$attribute = $arrayOfValues;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!is_array($value) && !$value instanceof \ArrayAccess) {
            return [$this->message, []];
        }

        $validator = $this->createEmbeddedValidator();
        foreach ($value as $v) {
            if ($validator->skipOnEmpty && $validator->isEmpty($v)) {
                continue;
            }
            $result = $validator->validateValue($v);
            if ($result !== null) {
                if ($this->allowMessageFromRule) {
                    $result[1]['value'] = $v;
                    return $result;
                }

                return [$this->message, ['value' => $v]];
            }
        }

        return null;
    }
}
