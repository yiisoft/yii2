<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use Yii;
use yii\base\InvalidParamException;

/**
 * Html provides a set of static methods for generating commonly used HTML tags.
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Html
{
	/**
	 * @var boolean whether to close void (empty) elements. Defaults to true.
	 * @see voidElements
	 */
	public static $closeVoidElements = true;
	/**
	 * @var array list of void elements (element name => 1)
	 * @see closeVoidElements
	 * @see http://www.w3.org/TR/html-markup/syntax.html#void-element
	 */
	public static $voidElements = array(
		'area' => 1,
		'base' => 1,
		'br' => 1,
		'col' => 1,
		'command' => 1,
		'embed' => 1,
		'hr' => 1,
		'img' => 1,
		'input' => 1,
		'keygen' => 1,
		'link' => 1,
		'meta' => 1,
		'param' => 1,
		'source' => 1,
		'track' => 1,
		'wbr' => 1,
	);
	/**
	 * @var boolean whether to show the values of boolean attributes in element tags.
	 * If false, only the attribute names will be generated.
	 * @see booleanAttributes
	 */
	public static $showBooleanAttributeValues = true;
	/**
	 * @var array list of boolean attributes. The presence of a boolean attribute on
	 * an element represents the true value, and the absence of the attribute represents the false value.
	 * @see showBooleanAttributeValues
	 * @see http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes
	 */
	public static $booleanAttributes = array(
		'async' => 1,
		'autofocus' => 1,
		'autoplay' => 1,
		'checked' => 1,
		'controls' => 1,
		'declare' => 1,
		'default' => 1,
		'defer' => 1,
		'disabled' => 1,
		'formnovalidate' => 1,
		'hidden' => 1,
		'ismap' => 1,
		'loop' => 1,
		'multiple' => 1,
		'muted' => 1,
		'nohref' => 1,
		'noresize' => 1,
		'novalidate' => 1,
		'open' => 1,
		'readonly' => 1,
		'required' => 1,
		'reversed' => 1,
		'scoped' => 1,
		'seamless' => 1,
		'selected' => 1,
		'typemustmatch' => 1,
	);
	/**
	 * @var array the preferred order of attributes in a tag. This mainly affects the order of the attributes
	 * that are rendered by [[renderAttributes()]].
	 */
	public static $attributeOrder = array(
		'type',
		'id',
		'class',
		'name',
		'value',

		'href',
		'src',
		'action',
		'method',

		'selected',
		'checked',
		'readonly',
		'disabled',

		'size',
		'maxlength',
		'width',
		'height',
		'rows',
		'cols',

		'alt',
		'title',
		'rel',
		'media',
	);


	/**
	 * Encodes special characters into HTML entities.
	 * The [[yii\base\Application::charset|application charset]] will be used for encoding.
	 * @param string $content the content to be encoded
	 * @return string the encoded content
	 * @see decode
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	public static function encode($content)
	{
		return htmlspecialchars($content, ENT_QUOTES, Yii::$app->charset);
	}

	/**
	 * Decodes special HTML entities back to the corresponding characters.
	 * This is the opposite of [[encode()]].
	 * @param string $content the content to be decoded
	 * @return string the decoded content
	 * @see encode
	 * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
	 */
	public static function decode($content)
	{
		return htmlspecialchars_decode($content, ENT_QUOTES);
	}

	/**
	 * Generates a complete HTML tag.
	 * @param string $name the tag name
	 * @param string $content the content to be enclosed between the start and end tags. It will not be HTML-encoded.
	 * If this is coming from end users, you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $attributes the element attributes. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated HTML tag
	 * @see beginTag
	 * @see endTag
	 */
	public static function tag($name, $content = '', $attributes = array())
	{
		$html = '<' . $name . static::renderAttributes($attributes);
		if (isset(static::$voidElements[strtolower($name)])) {
			return $html . (static::$closeVoidElements ? ' />' : '>');
		} else {
			return $html . ">$content</$name>";
		}
	}

	/**
	 * Generates a start tag.
	 * @param string $name the tag name
	 * @param array $attributes the element attributes. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated start tag
	 * @see endTag
	 * @see tag
	 */
	public static function beginTag($name, $attributes = array())
	{
		return '<' . $name . static::renderAttributes($attributes) . '>';
	}

	/**
	 * Generates an end tag.
	 * @param string $name the tag name
	 * @return string the generated end tag
	 * @see beginTag
	 * @see tag
	 */
	public static function endTag($name)
	{
		return "</$name>";
	}

	/**
	 * Encloses the given content within a CDATA tag.
	 * @param string $content the content to be enclosed within the CDATA tag
	 * @return string the CDATA tag with the enclosed content.
	 */
	public static function cdata($content)
	{
		return '<![CDATA[' . $content . ']]>';
	}

	/**
	 * Generates a style tag.
	 * @param string $content the style content
	 * @param array $attributes the attributes of the style tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * If the attributes does not contain "type", a default one with value "text/css" will be used.
	 * @return string the generated style tag
	 */
	public static function style($content, $attributes = array())
	{
		if (!isset($attributes['type'])) {
			$attributes['type'] = 'text/css';
		}
		return static::tag('style', "/*<![CDATA[*/\n{$content}\n/*]]>*/", $attributes);
	}

	/**
	 * Generates a script tag.
	 * @param string $content the script content
	 * @param array $attributes the attributes of the script tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * If the attributes does not contain "type", a default one with value "text/javascript" will be used.
	 * @return string the generated script tag
	 */
	public static function script($content, $attributes = array())
	{
		if (!isset($attributes['type'])) {
			$attributes['type'] = 'text/javascript';
		}
		return static::tag('script', "/*<![CDATA[*/\n{$content}\n/*]]>*/", $attributes);
	}

	/**
	 * Generates a link tag that refers to an external CSS file.
	 * @param array|string $url the URL of the external CSS file. This parameter will be processed by [[url()]].
	 * @param array $attributes the attributes of the link tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated link tag
	 * @see url
	 */
	public static function cssFile($url, $attributes = array())
	{
		$attributes['rel'] = 'stylesheet';
		$attributes['type'] = 'text/css';
		$attributes['href'] = static::url($url);
		return static::tag('link', '', $attributes);
	}

	/**
	 * Generates a script tag that refers to an external JavaScript file.
	 * @param string $url the URL of the external JavaScript file. This parameter will be processed by [[url()]].
	 * @param array $attributes the attributes of the script tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated script tag
	 * @see url
	 */
	public static function jsFile($url, $attributes = array())
	{
		$attributes['type'] = 'text/javascript';
		$attributes['src'] = static::url($url);
		return static::tag('script', '', $attributes);
	}

	/**
	 * Generates a form start tag.
	 * @param array|string $action the form action URL. This parameter will be processed by [[url()]].
	 * @param string $method form method, either "post" or "get" (case-insensitive)
	 * @param array $attributes the attributes of the form tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated form start tag.
	 * @see endForm
	 */
	public static function beginForm($action = '', $method = 'post', $attributes = array())
	{
		$action = static::url($action);

		// query parameters in the action are ignored for GET method
		// we use hidden fields to add them back
		$hiddens = array();
		if (!strcasecmp($method, 'get') && ($pos = strpos($action, '?')) !== false) {
			foreach (explode('&', substr($action, $pos + 1)) as $pair) {
				if (($pos1 = strpos($pair, '=')) !== false) {
					$hiddens[] = static::hiddenInput(urldecode(substr($pair, 0, $pos1)), urldecode(substr($pair, $pos1 + 1)));
				} else {
					$hiddens[] = static::hiddenInput(urldecode($pair), '');
				}
			}
			$action = substr($action, 0, $pos);
		}

		$attributes['action'] = $action;
		$attributes['method'] = $method;
		$form = static::beginTag('form', $attributes);
		if ($hiddens !== array()) {
			$form .= "\n" . implode("\n", $hiddens);
		}

		return $form;
	}

	/**
	 * Generates a form end tag.
	 * @return string the generated tag
	 * @see beginForm
	 */
	public static function endForm()
	{
		return '</form>';
	}

	/**
	 * Generates a hyperlink tag.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param array|string|null $url the URL for the hyperlink tag. This parameter will be processed by [[url()]]
	 * and will be used for the "href" attribute of the tag. If this parameter is null, the "href" attribute
	 * will not be generated.
	 * @param array $attributes the attributes of the hyperlink tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated hyperlink
	 * @see url
	 */
	public static function a($text, $url = null, $attributes = array())
	{
		if ($url !== null) {
			$attributes['href'] = static::url($url);
		}
		return static::tag('a', $text, $attributes);
	}

	/**
	 * Generates a mailto hyperlink.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param string $email email address. If this is null, the first parameter (link body) will be treated
	 * as the email address and used.
	 * @param array $attributes the attributes of the hyperlink tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated mailto link
	 */
	public static function mailto($text, $email = null, $attributes = array())
	{
		return static::a($text, 'mailto:' . ($email === null ? $text : $email), $attributes);
	}

	/**
	 * Generates an image tag.
	 * @param string $src the image URL. This parameter will be processed by [[url()]].
	 * @param array $attributes the attributes of the image tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated image tag
	 */
	public static function img($src, $attributes = array())
	{
		$attributes['src'] = static::url($src);
		if (!isset($attributes['alt'])) {
			$attributes['alt'] = '';
		}
		return static::tag('img', null, $attributes);
	}

	/**
	 * Generates a label tag.
	 * @param string $content label text. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param string $for the ID of the HTML element that this label is associated with.
	 * If this is null, the "for" attribute will not be generated.
	 * @param array $attributes the attributes of the label tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated label tag
	 */
	public static function label($content, $for = null, $attributes = array())
	{
		$attributes['for'] = $for;
		return static::tag('label', $content, $attributes);
	}

	/**
	 * Generates a button tag.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * If the attributes does not contain "type", a default one with value "button" will be used.
	 * @return string the generated button tag
	 */
	public static function button($name = null, $value = null, $content = 'Button', $attributes = array())
	{
		$attributes['name'] = $name;
		$attributes['value'] = $value;
		if (!isset($attributes['type'])) {
			$attributes['type'] = 'button';
		}
		return static::tag('button', $content, $attributes);
	}

	/**
	 * Generates a submit button tag.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated submit button tag
	 */
	public static function submitButton($name = null, $value = null, $content = 'Submit', $attributes = array())
	{
		$attributes['type'] = 'submit';
		return static::button($name, $value, $content, $attributes);
	}

	/**
	 * Generates a reset button tag.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated reset button tag
	 */
	public static function resetButton($name = null, $value = null, $content = 'Reset', $attributes = array())
	{
		$attributes['type'] = 'reset';
		return static::button($name, $value, $content, $attributes);
	}

	/**
	 * Generates an input type of the given type.
	 * @param string $type the type attribute.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated input tag
	 */
	public static function input($type, $name = null, $value = null, $attributes = array())
	{
		$attributes['type'] = $type;
		$attributes['name'] = $name;
		$attributes['value'] = $value;
		return static::tag('input', null, $attributes);
	}

	/**
	 * Generates an input button.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function buttonInput($name, $value = 'Button', $attributes = array())
	{
		return static::input('button', $name, $value, $attributes);
	}

	/**
	 * Generates a submit input button.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function submitInput($name = null, $value = 'Submit', $attributes = array())
	{
		return static::input('submit', $name, $value, $attributes);
	}

	/**
	 * Generates a reset input button.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function resetInput($name = null, $value = 'Reset', $attributes = array())
	{
		return static::input('reset', $name, $value, $attributes);
	}

	/**
	 * Generates a text input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function textInput($name, $value = null, $attributes = array())
	{
		return static::input('text', $name, $value, $attributes);
	}

	/**
	 * Generates a hidden input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function hiddenInput($name, $value = null, $attributes = array())
	{
		return static::input('hidden', $name, $value, $attributes);
	}

	/**
	 * Generates a password input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function passwordInput($name, $value = null, $attributes = array())
	{
		return static::input('password', $name, $value, $attributes);
	}

	/**
	 * Generates a file input field.
	 * To use a file input field, you should set the enclosing form's "enctype" attribute to
	 * be "multipart/form-data". After the form is submitted, the uploaded file information
	 * can be obtained via $_FILES[$name] (see PHP documentation).
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function fileInput($name, $value = null, $attributes = array())
	{
		return static::input('file', $name, $value, $attributes);
	}

	/**
	 * Generates a text area input.
	 * @param string $name the input name
	 * @param string $value the input value. Note that it will be encoded using [[encode()]].
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated text area tag
	 */
	public static function textarea($name, $value = '', $attributes = array())
	{
		$attributes['name'] = $name;
		return static::tag('textarea', static::encode($value), $attributes);
	}

	/**
	 * Generates a radio button input.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param boolean $checked whether the radio button should be checked.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned. The following attribute
	 * will be specially handled and not put in the resulting tag:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. When this attribute
	 *   is present, a hidden input will be generated so that if the radio button is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 *
	 * @return string the generated radio button tag
	 */
	public static function radio($name, $value = '1', $checked = false, $attributes = array())
	{
		$attributes['checked'] = $checked;
		if (isset($attributes['uncheck'])) {
			// add a hidden field so that if the radio button is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $attributes['uncheck']);
			unset($attributes['uncheck']);
		} else {
			$hidden = '';
		}
		return $hidden . static::input('radio', $name, $value, $attributes);
	}

	/**
	 * Generates a checkbox input.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param boolean $checked whether the checkbox should be checked.
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned. The following attribute
	 * will be specially handled and not put in the resulting tag:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute
	 *   is present, a hidden input will be generated so that if the checkbox is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 *
	 * @return string the generated checkbox tag
	 */
	public static function checkbox($name, $value = '1', $checked = false, $attributes = array())
	{
		$attributes['checked'] = $checked;
		if (isset($attributes['uncheck'])) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $attributes['uncheck']);
			unset($attributes['uncheck']);
		} else {
			$hidden = '';
		}
		return $hidden . static::input('checkbox', $name, $value, $attributes);
	}

	/**
	 * Generates a drop-down list.
	 * @param string $name the input name
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\util\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param string $selection the selected value
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned. The following attribute
	 * will be specially handled and not put in the resulting tag:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the option tags. The array keys must be valid option values,
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
	 * @return string the generated drop-down list tag
	 */
	public static function dropDownList($name, $items, $selection = null, $attributes = array())
	{
		$attributes['name'] = $name;
		$options = static::renderOptions($items, $selection, $attributes);
		return static::tag('select', "\n" . $options . "\n", $attributes);
	}

	/**
	 * Generates a list box.
	 * @param string $name the input name
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\util\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param string|array $selection the selected value(s)
	 * @param array $attributes the attributes of the input tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned. The following attribute
	 * will be specially handled and not put in the resulting tag:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option;
	 * - options: array, the attributes for the option tags. The array keys must be valid option values,
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
	 * @return string the generated list box tag
	 */
	public static function listBox($name, $items, $selection = null, $attributes = array())
	{
		if (!isset($attributes['size'])) {
			$attributes['size'] = 4;
		}
		if (isset($attributes['multiple']) && $attributes['multiple'] && substr($name, -2) !== '[]') {
			$name .= '[]';
		}
		$attributes['name'] = $name;
		if (isset($attributes['unselect'])) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			if (substr($name, -2) === '[]') {
				$name = substr($name, 0, -2);
			}
			$hidden = static::hiddenInput($name, $attributes['unselect']);
			unset($attributes['unselect']);
		} else {
			$hidden = '';
		}
		$options = static::renderOptions($items, $selection, $attributes);
		return $hidden . static::tag('select', "\n" . $options . "\n", $attributes);
	}

	/**
	 * Generates a list of checkboxes.
	 * A checkbox list allows multiple selection, like [[listBox()]].
	 * As a result, the corresponding submitted value is an array.
	 * @param string $name the name attribute of each checkbox.
	 * @param array $items the data item used to generate the checkboxes.
	 * The array keys are the labels, while the array values are the corresponding checkbox values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param string|array $selection the selected value(s).
	 * @param array $options options (name => config) for the checkbox list. The following options are supported:
	 *
	 * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - separator: string, the HTML code that separates items.
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $value, $checked)
	 * ~~~
	 *
	 * where $index is the zero-based index of the checkbox in the whole list; $label
	 * is the label for the checkbox; and $name, $value and $checked represent the name,
	 * value and the checked status of the checkbox input.
	 * @return string the generated checkbox list
	 */
	public static function checkboxList($name, $items, $selection = null, $options = array())
	{
		if (substr($name, -2) !== '[]') {
			$name .= '[]';
		}

		$formatter = isset($options['item']) ? $options['item'] : null;
		$lines = array();
		$index = 0;
		foreach ($items as $value => $label) {
			$checked = $selection !== null &&
				(!is_array($selection) && !strcmp($value, $selection)
				|| is_array($selection) && in_array($value, $selection));
			if ($formatter !== null) {
				$lines[] = call_user_func($formatter, $index, $label, $name, $value, $checked);
			} else {
				$lines[] = static::label(static::checkbox($name, $value, $checked) . ' ' . $label);
			}
			$index++;
		}

		if (isset($options['unselect'])) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$name2 = substr($name, -2) === '[]' ? substr($name, 0, -2) : $name;
			$hidden = static::hiddenInput($name2, $options['unselect']);
		} else {
			$hidden = '';
		}
		$separator = isset($options['separator']) ? $options['separator'] : "\n";

		return $hidden . implode($separator, $lines);
	}

	/**
	 * Generates a list of radio buttons.
	 * A radio button list is like a checkbox list, except that it only allows single selection.
	 * @param string $name the name attribute of each radio button.
	 * @param array $items the data item used to generate the radio buttons.
	 * The array keys are the labels, while the array values are the corresponding radio button values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param string|array $selection the selected value(s).
	 * @param array $options options (name => config) for the radio button list. The following options are supported:
	 *
	 * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - separator: string, the HTML code that separates items.
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $value, $checked)
	 * ~~~
	 *
	 * where $index is the zero-based index of the radio button in the whole list; $label
	 * is the label for the radio button; and $name, $value and $checked represent the name,
	 * value and the checked status of the radio button input.
	 * @return string the generated radio button list
	 */
	public static function radioList($name, $items, $selection = null, $options = array())
	{
		$formatter = isset($options['item']) ? $options['item'] : null;
		$lines = array();
		$index = 0;
		foreach ($items as $value => $label) {
			$checked = $selection !== null &&
				(!is_array($selection) && !strcmp($value, $selection)
				|| is_array($selection) && in_array($value, $selection));
			if ($formatter !== null) {
				$lines[] = call_user_func($formatter, $index, $label, $name, $value, $checked);
			} else {
				$lines[] = static::label(static::radio($name, $value, $checked) . ' ' . $label);
			}
			$index++;
		}

		$separator = isset($options['separator']) ? $options['separator'] : "\n";
		if (isset($options['unselect'])) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			$hidden = static::hiddenInput($name, $options['unselect']);
		} else {
			$hidden = '';
		}

		return $hidden . implode($separator, $lines);
	}

	/**
	 * Renders the option tags that can be used by [[dropDownList()]] and [[listBox()]].
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\util\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param string|array $selection the selected value(s). This can be either a string for single selection
	 * or an array for multiple selections.
	 * @param array $attributes the attributes parameter that is passed to the [[dropDownList()]] or [[listBox()]] call.
	 * This method will take out these elements, if any: "prompt", "options" and "groups". See more details
	 * in [[dropDownList()]] for the explanation of these elements.
	 *
	 * @return string the generated list options
	 */
	public static function renderOptions($items, $selection = null, &$attributes = array())
	{
		$lines = array();
		if (isset($attributes['prompt'])) {
			$prompt = strtr(static::encode($attributes['prompt']), ' ', '&nbsp;');
			$lines[] = static::tag('option', $prompt, array('value' => ''));
		}

		$options = isset($attributes['options']) ? $attributes['options'] : array();
		$groups = isset($attributes['groups']) ? $attributes['groups'] : array();
		unset($attributes['prompt'], $attributes['options'], $attributes['groups']);

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$groupAttrs = isset($groups[$key]) ? $groups[$key] : array();
				$groupAttrs['label'] = $key;
				$attrs = array('options' => $options, 'groups' => $groups);
				$content = static::renderOptions($selection, $value, $attrs);
				$lines[] = static::tag('optgroup', "\n" . $content . "\n", $groupAttrs);
			} else {
				$attrs = isset($options[$key]) ? $options[$key] : array();
				$attrs['value'] = $key;
				$attrs['selected'] = $selection !== null &&
					(!is_array($selection) && !strcmp($key, $selection)
					|| is_array($selection) && in_array($key, $selection));
				$lines[] = static::tag('option', strtr(static::encode($value), ' ', '&nbsp;'), $attrs);
			}
		}

		return implode("\n", $lines);
	}

	/**
	 * Renders the HTML tag attributes.
	 * Boolean attributes such as s 'checked', 'disabled', 'readonly', will be handled specially
	 * according to [[booleanAttributes]] and [[showBooleanAttributeValues]].
	 * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the rendering result.
	 * @return string the rendering result. If the attributes are not empty, they will be rendered
	 * into a string with a leading white space (such that it can be directly appended to the tag name
	 * in a tag. If there is no attribute, an empty string will be returned.
	 */
	public static function renderAttributes($attributes)
	{
		if (count($attributes) > 1) {
			$sorted = array();
			foreach (static::$attributeOrder as $name) {
				if (isset($attributes[$name])) {
					$sorted[$name] = $attributes[$name];
				}
			}
			$attributes = array_merge($sorted, $attributes);
		}

		$html = '';
		foreach ($attributes as $name => $value) {
			if (isset(static::$booleanAttributes[strtolower($name)])) {
				if ($value || strcasecmp($name, $value) === 0) {
					$html .= static::$showBooleanAttributeValues ? " $name=\"$name\"" : " $name";
				}
			} elseif ($value !== null) {
				$html .= " $name=\"" . static::encode($value) . '"';
			}
		}
		return $html;
	}

	/**
	 * Normalizes the input parameter to be a valid URL.
	 *
	 * If the input parameter
	 *
	 * - is an empty string: the currently requested URL will be returned;
	 * - is a non-empty string: it will be processed by [[Yii::getAlias()]] which, if the string is an alias,
	 *   will be resolved into a URL;
	 * - is an array: the first array element is considered a route, while the rest of the name-value
	 *   pairs are considered as the parameters to be used for URL creation using [[\yii\base\Application::createUrl()]].
	 *   Here are some examples: `array('post/index', 'page' => 2)`, `array('index')`.
	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function url($url)
	{
		if (is_array($url)) {
			if (isset($url[0])) {
				return Yii::$app->createUrl($url[0], array_splice($url, 1));
			} else {
				throw new InvalidParamException('The array specifying a URL must contain at least one element.');
			}
		} elseif ($url === '') {
			return Yii::$app->getRequest()->getUrl();
		} else {
			return Yii::getAlias($url);
		}
	}
}
