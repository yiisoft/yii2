<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidParamException;
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
	 * @var string the default CSS class for the error summary container.
	 * @see errorSummary()
	 */
	public $errorSummaryClass = 'yii-error-summary';
	public $errorMessageClass = 'yii-error-message';
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
	 * @var array model-class mapped to name prefix
	 */
	public $modelMap;

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
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['showAll'], $options['header'], $options['footer'], $options['container']);

		if (!isset($options['class'])) {
			$options['class'] = $this->errorSummaryClass;
		} else {
			$options['class'] .= ' ' . $this->errorSummaryClass;
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

	/**
	 * @param Model $model
	 * @param string $attribute
	 * @param array $options
	 * @return string
	 */
	public function error($model, $attribute, $options = array())
	{
		$attribute = $this->getAttributeName($attribute);
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag']);
		$error = $model->getFirstError($attribute);
		return Html::tag($tag, Html::encode($error), $options);
	}

	/**
	 * @param Model $model
	 * @param string $attribute
	 * @param array $options
	 * @return string
	 */
	public function label($model, $attribute, $options = array())
	{
		$attribute = $this->getAttributeName($attribute);
		$label = isset($options['label']) ? $options['label'] : Html::encode($model->getAttributeLabel($attribute));
		$for = array_key_exists('for', $options) ? $options['for'] : $this->getInputId($model, $attribute);
		return Html::label($label, $for, $options);
	}

	public function input($type, $model, $attribute, $options = array())
	{
		$value = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::input($type, $name, $value, $options);
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
		$value = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::textarea($name, $value, $options);
	}

	public function radio($model, $attribute, $value = '1', $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('uncheck', $options)) {
			$options['unchecked'] = '0';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::radio($name, $checked, $value, $options);
	}

	public function checkbox($model, $attribute, $value = '1', $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('uncheck', $options)) {
			$options['unchecked'] = '0';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::checkbox($name, $checked, $value, $options);
	}

	public function dropDownList($model, $attribute, $items, $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::dropDownList($name, $checked, $items, $options);
	}

	public function listBox($model, $attribute, $items, $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '0';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = $this->getInputId($model, $attribute);
		}
		return Html::listBox($name, $checked, $items, $options);
	}

	public function checkboxList($model, $attribute, $items, $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '0';
		}
		return Html::checkboxList($name, $checked, $items, $options);
	}

	public function radioList($model, $attribute, $items, $options = array())
	{
		$checked = $this->getAttributeValue($model, $attribute);
		$name = $this->getInputName($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '0';
		}
		return Html::radioList($name, $checked, $items, $options);
	}

	public function getInputName($model, $attribute)
	{
		$class = get_class($model);
		if (isset($this->modelMap[$class])) {
			$class = $this->modelMap[$class];
		} elseif (($pos = strrpos($class, '\\')) !== false) {
			$class = substr($class, $pos + 1);
		}
		if (!preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
		$prefix = $matches[1];
		$attribute = $matches[2];
		$suffix = $matches[3];
		if ($class === '' && $prefix === '') {
			return $attribute . $suffix;
		} elseif ($class !== '') {
			return $class . $prefix . "[$attribute]" . $suffix;
		} else {
			throw new InvalidParamException('Model name cannot be mapped to empty for tabular inputs.');
		}
	}

	public function getInputId($model, $attribute)
	{
		$name = $this->getInputName($model, $attribute);
		return str_replace(array('[]', '][', '[', ']', ' '), array('', '-', '-', '', '-'), $name);
	}

	public function getAttributeValue($model, $attribute)
	{
		if (!preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
		$attribute = $matches[2];
		$index = $matches[3];
		if ($index === '') {
			return $model->$attribute;
		} else {
			$value = $model->$attribute;
			foreach (explode('][', trim($index, '[]')) as $id) {
				if ((is_array($value) || $value instanceof \ArrayAccess) && isset($value[$id])) {
					$value = $value[$id];
				} else {
					return null;
				}
			}
			return $value;
		}
	}

	public function getAttributeName($attribute)
	{
		if (preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			return $matches[2];
		} else {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
	}
}
