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
use yii\util\Html;
use yii\util\ArrayHelper;

/**
 * ActiveForm ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveForm extends Widget
{
	/**
	 * @param array|string $action the form action URL. This parameter will be processed by [[\yii\util\Html::url()]].
	 */
	public $action = '';
	/**
	 * @var string the form submission method. This should be either 'post' or 'get'.
	 * Defaults to 'post'.
	 */
	public $method = 'post';
	/**
	 * @var string the default CSS class for the error summary container.
	 * @see errorSummary()
	 */
	public $errorSummaryClass = 'yii-error-summary';
	/**
	 * @var string the default CSS class that indicates an input has error.
	 * This is
	 */
	public $errorClass = 'yii-error';
	public $successClass = 'yii-success';
	public $validatingClass = 'yii-validating';
	/**
	 * @var boolean whether to enable client-side data validation. Defaults to false.
	 * When this property is set true, client-side validation will be performed by validators
	 * that support it (see {@link CValidator::enableClientValidation} and {@link CValidator::clientValidateAttribute}).
	 */
	public $enableClientValidation = false;

	public $options = array();


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

		$showAll = isset($options['showAll']) && $options['showAll'];
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
		$container = isset($options['container']) ? $options['container'] : 'div';
		unset($options['showAll'], $options['header'], $options['footer'], $options['container']);

		if (!isset($options['class'])) {
			$options['class'] = $this->errorSummaryClass;
		}

		if ($lines !== array()) {
			$content = "<ul><li>" . implode("</li>\n<li>", ArrayHelper::htmlEncode($lines)) . "</li><ul>";
			return Html::tag($container, $header . $content . $footer, $options);
		} else {
			$content = "<ul></ul>";
			$options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
			return Html::tag($container, $header . $content . $footer, $options);
		}
	}

	/**
	 * @param Model $model
	 * @param string $attribute
	 * @param array $options
	 * @return string
	 */
	public function error($model, $attribute, $options = array())
	{
		self::resolveName($model, $attribute); // turn [a][b]attr into attr
		$container = isset($options['container']) ? $options['container'] : 'div';
		unset($options['container']);
		$error = $model->getFirstError($attribute);
		return Html::tag($container, Html::encode($error), $options);
	}

	public function resolveAttributeName($name)
	{

	}

	public function label($model, $attribute, $options = array())
	{
	}

	public function input($type, $model, $attribute, $options = array())
	{
		return '';
	}

	public function textInput($model, $attribute, $options = array())
	{
		return $this->input('text', $model, $attribute, $options);
	}

	public function hiddenInput($model, $attribute, $options = array())
	{
		return $this->input('hidden', $model, $attribute, $options);
	}

	public function passwordInput($model, $attribute, $options = array())
	{
		return $this->input('password', $model, $attribute, $options);
	}

	public function fileInput($model, $attribute, $options = array())
	{
		return $this->input('file', $model, $attribute, $options);
	}

	public function textarea($model, $attribute, $options = array())
	{
	}

	public function radio($model, $attribute, $value = '1', $options = array())
	{
	}

	public function checkbox($model, $attribute, $value = '1', $options = array())
	{
	}

	public function dropDownList($model, $attribute, $items, $options = array())
	{
	}

	public function listBox($model, $attribute, $items, $options = array())
	{
	}

	public function checkboxList($model, $attribute, $items, $options = array())
	{
	}

	public function radioList($model, $attribute, $items, $options = array())
	{
	}
}
