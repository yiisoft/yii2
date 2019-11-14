<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * EachValidator 用于通过指定的校验规则来校验一个数组的每一个元素。
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
 * > 注意：这个校验器在模型外使用时，不支持行内校验规则。
 *   例如：通过 [[validate()]] 方法进行校验。
 *
 * > 注意：EachValidator 通常只在一些基本场景下使用，
 *   在更复杂的表格输入场景下，你需要考虑使用多个模型。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.4
 */
class EachValidator extends Validator
{
    /**
     * @var array|Validator 作用于数组值的校验规则定义。
     * 它的格式应该类似于 [[\yii\base\Model::rules()]] ，
     * 只是它的第一个元素不包含属性列表。
     * 例如：
     *
     * ```php
     * ['integer']
     * ['match', 'pattern' => '/[a-z]/is']
     * ```
     *
     * 更多详情，参阅 [[\yii\base\Model::rules()]]。
     */
    public $rule;
    /**
     * @var bool 是否应该使用通过 [[rule]] 定义的校验器中组装的错误消息。
     * 如果启用，这个校验器指定的错误消息只有在属性值不是一个数组时候才出现。
     * 如果禁用，将使用校验器自带的错误消息。
     */
    public $allowMessageFromRule = true;
    /**
     * @var bool 是否在检测到第一个错误时就停止。
     * 当启用时，校验器最多只会产生一个关于属性的校验错误消息，
     * 如果禁用，将会产生多个错误消息：每个异常值一个。
     * 注意，这个开关只会影响 [[validateAttribute()]] 的值，
     * 而 [[validateValue()]] 将不会受影响。
     * @since 2.0.11
     */
    public $stopOnFirstError = true;

    /**
     * @var Validator validator instance.
     */
    private $_validator;


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
     * 返回 [[rule]] 中定义的校验器。
     * @param Model|null $model 校验器创建的model上下文。
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
     * 基于 [[rule]] 中指定的校验规则创建校验器对象。
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
        }

        throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!is_array($value) && !$value instanceof \ArrayAccess) {
            $this->addError($model, $attribute, $this->message, []);
            return;
        }

        $validator = $this->getValidator($model); // ensure model context while validator creation

        $detectedErrors = $model->getErrors($attribute);
        $filteredValue = $model->$attribute;
        foreach ($value as $k => $v) {
            $model->clearErrors($attribute);
            $model->$attribute = $v;
            if (!$validator->skipOnEmpty || !$validator->isEmpty($v)) {
                $validator->validateAttribute($model, $attribute);
            }
            $filteredValue[$k] = $model->$attribute;
            if ($model->hasErrors($attribute)) {
                if ($this->allowMessageFromRule) {
                    $validationErrors = $model->getErrors($attribute);
                    $detectedErrors = array_merge($detectedErrors, $validationErrors);
                } else {
                    $model->clearErrors($attribute);
                    $this->addError($model, $attribute, $this->message, ['value' => $v]);
                    $detectedErrors[] = $model->getFirstError($attribute);
                }
                $model->$attribute = $value;

                if ($this->stopOnFirstError) {
                    break;
                }
            }
        }

        $model->$attribute = $filteredValue;
        $model->clearErrors($attribute);
        $model->addErrors([$attribute => $detectedErrors]);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!is_array($value) && !$value instanceof \ArrayAccess) {
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
                }

                return [$this->message, ['value' => $v]];
            }
        }

        return null;
    }
}
