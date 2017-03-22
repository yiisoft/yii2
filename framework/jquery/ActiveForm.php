<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\helpers\Json;
use yii\helpers\Url;

/**
 * ActiveForm is an enhanced version of [[\yii\widgets\ActiveForm]], which allows input client and AJAX validation
 * via underlying jQuery component.
 *
 * @see ActiveField
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class ActiveForm extends \yii\widgets\ActiveForm
{
    /**
     * @inheritdoc
     */
    public $fieldClass = ActiveField::class;
    /**
     * @var string the CSS class that is added to a field container when the associated attribute is being validated.
     */
    public $validatingCssClass = 'validating';
    /**
     * @var bool whether to enable client-side data validation.
     * If [[ActiveField::enableClientValidation]] is set, its value will take precedence for that input field.
     */
    public $enableClientValidation = true;
    /**
     * @var bool whether to enable AJAX-based data validation.
     * If [[ActiveField::enableAjaxValidation]] is set, its value will take precedence for that input field.
     */
    public $enableAjaxValidation = false;
    /**
     * @var bool whether to hook up `yii.activeForm` JavaScript plugin.
     * This property must be set `true` if you want to support client validation and/or AJAX validation, or if you
     * want to take advantage of the `yii.activeForm` plugin. When this is `false`, the form will not generate
     * any JavaScript.
     */
    public $enableClientScript = true;
    /**
     * @var array|string the URL for performing AJAX-based validation. This property will be processed by
     * [[Url::to()]]. Please refer to [[Url::to()]] for more details on how to configure this property.
     * If this property is not set, it will take the value of the form's action attribute.
     */
    public $validationUrl;
    /**
     * @var bool whether to perform validation when the form is submitted.
     */
    public $validateOnSubmit = true;
    /**
     * @var bool whether to perform validation when the value of an input field is changed.
     * If [[ActiveField::validateOnChange]] is set, its value will take precedence for that input field.
     */
    public $validateOnChange = true;
    /**
     * @var bool whether to perform validation when an input field loses focus.
     * If [[ActiveField::$validateOnBlur]] is set, its value will take precedence for that input field.
     */
    public $validateOnBlur = true;
    /**
     * @var bool whether to perform validation while the user is typing in an input field.
     * If [[ActiveField::validateOnType]] is set, its value will take precedence for that input field.
     * @see validationDelay
     */
    public $validateOnType = false;
    /**
     * @var int number of milliseconds that the validation should be delayed when the user types in the field
     * and [[validateOnType]] is set `true`.
     * If [[ActiveField::validationDelay]] is set, its value will take precedence for that input field.
     */
    public $validationDelay = 500;
    /**
     * @var string the name of the GET parameter indicating the validation request is an AJAX request.
     */
    public $ajaxParam = 'ajax';
    /**
     * @var string the type of data that you're expecting back from the server.
     */
    public $ajaxDataType = 'json';
    /**
     * @var bool whether to scroll to the first error after validation.
     * @since 2.0.6
     */
    public $scrollToError = true;
    /**
     * @var int offset in pixels that should be added when scrolling to the first error.
     * @since 2.0.11
     */
    public $scrollToErrorOffset = 0;
    /**
     * @var array the client validation options for individual attributes. Each element of the array
     * represents the validation options for a particular attribute.
     * @internal
     */
    public $attributes = [];


    /**
     * Runs the widget.
     * This registers the necessary JavaScript code and renders the form close tag.
     */
    public function run()
    {
        $html = parent::run();

        if ($this->enableClientScript) {
            $id = $this->options['id'];
            $options = Json::htmlEncode($this->getClientOptions());
            $attributes = Json::htmlEncode($this->attributes);
            $view = $this->getView();
            ActiveFormAsset::register($view);
            $view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
        }

        return $html;
    }

    /**
     * Returns the options for the form JS widget.
     * @return array the options.
     */
    protected function getClientOptions()
    {
        $options = [
            'encodeErrorSummary' => $this->encodeErrorSummary,
            'errorSummary' => '.' . implode('.', preg_split('/\s+/', $this->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY)),
            'validateOnSubmit' => $this->validateOnSubmit,
            'errorCssClass' => $this->errorCssClass,
            'successCssClass' => $this->successCssClass,
            'validatingCssClass' => $this->validatingCssClass,
            'ajaxParam' => $this->ajaxParam,
            'ajaxDataType' => $this->ajaxDataType,
            'scrollToError' => $this->scrollToError,
            'scrollToErrorOffset' => $this->scrollToErrorOffset,
        ];
        if ($this->validationUrl !== null) {
            $options['validationUrl'] = Url::to($this->validationUrl);
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc($options, [
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
        ]);
    }
}