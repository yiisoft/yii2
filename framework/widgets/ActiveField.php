<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Component;
use yii\helpers\Html;
use yii\base\Model;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends Component
{
	/**
	 * @var ActiveForm the form that this field is associated with.
	 */
	public $form;
	/**
	 * @var Model the data model that this field is associated with
	 */
	public $model;
	/**
	 * @var string the model attribute that this field is associated with
	 */
	public $attribute;
	/**
	 * @var string the tag name for the field container.
	 */
	public $tag = 'div';
	/**
	 * @var array the HTML attributes (name-value pairs) for the field container tag.
	 * The values will be HTML-encoded using [[Html::encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 */
	public $options = array(
		'class' => 'control-group',
	);
	/**
	 * @var string the template that is used to arrange the label, the input and the error message.
	 * The following tokens will be replaced when [[render()]] is called: `{label}`, `{input}` and `{error}`.
	 */
	public $template = "{label}\n<div class=\"controls\">\n{input}\n{error}\n</div>";
	/**
	 * @var array the default options for the error message. This property is used when calling [[error()]]
	 * without the `$options` parameter.
	 */
	public $errorOptions = array('tag' => 'span', 'class' => 'help-inline');
	/**
	 * @var array the default options for the label. This property is used when calling [[label()]]
	 * without the `$options` parameter.
	 */
	public $labelOptions = array('class' => 'control-label');


	public function begin()
	{
		$options = $this->options;
		$class = isset($options['class']) ? array($options['class']) : array();
		$class[] = 'field-' . Html::getInputId($this->model, $this->attribute);
		if ($this->model->isAttributeRequired($this->attribute)) {
			$class[] = $this->form->requiredCssClass;
		}
		if ($this->model->hasErrors($this->attribute)) {
			$class[] = $this->form->errorCssClass;
		}
		$options['class'] = implode(' ', $class);
		return Html::beginTag($this->tag, $options);
	}
	
	public function end()
	{
		return Html::endTag($this->tag);
	}

	/**
	 * Generates a label tag for [[attribute]].
	 * The label text is the label associated with the attribute, obtained via [[Model::getAttributeLabel()]].
	 * @param array $options the tag options in terms of name-value pairs. If this is null, [[labelOptions]] will be used.
	 * The options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
	 * using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * The following options are specially handled:
	 *
	 * - label: this specifies the label to be displayed. Note that this will NOT be [[encoded()]].
	 *   If this is not set, [[Model::getAttributeLabel()]] will be called to get the label for display
	 *   (after encoding).
	 *
	 * @return string the generated label tag
	 */
	public function label($options = null)
	{
		if ($options === null) {
			$options = $this->labelOptions;
		}
		return Html::activeLabel($this->model, $this->attribute, $options);
	}

	/**
	 * Generates a tag that contains the first validation error of [[attribute]].
	 * If there is no validation, the tag will be returned and styled as hidden.
	 * @param array $options the tag options in terms of name-value pairs. If this is null, [[errorOptions]] will be used.
	 * The options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
	 * using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * The following options are specially handled:
	 *
	 * - tag: this specifies the tag name. If not set, "span" will be used.
	 *
	 * @return string the generated label tag
	 */
	public function error($options = null)
	{
		if ($options === null) {
			$options = $this->errorOptions;
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

	/**
	 * Renders the field with the given input HTML.
	 * This method will generate the label and error tags, and return them together with the given
	 * input HTML according to [[template]].
	 * @param string $input the input HTML
	 * @return string the rendering result
	 */
	public function render($input)
	{
		return $this->begin() . "\n" . strtr($this->template, array(
			'{input}' => $input,
			'{label}' => $this->label(),
			'{error}' => $this->error(),
		)) . "\n" . $this->end();
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
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated radio button tag
	 */
	public function radio($options = array())
	{
		return $this->render(Html::activeRadio($this->model, $this->attribute, $options));
	}

	/**
	 * Generates a checkbox tag for the given model attribute.
	 * This method will generate the "name" tag attribute automatically unless it is explicitly specified in `$options`.
	 * This method will generate the "checked" tag attribute according to the model attribute value.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated checkbox tag
	 */
	public function checkbox($options = array())
	{
		return $this->render(Html::activeCheckbox($this->model, $this->attribute, $options));
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
		return $this->render(
			'<div id="' . Html::getInputId($this->model, $this->attribute) . '">'
			. Html::activeCheckboxList($this->model, $this->attribute, $items, $options)
			. '</div>'
		);
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
		return $this->render(
			'<div id="' . Html::getInputId($this->model, $this->attribute) . '">'
			. Html::activeRadioList($this->model, $this->attribute, $items, $options)
			. '</div>'
		);
	}
}