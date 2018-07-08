<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Behavior;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * ActiveFormClientScript is a base behavior for [[ActiveForm]], which allows composition of the client-side form validation.
 * Particular JavaScript-associated library should provide extension of this class, implementing [[registerClientScript()]] method.
 *
 * @property ActiveForm $owner the owner of this behavior.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
abstract class ActiveFormClientScript extends Behavior
{
    /**
     * @var array client validator class map in format: `[server-side-validator-class => client-side-validator]`.
     * Client side validator should be specified as an instance of [[\yii\validators\client\ClientValidator]] or
     * its DI compatible configuration.
     *
     * Class map respects validators inheritance, e.g. if you specify map for `ParentValidator` it will be used for
     * `ChildValidator` in case it extends `ParentValidator`. In case maps for both `ParentValidator` and `ChildValidator`
     * are specified the first value will take precedence.
     *
     * Result of [[defaultClientValidatorMap()]] method will be merged into this field at behavior initialization.
     * In order to disable mapping for pre-defined validator use `false` value.
     *
     * For example:
     *
     * ```php
     * [
     *     \yii\validators\BooleanValidator::class => \yii\jquery\validators\client\BooleanValidator::class,
     *     \yii\validators\ImageValidator::class => \yii\jquery\validators\client\ImageValidator::class,
     *     \yii\validators\FileValidator::class => false, // disable client validation for `FileValidator`
     * ]
     * ```
     *
     * You should use this field only in case particular client script does not provide any default mapping or
     * in case you wish to override this mapping.
     */
    public $clientValidatorMap = [];

    /**
     * @var array the client validation options for individual attributes. Each element of the array
     * represents the validation options for a particular attribute.
     */
    protected $attributes = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->clientValidatorMap = array_merge(
            $this->defaultClientValidatorMap(),
            $this->clientValidatorMap
        );
    }

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Widget::EVENT_AFTER_RUN => 'afterRun',
            ActiveForm::EVENT_BEFORE_FIELD_RENDER => 'beforeFieldRender',
        ];
    }

    /**
     * Handles [[Widget::EVENT_AFTER_RUN]] event, registering related client script.
     * @param \yii\base\Event $event event instance.
     */
    public function afterRun($event)
    {
        $this->registerClientScript();
    }

    /**
     * Handles [[ActiveForm::EVENT_BEFORE_FIELD_RENDER]] event.
     * @param ActiveFieldEvent $event event instance.
     */
    public function beforeFieldRender($event)
    {
        $clientOptions = $this->getFieldClientOptions($event->field);
        if (!empty($clientOptions)) {
            $this->attributes[] = $clientOptions;
        }
    }

    /**
     * Registers underlying client script including JavaScript code, which supports form validation.
     */
    abstract protected function registerClientScript();

    /**
     * Returns the JS options for the field.
     * @param ActiveField $field active field instance.
     * @return array the JS options.
     */
    protected function getFieldClientOptions($field)
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
                /* @var $validator \yii\validators\Validator */
                if (!$validator->enableClientValidation) {
                    continue;
                }

                $js = $validator->clientValidateAttribute($field->model, $attribute, $field->form->getView());
                if ($js == '') {
                    $js = $this->buildClientValidator($validator, $field->model, $attribute, $field->form->getView());
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

        $inputID = $field->getInputId();
        $options['id'] = Html::getInputId($field->model, $field->attribute);
        $options['name'] = $field->attribute;

        $options['container'] = isset($field->selectors['container']) ? $field->selectors['container'] : ".field-$inputID";
        $options['input'] = isset($field->selectors['input']) ? $field->selectors['input'] : "#$inputID";
        if (isset($field->selectors['error'])) {
            $options['error'] = $field->selectors['error'];
        } elseif (isset($field->errorOptions['class'])) {
            $options['error'] = '.' . implode('.', preg_split('/\s+/', $field->errorOptions['class'], -1, PREG_SPLIT_NO_EMPTY));
        } else {
            $options['error'] = isset($field->errorOptions['tag']) ? $field->errorOptions['tag'] : 'span';
        }

        $options['encodeError'] = !isset($field->errorOptions['encode']) || $field->errorOptions['encode'];
        if ($ajaxValidation) {
            $options['enableAjaxValidation'] = true;
        }
        foreach (['validateOnChange', 'validateOnBlur', 'validateOnType', 'validationDelay'] as $name) {
            $options[$name] = $field->$name === null ? $field->form->$name : $field->$name;
        }

        if (!empty($validators)) {
            $options['validate'] = new JsExpression("function (attribute, value, messages, deferred, \$form) {" . implode('', $validators) . '}');
        }

        if ($field->addAriaAttributes === false) {
            $options['updateAriaInvalid'] = false;
        }

        return $options;
    }

    /**
     * Returns the options for the form JS widget.
     * @return array the options.
     */
    protected function getClientOptions()
    {
        $options = [
            'encodeErrorSummary' => $this->owner->encodeErrorSummary,
            'errorSummary' => '.' . implode('.', preg_split('/\s+/', $this->owner->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY)),
            'validateOnSubmit' => $this->owner->validateOnSubmit,
            'errorCssClass' => $this->owner->errorCssClass,
            'successCssClass' => $this->owner->successCssClass,
            'validatingCssClass' => $this->owner->validatingCssClass,
            'ajaxParam' => $this->owner->ajaxParam,
            'ajaxDataType' => $this->owner->ajaxDataType,
            'scrollToError' => $this->owner->scrollToError,
            'scrollToErrorOffset' => $this->owner->scrollToErrorOffset,
        ];
        if ($this->owner->validationUrl !== null) {
            $options['validationUrl'] = Url::to($this->owner->validationUrl);
        }

        return $options;
    }

    /**
     * Builds the JavaScript needed for performing client-side validation for given validator.
     * @param \yii\validators\Validator $validator validator to be built.
     * @param \yii\base\Model $model the data model being validated.
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view object that is going to be used to render views or view files
     * containing a model form with validator applied.
     * @return string|null client-side validation JavaScript code, `null` - if given validator is not supported.
     */
    protected function buildClientValidator($validator, $model, $attribute, $view)
    {
        foreach ($this->clientValidatorMap as $serverSideValidatorClass => $clientSideValidator) {
            if ($clientSideValidator !== false && $validator instanceof $serverSideValidatorClass) {
                /* @var $clientValidator \yii\validators\client\ClientValidator */
                $clientValidator = Yii::createObject($clientSideValidator);
                return $clientValidator->build($validator, $model, $attribute, $view);
            }
        }
        return null;
    }

    /**
     * Returns default client validator map, which will be merged with [[clientValidatorMap]] at [[init()]].
     * Child class may override this method providing validator map specific for particular client script.
     * @return array client validator class map in format: `[server-side-validator-class => client-side-validator]`.
     */
    protected function defaultClientValidatorMap()
    {
        return [];
    }
}