<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use yii\base\InvalidConfigException;
use Yii;
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
     * @var Validator validator instance.
     */
    private $_validator;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * Returns the validator declared in [[rule]].
     * @param Model|null $model model in which context validator should be created.
     * @return Validator the declared validator.
     */
    private function getValidator($model = null)
    {
        if ($this->_validator === null) {
            $this->_validator = $this->createEmbeddedValidator($model);
        }
        return $this->_validator;
    }

    /**
     * Creates validator object based on the validation rule specified in [[rule]].
     * @param Model|null $model model in which context validator should be created.
     * @throws \yii\base\InvalidConfigException
     * @return Validator validator instance
     */
    private function createEmbeddedValidator($model)
    {
        $rule = $this->rule;
        if ($rule instanceof Validator) {
            return $rule;
        } elseif (is_array($rule) && isset($rule[0])) { // validator type
            if (!is_object($model)) {
                $model = new Model(); // mock up context model
            }
            return Validator::createValidator($rule[0], $model, $this->attributes, array_slice($rule, 1));
        } else {
            throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!is_array($value)) {
            $this->addError($model, $attribute, $this->message, []);
            return;
        }

        $validator = $this->getValidator($model); // ensure model context while validator creation

        $originalErrors = $model->getErrors($attribute);
        $filteredValue = [];
        foreach ($value as $k => $v) {
            $model->$attribute = $v;
            if (!$validator->skipOnEmpty || !$validator->isEmpty($v)) {
                $validator->validateAttribute($model, $attribute);
            }
            $filteredValue[$k] = $model->$attribute;
            if ($model->hasErrors($attribute)) {
                $validationErrors = $model->getErrors($attribute);
                $model->clearErrors($attribute);
                if (!empty($originalErrors)) {
                    $model->addErrors([$attribute => $originalErrors]);
                }
                if ($this->allowMessageFromRule) {
                    $model->addErrors([$attribute => $validationErrors]);
                } else {
                    $this->addError($model, $attribute, $this->message, ['value' => $v]);
                }
                $model->$attribute = $value;
                return;
            }
        }
        $model->$attribute = $filteredValue;
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_array($value)) {
            return [$this->message, []];
        }

        $validator = $this->getValidator();
        foreach ($value as $v) {
            if ($validator->skipOnEmpty && $validator->isEmpty($v)) {
                continue;
            }
            $result = $validator->validateValue($v);
            if ($result !== null) {
                if ($this->allowMessageFromRule) {
                    $result[1]['value'] = $v;
                    return $result;
                } else {
                    return [$this->message, ['value' => $v]];
                }
            }
        }

        return null;
    }
}
