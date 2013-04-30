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
	public $options = array();
	/**
	 * @var string the default CSS class for the error summary container.
	 * @see errorSummary()
	 */
	public $errorSummaryCssClass = 'yii-error-summary';
	/**
	 * @var boolean whether to enable client-side data validation.
	 * Client-side validation will be performed by validators that support it
	 * (see [[\yii\validators\Validator::enableClientValidation]] and [[\yii\validators\Validator::clientValidateAttribute()]]).
	 */
	public $enableClientValidation = true;
	/**
	 * @var array the default configuration used by [[field()]] when creating a new field object.
	 */
	public $fieldConfig = array(
		'class' => 'yii\widgets\ActiveField',
	);
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is required.
	 */
	public $requiredCssClass = 'required';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute has validation error.
	 */
	public $errorCssClass = 'error';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is successfully validated.
	 */
	public $successCssClass = 'success';
	/**
	 * @var string the CSS class that is added to a field container when the associated attribute is being validated.
	 */
	public $validatingCssClass = 'validating';

	/**
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		$this->options['id'] = $this->getId();
		echo Html::beginForm($this->action, $this->method, $this->options);
	}

	/**
	 * Runs the widget.
	 * This registers the necessary javascript code and renders the form close tag.
	 */
	public function run()
	{
		$id = $this->getId();
		$options = array();
		$options = json_encode($options);
		$this->view->registerAssetBundle('yii/form');
		$this->view->registerJs("jQuery('#$id').yii.form($options);");
		echo Html::endForm();
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
	public function errorSummary($models, $options = array())
	{
		if (!is_array($models)) {
			$models = array($models);
		}

		$lines = array();
		foreach ($models as $model) {
			/** @var $model Model */
			foreach ($model->getFirstErrors() as $error) {
				$lines[] = Html::encode($error);
			}
		}

		$header = isset($options['header']) ? $options['header'] : '<p>' . Yii::t('yii|Please fix the following errors:') . '</p>';
		$footer = isset($options['footer']) ? $options['footer'] : '';
		unset($options['header'], $options['footer']);

		if (!isset($options['class'])) {
			$options['class'] = $this->errorSummaryCssClass;
		} else {
			$options['class'] .= ' ' . $this->errorSummaryCssClass;
		}

		if ($lines !== array()) {
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
	 * @return ActiveField the created ActiveField object
	 */
	public function field($model, $attribute, $options = array())
	{
		return Yii::createObject(array_merge($this->fieldConfig, $options, array(
			'model' => $model,
			'attribute' => $attribute,
			'form' => $this,
		)));
	}
}
