<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\widgets;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\client\ClientScriptInterface;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use yii\widgets\ActiveFormAsset;

use function array_diff_assoc;
use function implode;
use function in_array;
use function preg_split;

/**
 * ActiveFormJqueryClientScript provides jQuery-based client script integration for ActiveForm and ActiveField widgets.
 *
 * This class is responsible for generating and registering client-side validation options and scripts
 * for Yii2 ActiveForm and ActiveField widgets, enabling AJAX and client validation via the `yii.activeForm` jQuery
 * plugin.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class ActiveFormJqueryClientScript implements ClientScriptInterface
{
    public function getClientOptions(BaseObject $object): array
    {
        if (!($object instanceof ActiveForm) && !($object instanceof ActiveField)) {
            throw new InvalidArgumentException(
                'Object must be an instance of ' . ActiveForm::class . ' or ' . ActiveField::class . '.',
            );
        }

        if ($object instanceof ActiveForm) {
            return $this->getClientOptionsInternal($object);
        }

        return $this->getClientOptionsForFieldInternal($object);
    }

    public function register(BaseObject $object, View $view): void
    {
        $id = $object->options['id'];

        $options = Json::htmlEncode($this->getClientOptions($object));
        $attributes = Json::htmlEncode($object->attributes);
        $view = $object->getView();

        ActiveFormAsset::register($view);

        $view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
    }

    private function getClientOptionsInternal(ActiveForm $form): array
    {
        $options = [
            'encodeErrorSummary' => $form->encodeErrorSummary,
            'errorSummary' => '.' . implode(
                '.',
                preg_split('/\s+/', $form->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY),
            ),
            'validateOnSubmit' => $form->validateOnSubmit,
            'errorCssClass' => $form->errorCssClass,
            'successCssClass' => $form->successCssClass,
            'validatingCssClass' => $form->validatingCssClass,
            'ajaxParam' => $form->ajaxParam,
            'ajaxDataType' => $form->ajaxDataType,
            'scrollToError' => $form->scrollToError,
            'scrollToErrorOffset' => $form->scrollToErrorOffset,
            'validationStateOn' => $form->validationStateOn,
        ];

        if ($form->validationUrl !== null) {
            $options['validationUrl'] = Url::to($form->validationUrl);
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc(
            $options,
            [
                'encodeErrorSummary' => true,
                'errorSummary' => '.error-summary',
                'validateOnSubmit' => true,
                'errorCssClass' => 'has-error',
                'successCssClass' => 'has-success',
                'validatingCssClass' => 'validating',
                'ajaxParam' => 'ajax',
                'ajaxDataType' => 'json',
                'scrollToError' => true,
                'scrollToErrorOffset' => 0,
                'validationStateOn' => $form::VALIDATION_STATE_ON_CONTAINER,
            ],
        );
    }

    public function getClientOptionsForFieldInternal(ActiveField $field): array
    {
        $attribute = Html::getAttributeName($field->attribute);

        if (!in_array($attribute, $field->model->activeAttributes(), true)) {
            return [];
        }

        $clientValidation = $field->isClientValidationEnabled();
        $ajaxValidation = $field->isAjaxValidationEnabled();

        if ($clientValidation) {
            $validators = [];
            foreach ($field->model->getActiveValidators($attribute) as $validator) {
                /** @var \yii\validators\Validator $validator */
                $js = $validator->clientValidateAttribute($field->model, $attribute, $field->form->getView());

                if ($validator->enableClientValidation && $js != '') {
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
        $inputID = $field->getInputId();
        $options['id'] = $inputID ?: Html::getInputId($field->model, $field->attribute);
        $options['name'] = $field->attribute;
        $options['container'] = $field->selectors['container'] ?? ".field-$inputID";
        $options['input'] = $field->selectors['input'] ?? "#$inputID";

        if (isset($field->selectors['error'])) {
            $options['error'] = $field->selectors['error'];
        } elseif (isset($field->errorOptions['class'])) {
            $options['error'] = '.' . implode(
                '.',
                preg_split('/\s+/', $field->errorOptions['class'], -1, PREG_SPLIT_NO_EMPTY),
            );
        } else {
            $options['error'] = $field->errorOptions['tag'] ?? 'span';
        }

        $options['encodeError'] = !isset($field->errorOptions['encode']) || $field->errorOptions['encode'];

        if ($ajaxValidation) {
            $options['enableAjaxValidation'] = true;
        }

        foreach (['validateOnChange', 'validateOnBlur', 'validateOnType', 'validationDelay'] as $name) {
            $options[$name] = $field->$name ?? $field->form->$name;
        }

        if (!empty($validators)) {
            $options['validate'] = new JsExpression(
                'function (attribute, value, messages, deferred, $form) {' . implode('', $validators) . '}',
            );
        }

        if ($field->addAriaAttributes === false) {
            $options['updateAriaInvalid'] = false;
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc(
            $options,
            [
                'validateOnChange' => true,
                'validateOnBlur' => true,
                'validateOnType' => false,
                'validationDelay' => 500,
                'encodeError' => true,
                'error' => '.help-block',
                'updateAriaInvalid' => true,
            ],
        );
    }
}
