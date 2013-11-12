<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * ActiveForm ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveForm extends Widget
{
	/**
	 * @param array|string $action the form action URL. This parameter will be processed by [[\yii\helpers\Html::url()]].
	 */
	public $action = '';
	/**
	 * @var string the form submission method. This should be either 'post' or 'get'.
	 * Defaults to 'post'.
	 */
	public $method = 'post';
	/**
	 * @var array the HTML attributes (name-value pairs) for the form tag.
	 * The values will be HTML-encoded using [[Html::encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 */
	public $options = [];
	/**
	 * @var array the default configuration used by [[field()]] when creating a new field object.
	 */
	public $fieldConfig;
	/**
	 * @var string the default CSS class for the error summary container.
	 * @see errorSummary()
	 */
	public $errorSummaryCssClass = 'error-summary';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is required.
	 */
	public $requiredCssClass = 'required';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute has validation error.
	 */
	public $errorCssClass = 'has-error';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is successfully validated.
	 */
	public $successCssClass = 'has-success';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is being validated.
	 */
	public $validatingCssClass = 'validating';
	/**
	 * @var boolean whether to enable client-side data validation.
	 * If [[ActiveField::enableClientValidation]] is set, its value will take precedence for that input field.
	 */
	public $enableClientValidation = true;
	/**
	 * @var boolean whether to enable AJAX-based data validation.
	 * If [[ActiveField::enableAjaxValidation]] is set, its value will take precedence for that input field.
	 */
	public $enableAjaxValidation = false;
	/**
	 * @var array|string the URL for performing AJAX-based validation. This property will be processed by
	 * [[Html::url()]]. Please refer to [[Html::url()]] for more details on how to configure this property.
	 * If this property is not set, it will take the value of the form's action attribute.
	 */
	public $validationUrl;
	/**
	 * @var boolean whether to perform validation when the form is submitted.
	 */
	public $validateOnSubmit = true;
	/**
	 * @var boolean whether to perform validation when an input field loses focus and its value is found changed.
	 * If [[ActiveField::validateOnChange]] is set, its value will take precedence for that input field.
	 */
	public $validateOnChange = true;
	/**
	 * @var boolean whether to perform validation while the user is typing in an input field.
	 * If [[ActiveField::validateOnType]] is set, its value will take precedence for that input field.
	 * @see validationDelay
	 */
	public $validateOnType = false;
	/**
	 * @var integer number of milliseconds that the validation should be delayed when an input field
	 * is changed or the user types in the field.
	 * If [[ActiveField::validationDelay]] is set, its value will take precedence for that input field.
	 */
	public $validationDelay = 200;
	/**
	 * @var string the name of the GET parameter indicating the validation request is an AJAX request.
	 */
	public $ajaxVar = 'ajax';
	/**
	 * @var string|JsExpression a JS callback that will be called when the form is being submitted.
	 * The signature of the callback should be:
	 *
	 * ~~~
	 * function ($form) {
	 *     ...return false to cancel submission...
	 * }
	 * ~~~
	 */
	public $beforeSubmit;
	/**
	 * @var string|JsExpression a JS callback that is called before validating an attribute.
	 * The signature of the callback should be:
	 *
	 * ~~~
	 * function ($form, attribute, messages) {
	 *     ...return false to cancel the validation...
	 * }
	 * ~~~
	 */
	public $beforeValidate;
	/**
	 * @var string|JsExpression a JS callback that is called after validating an attribute.
	 * The signature of the callback should be:
	 *
	 * ~~~
	 * function ($form, attribute, messages) {
	 * }
	 * ~~~
	 */
	public $afterValidate;
	/**
	 * @var array the client validation options for individual attributes. Each element of the array
	 * represents the validation options for a particular attribute.
	 * @internal
	 */
	public $attributes = [];

	/**
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		if (!isset($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}
		if (!isset($this->fieldConfig['class'])) {
			$this->fieldConfig['class'] = ActiveField::className();
		}
		echo Html::beginForm($this->action, $this->method, $this->options);
	}

	/**
	 * Runs the widget.
	 * This registers the necessary javascript code and renders the form close tag.
	 */
	public function run()
	{
		if (!empty($this->attributes)) {
			$id = $this->options['id'];
			$options = Json::encode($this->getClientOptions());
			$attributes = Json::encode($this->attributes);
			$view = $this->getView();
			ActiveFormAsset::register($view);
			$view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
		}
		echo Html::endForm();
	}

	/**
	 * Returns the options for the form JS widget.
	 * @return array the options
	 */
	protected function getClientOptions()
	{
		$options = [
			'errorSummary' => '.' . $this->errorSummaryCssClass,
			'validateOnSubmit' => $this->validateOnSubmit,
			'errorCssClass' => $this->errorCssClass,
			'successCssClass' => $this->successCssClass,
			'validatingCssClass' => $this->validatingCssClass,
			'ajaxVar' => $this->ajaxVar,
		];
		if ($this->validationUrl !== null) {
			$options['validationUrl'] = Html::url($this->validationUrl);
		}
		foreach (['beforeSubmit', 'beforeValidate', 'afterValidate'] as $name) {
			if (($value = $this->$name) !== null) {
				$options[$name] = $value instanceof JsExpression ? $value : new JsExpression($value);
			}
		}
		return $options;
	}

	/**
	 * Generates a summary of the validation errors.
	 * If there is no validation error, an empty error summary markup will still be generated, but it will be hidden.
	 * @param Model|Model[] $models the model(s) associated with this form
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - header: string, the header HTML for the error summary. If not set, a default prompt string will be used.
	 * - footer: string, the footer HTML for the error summary.
	 *
	 * The rest of the options will be rendered as the attributes of the container tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated error summary
	 */
	public function errorSummary($models, $options = [])
	{
		if (!is_array($models)) {
			$models = [$models];
		}

		$lines = [];
		foreach ($models as $model) {
			/** @var Model $model */
			foreach ($model->getFirstErrors() as $error) {
				$lines[] = Html::encode($error);
			}
		}

		$header = isset($options['header']) ? $options['header'] : '<p>' . Yii::t('yii', 'Please fix the following errors:') . '</p>';
		$footer = isset($options['footer']) ? $options['footer'] : '';
		unset($options['header'], $options['footer']);

		if (!isset($options['class'])) {
			$options['class'] = $this->errorSummaryCssClass;
		} else {
			$options['class'] .= ' ' . $this->errorSummaryCssClass;
		}

		if (!empty($lines)) {
			$content = "<ul><li>" . implode("</li>\n<li>", $lines) . "</li><ul>";
			return Html::tag('div', $header . $content . $footer, $options);
		} else {
			$content = "<ul></ul>";
			$options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
			return Html::tag('div', $header . $content . $footer, $options);
		}
	}

	/**
	 * Generates a form field.
	 * A form field is associated with a model and an attribute. It contains a label, an input and an error message
	 * and use them to interact with end users to collect their inputs for the attribute.
	 * @param Model $model the data model
	 * @param string $attribute the attribute name or expression. See [[Html::getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the additional configurations for the field object
	 * @return ActiveField the created ActiveField object
	 * @see fieldConfig
	 */
	public function field($model, $attribute, $options = [])
	{
		return Yii::createObject(array_merge($this->fieldConfig, $options, [
			'model' => $model,
			'attribute' => $attribute,
			'form' => $this,
		]));
	}

	/**
	 * Validates one or several models and returns an error message array indexed by the attribute IDs.
	 * This is a helper method that simplifies the way of writing AJAX validation code.
	 *
	 * For example, you may use the following code in a controller action to respond
	 * to an AJAX validation request:
	 *
	 * ~~~
	 * $model = new Post;
	 * $model->load($_POST);
	 * if (Yii::$app->request->isAjax) {
	 *     Yii::$app->response->format = Response::FORMAT_JSON;
	 *     return ActiveForm::validate($model);
	 * }
	 * // ... respond to non-AJAX request ...
	 * ~~~
	 *
	 * To validate multiple models, simply pass each model as a parameter to this method, like
	 * the following:
	 *
	 * ~~~
	 * ActiveForm::validate($model1, $model2, ...);
	 * ~~~
	 *
	 * @param Model $model the model to be validated
	 * @param mixed $attributes list of attributes that should be validated.
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 *
	 * When this method is used to validate multiple models, this parameter will be interpreted
	 * as a model.
	 *
	 * @return array the error message array indexed by the attribute IDs.
	 */
	public static function validate($model, $attributes = null)
	{
		$result = [];
		if ($attributes instanceof Model) {
			// validating multiple models
			$models = func_get_args();
			$attributes = null;
		} else {
			$models = [$model];
		}
		/** @var Model $model */
		foreach ($models as $model) {
			$model->validate($attributes);
			foreach ($model->getErrors() as $attribute => $errors) {
				$result[Html::getInputId($model, $attribute)] = $errors;
			}
		}
		return $result;
	}

	/**
	 * Validates an array of model instances and returns an error message array indexed by the attribute IDs.
	 * This is a helper method that simplifies the way of writing AJAX validation code for tabular input.
	 *
	 * For example, you may use the following code in a controller action to respond
	 * to an AJAX validation request:
	 *
	 * ~~~
	 * // ... load $models ...
	 * if (Yii::$app->request->isAjax) {
	 *     Yii::$app->response->format = Response::FORMAT_JSON;
	 *     return ActiveForm::validateMultiple($models);
	 * }
	 * // ... respond to non-AJAX request ...
	 * ~~~
	 *
	 * @param array $models an array of models to be validated.
	 * @param mixed $attributes list of attributes that should be validated.
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 * @return array the error message array indexed by the attribute IDs.
	 */
	public static function validateMultiple($models, $attributes = null)
	{
		$result = [];
		/** @var Model $model */
		foreach ($models as $i => $model) {
			$model->validate($attributes);
			foreach ($model->getErrors() as $attribute => $errors) {
				$result[Html::getInputId($model, "[$i]" . $attribute)] = $errors;
			}
		}
		return $result;
	}
}
