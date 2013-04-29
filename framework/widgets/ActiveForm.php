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
use yii\helpers\ArrayHelper;

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
	 * @param array $options the attributes (name-value pairs) for the form tag.
	 * The values will be HTML-encoded using [[encode()]].
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
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		echo Html::beginForm($this->action, $this->method, $this->options);
	}

	/**
	 * Runs the widget.
	 * This registers the necessary javascript code and renders the form close tag.
	 */
	public function run()
	{
		echo Html::endForm();
	}

	/**
	 * @param Model|Model[] $models
	 * @param array $options
	 * @return string
	 */
	public function errorSummary($models, $options = array())
	{
		if (!is_array($models)) {
			$models = array($models);
		}

		$showAll = !empty($options['showAll']);
		$lines = array();
		/** @var $model Model */
		foreach ($models as $model) {
			if ($showAll) {
				foreach ($model->getErrors() as $errors) {
					$lines = array_merge($lines, $errors);
				}
			} else {
				$lines = array_merge($lines, $model->getFirstErrors());
			}
		}

		$header = isset($options['header']) ? $options['header'] : '<p>' . Yii::t('yii|Please fix the following errors:') . '</p>';
		$footer = isset($options['footer']) ? $options['footer'] : '';
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['showAll'], $options['header'], $options['footer'], $options['container']);

		if (!isset($options['class'])) {
			$options['class'] = $this->errorSummaryCssClass;
		} else {
			$options['class'] .= ' ' . $this->errorSummaryCssClass;
		}

		if ($lines !== array()) {
			$content = "<ul><li>" . implode("</li>\n<li>", ArrayHelper::htmlEncode($lines)) . "</li><ul>";
			return Html::tag($tag, $header . $content . $footer, $options);
		} else {
			$content = "<ul></ul>";
			$options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
			return Html::tag($tag, $header . $content . $footer, $options);
		}
	}

	public function field($model, $attribute, $options = null)
	{
		return Yii::createObject(array_merge($this->fieldConfig, array(
			'model' => $model,
			'attribute' => $attribute,
			'form' => $this,
			'options' => $options,
		)));
	}
}
