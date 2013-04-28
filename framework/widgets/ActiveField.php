<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends Component
{
	/**
	 * @var string the tag name. Defaults to 'div'.
	 */
	public $tag;
	/**
	 * @var ActiveForm
	 */
	public $form;
	/**
	 * @var \yii\base\Model
	 */
	public $model;
	/**
	 * @var string
	 */
	public $attribute;
	/**
	 * @var array
	 */
	public $options;
	
	public function begin()
	{
		$options = $this->options === null ? $this->form->fieldOptions : $this->options;
		$this->tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag']);
		$class = isset($options['class']) ? array($options['class']) : array();
		if ($this->form->autoFieldCssClass) {
			$class[] = 'field-' . Html::getInputId($this->model, $this->attribute);
		}
		if ($this->model->isAttributeRequired($this->attribute)) {
			$class[] = $this->form->requiredCssClass;
		}
		if ($this->model->hasErrors($this->attribute)) {
			$class[] = $this->form->errorCssClass;
		}
		if ($class !== array()) {
			$options['class'] = implode(' ', $class);
		}
		return Html::beginTag($this->tag, $options);
	}
	
	public function end()
	{
		return Html::endTag($this->tag);
	}

	public function label($options = null)
	{
		if ($options === null) {
			$options = $this->form->labelOptions;
		}
		return Html::activeLabel($this->model, $this->attribute, $options);
	}

	public function error($options = null)
	{
		if ($options === null) {
			$options = $this->form->errorOptions;
		}
		$attribute = Html::getAttributeName($this->attribute);
		$error = $this->model->getFirstError($attribute);
		if ($error === null) {
			$options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
		}
		$tag = isset($options['tag']) ? $options['tag'] : 'span';
		unset($options['tag']);
		return Html::tag($tag, Html::encode($error), $options);
	}

	protected function render($input)
	{
		return $this->begin() . "\n" . strtr($this->form->fieldTemplate, array(
			'{input}' => $input,
			'{label}' => $this->label(),
			'{error}' => $this->error(),
		)) . $this->end();
	}

	/**
	 * Generates an input tag for the given model attribute.
	 * @param string $type the input type (e.g. 'text', 'password')
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public function input($type, $options = array())
	{
		return $this->render(Html::activeInput($type, $this->model, $this->attribute, $options));
	}

	/**
	 * Generates a text input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public function textInput($options = array())
	{
		return $this->render(Html::activeTextInput($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a hidden input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public function hiddenInput($options = array())
	{
		return $this->render(Html::activeHiddenInput($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a password input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public function passwordInput($options = array())
	{
		return $this->render(Html::activePasswordInput($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a file input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public function fileInput($options = array())
	{
		return $this->render(Html::activeFileInput($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a textarea tag for the given model attribute.
	 * The model attribute value will be used as the content in the textarea.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated textarea tag
	 */
	public function textarea($options = array())
	{
		return $this->render(Html::activeTextarea($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a radio button tag for the given model attribute.
	 * This method will generate the "name" tag attribute automatically unless it is explicitly specified in `$options`.
	 * This method will generate the "checked" tag attribute according to the model attribute value.
	 * @param string $value the value tag attribute. If it is null, the value attribute will not be rendered.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated radio button tag
	 */
	public function radio($value = '1', $options = array())
	{
		return $this->render(Html::activeRadio($this->model, $this->attribute, $value, $options));
	}

	public function radioAlt($value = '1', $options = array())
	{
		$label = Html::encode($this->model->getAttributeLabel($this->attribute));
		return $this->begin() . "\n"
			. Html::label(Html::activeRadio($this->model, $this->attribute, $value, $options) . ' ' . $label) . "\n"
			. $this->error() . "\n"
			. $this->end();
	}

	/**
	 * Generates a checkbox tag for the given model attribute.
	 * This method will generate the "name" tag attribute automatically unless it is explicitly specified in `$options`.
	 * This method will generate the "checked" tag attribute according to the model attribute value.
	 * @param string $value the value tag attribute. If it is null, the value attribute will not be rendered.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated checkbox tag
	 */
	public function checkbox($value = '1', $options = array())
	{
		return $this->render(Html::activeCheckbox($this->model, $this->attribute, $value, $options));
	}

	public function checkboxAlt($value = '1', $options = array())
	{
		$label = Html::encode($this->model->getAttributeLabel($this->attribute));
		return $this->begin() . "\n"
			. Html::label(Html::activeCheckbox($this->model, $this->attribute, $value, $options) . ' ' . $label) . "\n"
			. $this->error() . "\n"
			. $this->end();
	}

	/**
	 * Generates a drop-down list for the given model attribute.
	 * The selection of the drop-down list is taken from the value of the model attribute.
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\helpers\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the select option tags. The array keys must be valid option values,
	 *   and the array values are the extra attributes for the corresponding option tags. For example,
	 *
	 * ~~~
	 * array(
	 *     'value1' => array('disabled' => true),
	 *     'value2' => array('label' => 'value 2'),
	 * );
	 * ~~~
	 *
	 * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
	 *   except that the array keys represent the optgroup labels specified in $items.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated drop-down list tag
	 */
	public function dropDownList($items, $options = array())
	{
		return $this->render(Html::activeDropDownList($this->model, $this->attribute, $items, $options));
	}

	/**
	 * Generates a list box.
	 * The selection of the list box is taken from the value of the model attribute.
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\helpers\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the select option tags. The array keys must be valid option values,
	 *   and the array values are the extra attributes for the corresponding option tags. For example,
	 *
	 * ~~~
	 * array(
	 *     'value1' => array('disabled' => true),
	 *     'value2' => array('label' => 'value 2'),
	 * );
	 * ~~~
	 *
	 * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
	 *   except that the array keys represent the optgroup labels specified in $items.
	 * - unselect: string, the value that will be submitted when no option is selected.
	 *   When this attribute is set, a hidden field will be generated so that if no option is selected in multiple
	 *   mode, we can still obtain the posted unselect value.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated list box tag
	 */
	public function listBox($items, $options = array())
	{
		return $this->render(Html::activeListBox($this->model, $this->attribute, $items, $options));
	}

	/**
	 * Generates a list of checkboxes.
	 * A checkbox list allows multiple selection, like [[listBox()]].
	 * As a result, the corresponding submitted value is an array.
	 * The selection of the checkbox list is taken from the value of the model attribute.
	 * @param array $items the data item used to generate the checkboxes.
	 * The array keys are the labels, while the array values are the corresponding checkbox values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param array $options options (name => config) for the checkbox list. The following options are specially handled:
	 *
	 * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - separator: string, the HTML code that separates items.
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $checked, $value)
	 * ~~~
	 *
	 * where $index is the zero-based index of the checkbox in the whole list; $label
	 * is the label for the checkbox; and $name, $value and $checked represent the name,
	 * value and the checked status of the checkbox input.
	 * @return string the generated checkbox list
	 */
	public function checkboxList($items, $options = array())
	{
		return Html::activeCheckboxList($this->model, $this->attribute, $items, $options);
	}

	/**
	 * Generates a list of radio buttons.
	 * A radio button list is like a checkbox list, except that it only allows single selection.
	 * The selection of the radio buttons is taken from the value of the model attribute.
	 * @param array $items the data item used to generate the radio buttons.
	 * The array keys are the labels, while the array values are the corresponding radio button values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param array $options options (name => config) for the radio button list. The following options are specially handled:
	 *
	 * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - separator: string, the HTML code that separates items.
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $checked, $value)
	 * ~~~
	 *
	 * where $index is the zero-based index of the radio button in the whole list; $label
	 * is the label for the radio button; and $name, $value and $checked represent the name,
	 * value and the checked status of the radio button input.
	 * @return string the generated radio button list
	 */
	public function radioList($items, $options = array())
	{
		return Html::activeRadioList($this->model, $this->attribute, $items, $options);
	}
}