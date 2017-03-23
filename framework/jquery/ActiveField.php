<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * ActiveField represents a form input field within an [[ActiveForm]].
 *
 * @see ActiveForm
 *
 * @property ActiveForm $form the form that this field is associated with.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * @var bool whether to enable client-side data validation.
     * If not set, it will take the value of [[ActiveForm::enableClientValidation]].
     */
    public $enableClientValidation;
    /**
     * @var bool whether to enable AJAX-based data validation.
     * If not set, it will take the value of [[ActiveForm::enableAjaxValidation]].
     */
    public $enableAjaxValidation;
    /**
     * @var bool whether to perform validation when the value of the input field is changed.
     * If not set, it will take the value of [[ActiveForm::validateOnChange]].
     */
    public $validateOnChange;
    /**
     * @var bool whether to perform validation when the input field loses focus.
     * If not set, it will take the value of [[ActiveForm::validateOnBlur]].
     */
    public $validateOnBlur;
    /**
     * @var bool whether to perform validation while the user is typing in the input field.
     * If not set, it will take the value of [[ActiveForm::validateOnType]].
     * @see validationDelay
     */
    public $validateOnType;
    /**
     * @var int number of milliseconds that the validation should be delayed when the user types in the field
     * and [[validateOnType]] is set `true`.
     * If not set, it will take the value of [[ActiveForm::validationDelay]].
     */
    public $validationDelay;
    /**
     * @var array the jQuery selectors for selecting the container, input and error tags.
     * The array keys should be `container`, `input`, and/or `error`, and the array values
     * are the corresponding selectors. For example, `['input' => '#my-input']`.
     *
     * The container selector is used under the context of the form, while the input and the error
     * selectors are used under the context of the container.
     *
     * You normally do not need to set this property as the default selectors should work well for most cases.
     */
    public $selectors = [];


    /**
     * @inheritdoc
     */
    public function begin()
    {
        if ($this->form->enableClientScript) {
            $clientOptions = $this->getClientOptions();
            if (!empty($clientOptions)) {
                $this->form->attributes[] = $clientOptions;
            }
        }

        return parent::begin();
    }

    /**
     * Returns the JS options for the field.
     * @return array the JS options.
     */
    protected function getClientOptions()
    {
        $attribute = Html::getAttributeName($this->attribute);
        if (!in_array($attribute, $this->model->activeAttributes(), true)) {
            return [];
        }

        $clientValidation = $this->isClientValidationEnabled();
        $ajaxValidation = $this->isAjaxValidationEnabled();

        if ($clientValidation) {
            $validators = [];
            foreach ($this->model->getActiveValidators($attribute) as $validator) {
                /* @var $validator \yii\validators\Validator */
                if (!$validator->enableClientValidation) {
                    continue;
                }

                $js = $validator->clientValidateAttribute($this->model, $attribute, $this->form->getView());
                if ($js != '') {
                    $js = $this->form->getClientValidatorBuilder()->build($validator, $this->model, $attribute, $this->form->getView());
                }

                if ($js != '') {
                    if ($validator->whenClient !== null) {
                        $js = "if (({$validator->whenClient})(attribute, value)) { $js }";
                    }
                    $validators[] = $js;
                }
            }
        }

        if (!$ajaxValidation && (!$clientValidation || empty($validators))) {
            return [];
        }

        $options = [];

        $inputID = $this->getInputId();
        $options['id'] = Html::getInputId($this->model, $this->attribute);
        $options['name'] = $this->attribute;

        $options['container'] = isset($this->selectors['container']) ? $this->selectors['container'] : ".field-$inputID";
        $options['input'] = isset($this->selectors['input']) ? $this->selectors['input'] : "#$inputID";
        if (isset($this->selectors['error'])) {
            $options['error'] = $this->selectors['error'];
        } elseif (isset($this->errorOptions['class'])) {
            $options['error'] = '.' . implode('.', preg_split('/\s+/', $this->errorOptions['class'], -1, PREG_SPLIT_NO_EMPTY));
        } else {
            $options['error'] = isset($this->errorOptions['tag']) ? $this->errorOptions['tag'] : 'span';
        }

        $options['encodeError'] = !isset($this->errorOptions['encode']) || $this->errorOptions['encode'];
        if ($ajaxValidation) {
            $options['enableAjaxValidation'] = true;
        }
        foreach (['validateOnChange', 'validateOnBlur', 'validateOnType', 'validationDelay'] as $name) {
            $options[$name] = $this->$name === null ? $this->form->$name : $this->$name;
        }

        if (!empty($validators)) {
            $options['validate'] = new JsExpression("function (attribute, value, messages, deferred, \$form) {" . implode('', $validators) . '}');
        }

        if ($this->addAriaAttributes === false) {
            $options['updateAriaInvalid'] = false;
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc($options, [
            'validateOnChange' => true,
            'validateOnBlur' => true,
            'validateOnType' => false,
            'validationDelay' => 500,
            'encodeError' => true,
            'error' => '.help-block',
            'updateAriaInvalid' => true,
        ]);
    }

    /**
     * Checks if client validation enabled for the field
     * @return bool
     * @since 2.0.11
     */
    protected function isClientValidationEnabled()
    {
        return $this->enableClientValidation || $this->enableClientValidation === null && $this->form->enableClientValidation;
    }

    /**
     * Checks if ajax validation enabled for the field
     * @return bool
     * @since 2.0.11
     */
    protected function isAjaxValidationEnabled()
    {
        return $this->enableAjaxValidation || $this->enableAjaxValidation === null && $this->form->enableAjaxValidation;
    }
}