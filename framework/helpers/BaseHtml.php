<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecordInterface;
use yii\web\Request;
use yii\base\Model;

/**
 * BaseHtml provides concrete implementation for [[Html]].
 *
 * Do not use BaseHtml. Use [[Html]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseHtml
{
	/**
	 * @var array list of void elements (element name => 1)
	 * @see http://www.w3.org/TR/html-markup/syntax.html#void-element
	 */
	public static $voidElements = [
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
	];
	/**
	 * @var array the preferred order of attributes in a tag. This mainly affects the order of the attributes
	 * that are rendered by [[renderTagAttributes()]].
	 */
	public static $attributeOrder = [
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
		'multiple',

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
	];

	/**
	 * Encodes special characters into HTML entities.
	 * The [[yii\base\Application::charset|application charset]] will be used for encoding.
	 * @param string $content the content to be encoded
	 * @param boolean $doubleEncode whether to encode HTML entities in `$content`. If false,
	 * HTML entities in `$content` will not be further encoded.
	 * @return string the encoded content
	 * @see decode()
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	public static function encode($content, $doubleEncode = true)
	{
		return htmlspecialchars($content, ENT_QUOTES, Yii::$app->charset, $doubleEncode);
	}

	/**
	 * Decodes special HTML entities back to the corresponding characters.
	 * This is the opposite of [[encode()]].
	 * @param string $content the content to be decoded
	 * @return string the decoded content
	 * @see encode()
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
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated HTML tag
	 * @see beginTag()
	 * @see endTag()
	 */
	public static function tag($name, $content = '', $options = [])
	{
		$html = "<$name" . static::renderTagAttributes($options) . '>';
		return isset(static::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
	}

	/**
	 * Generates a start tag.
	 * @param string $name the tag name
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated start tag
	 * @see endTag()
	 * @see tag()
	 */
	public static function beginTag($name, $options = [])
	{
		return "<$name" . static::renderTagAttributes($options) . '>';
	}

	/**
	 * Generates an end tag.
	 * @param string $name the tag name
	 * @return string the generated end tag
	 * @see beginTag()
	 * @see tag()
	 */
	public static function endTag($name)
	{
		return "</$name>";
	}

	/**
	 * Generates a style tag.
	 * @param string $content the style content
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * If the options does not contain "type", a "type" attribute with value "text/css" will be used.
	 * @return string the generated style tag
	 */
	public static function style($content, $options = [])
	{
		return static::tag('style', $content, $options);
	}

	/**
	 * Generates a script tag.
	 * @param string $content the script content
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * If the options does not contain "type", a "type" attribute with value "text/javascript" will be rendered.
	 * @return string the generated script tag
	 */
	public static function script($content, $options = [])
	{
		return static::tag('script', $content, $options);
	}

	/**
	 * Generates a link tag that refers to an external CSS file.
	 * @param array|string $url the URL of the external CSS file. This parameter will be processed by [[url()]].
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated link tag
	 * @see url()
	 */
	public static function cssFile($url, $options = [])
	{
		if (!isset($options['rel'])) {
			$options['rel'] = 'stylesheet';
		}
		$options['href'] = static::url($url);
		return static::tag('link', '', $options);
	}

	/**
	 * Generates a script tag that refers to an external JavaScript file.
	 * @param string $url the URL of the external JavaScript file. This parameter will be processed by [[url()]].
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated script tag
	 * @see url()
	 */
	public static function jsFile($url, $options = [])
	{
		$options['src'] = static::url($url);
		return static::tag('script', '', $options);
	}

	/**
	 * Generates a form start tag.
	 * @param array|string $action the form action URL. This parameter will be processed by [[url()]].
	 * @param string $method the form submission method, such as "post", "get", "put", "delete" (case-insensitive).
	 * Since most browsers only support "post" and "get", if other methods are given, they will
	 * be simulated using "post", and a hidden input will be added which contains the actual method type.
	 * See [[\yii\web\Request::restVar]] for more details.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated form start tag.
	 * @see endForm()
	 */
	public static function beginForm($action = '', $method = 'post', $options = [])
	{
		$action = static::url($action);

		$hiddenInputs = [];

		$request = Yii::$app->getRequest();
		if ($request instanceof Request) {
			if (strcasecmp($method, 'get') && strcasecmp($method, 'post')) {
				// simulate PUT, DELETE, etc. via POST
				$hiddenInputs[] = static::hiddenInput($request->restVar, $method);
				$method = 'post';
			}
			if ($request->enableCsrfValidation && !strcasecmp($method, 'post')) {
				$hiddenInputs[] = static::hiddenInput($request->csrfVar, $request->getCsrfToken());
			}
		}

		if (!strcasecmp($method, 'get') && ($pos = strpos($action, '?')) !== false) {
			// query parameters in the action are ignored for GET method
			// we use hidden fields to add them back
			foreach (explode('&', substr($action, $pos + 1)) as $pair) {
				if (($pos1 = strpos($pair, '=')) !== false) {
					$hiddenInputs[] = static::hiddenInput(
						urldecode(substr($pair, 0, $pos1)),
						urldecode(substr($pair, $pos1 + 1))
					);
				} else {
					$hiddenInputs[] = static::hiddenInput(urldecode($pair), '');
				}
			}
			$action = substr($action, 0, $pos);
		}

		$options['action'] = $action;
		$options['method'] = $method;
		$form = static::beginTag('form', $options);
		if (!empty($hiddenInputs)) {
			$form .= "\n" . implode("\n", $hiddenInputs);
		}

		return $form;
	}

	/**
	 * Generates a form end tag.
	 * @return string the generated tag
	 * @see beginForm()
	 */
	public static function endForm()
	{
		return '</form>';
	}

	/**
	 * Generates a hyperlink tag.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param array|string|null $url the URL for the hyperlink tag. This parameter will be processed by [[url()]]
	 * and will be used for the "href" attribute of the tag. If this parameter is null, the "href" attribute
	 * will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated hyperlink
	 * @see url()
	 */
	public static function a($text, $url = null, $options = [])
	{
		if ($url !== null) {
			$options['href'] = static::url($url);
		}
		return static::tag('a', $text, $options);
	}

	/**
	 * Generates a mailto hyperlink.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param string $email email address. If this is null, the first parameter (link body) will be treated
	 * as the email address and used.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated mailto link
	 */
	public static function mailto($text, $email = null, $options = [])
	{
		$options['href'] = 'mailto:' . ($email === null ? $text : $email);
		return static::tag('a', $text, $options);
	}

	/**
	 * Generates an image tag.
	 * @param string $src the image URL. This parameter will be processed by [[url()]].
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated image tag
	 */
	public static function img($src, $options = [])
	{
		$options['src'] = static::url($src);
		if (!isset($options['alt'])) {
			$options['alt'] = '';
		}
		return static::tag('img', '', $options);
	}

	/**
	 * Generates a label tag.
	 * @param string $content label text. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is is coming from end users, you should [[encode()]]
	 * it to prevent XSS attacks.
	 * @param string $for the ID of the HTML element that this label is associated with.
	 * If this is null, the "for" attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated label tag
	 */
	public static function label($content, $for = null, $options = [])
	{
		$options['for'] = $for;
		return static::tag('label', $content, $options);
	}

	/**
	 * Generates a button tag.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function button($content = 'Button', $options = [])
	{
		return static::tag('button', $content, $options);
	}

	/**
	 * Generates a submit button tag.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated submit button tag
	 */
	public static function submitButton($content = 'Submit', $options = [])
	{
		$options['type'] = 'submit';
		return static::button($content, $options);
	}

	/**
	 * Generates a reset button tag.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated reset button tag
	 */
	public static function resetButton($content = 'Reset', $options = [])
	{
		$options['type'] = 'reset';
		return static::button($content, $options);
	}

	/**
	 * Generates an input type of the given type.
	 * @param string $type the type attribute.
	 * @param string $name the name attribute. If it is null, the name attribute will not be generated.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated input tag
	 */
	public static function input($type, $name = null, $value = null, $options = [])
	{
		$options['type'] = $type;
		$options['name'] = $name;
		$options['value'] = $value === null ? null : (string)$value;
		return static::tag('input', '', $options);
	}

	/**
	 * Generates an input button.
	 * @param string $label the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function buttonInput($label = 'Button', $options = [])
	{
		$options['type'] = 'button';
		$options['value'] = $label;
		return static::tag('input', '', $options);
	}

	/**
	 * Generates a submit input button.
	 * @param string $label the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function submitInput($label = 'Submit', $options = [])
	{
		$options['type'] = 'submit';
		$options['value'] = $label;
		return static::tag('input', '', $options);
	}

	/**
	 * Generates a reset input button.
	 * @param string $label the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the attributes of the button tag. The values will be HTML-encoded using [[encode()]].
	 * Attributes whose value is null will be ignored and not put in the tag returned.
	 * @return string the generated button tag
	 */
	public static function resetInput($label = 'Reset', $options = [])
	{
		$options['type'] = 'reset';
		$options['value'] = $label;
		return static::tag('input', '', $options);
	}

	/**
	 * Generates a text input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function textInput($name, $value = null, $options = [])
	{
		return static::input('text', $name, $value, $options);
	}

	/**
	 * Generates a hidden input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function hiddenInput($name, $value = null, $options = [])
	{
		return static::input('hidden', $name, $value, $options);
	}

	/**
	 * Generates a password input field.
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function passwordInput($name, $value = null, $options = [])
	{
		return static::input('password', $name, $value, $options);
	}

	/**
	 * Generates a file input field.
	 * To use a file input field, you should set the enclosing form's "enctype" attribute to
	 * be "multipart/form-data". After the form is submitted, the uploaded file information
	 * can be obtained via $_FILES[$name] (see PHP documentation).
	 * @param string $name the name attribute.
	 * @param string $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated button tag
	 */
	public static function fileInput($name, $value = null, $options = [])
	{
		return static::input('file', $name, $value, $options);
	}

	/**
	 * Generates a text area input.
	 * @param string $name the input name
	 * @param string $value the input value. Note that it will be encoded using [[encode()]].
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * @return string the generated text area tag
	 */
	public static function textarea($name, $value = '', $options = [])
	{
		$options['name'] = $name;
		return static::tag('textarea', static::encode($value), $options);
	}

	/**
	 * Generates a radio button input.
	 * @param string $name the name attribute.
	 * @param boolean $checked whether the radio button should be checked.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. When this attribute
	 *   is present, a hidden input will be generated so that if the radio button is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 * - label: string, a label displayed next to the radio button.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent XSS attacks.
	 *   When this option is specified, the radio button will be enclosed by a label tag.
	 * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is specified.
	 * - container: array|boolean, the HTML attributes for the container tag. This is only used when the "label" option is specified.
	 *   If it is false, no container will be rendered. If it is an array or not, a "div" container will be rendered
	 *   around the the radio button.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting radio button tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated radio button tag
	 */
	public static function radio($name, $checked = false, $options = [])
	{
		$options['checked'] = (boolean)$checked;
		$value = array_key_exists('value', $options) ? $options['value'] : '1';
		if (isset($options['uncheck'])) {
			// add a hidden field so that if the radio button is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $options['uncheck']);
			unset($options['uncheck']);
		} else {
			$hidden = '';
		}
		if (isset($options['label'])) {
			$label = $options['label'];
			$labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
			$container = isset($options['container']) ? $options['container'] : ['class' => 'radio'];
			unset($options['label'], $options['labelOptions'], $options['container']);
			$content = static::label(static::input('radio', $name, $value, $options) . ' ' . $label, null, $labelOptions);
			if (is_array($container)) {
				return $hidden . static::tag('div', $content, $container);
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . static::input('radio', $name, $value, $options);
		}
	}

	/**
	 * Generates a checkbox input.
	 * @param string $name the name attribute.
	 * @param boolean $checked whether the checkbox should be checked.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute
	 *   is present, a hidden input will be generated so that if the checkbox is not checked and is submitted,
	 *   the value of this attribute will still be submitted to the server via the hidden input.
	 * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent XSS attacks.
	 *   When this option is specified, the checkbox will be enclosed by a label tag.
	 * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is specified.
	 * - container: array|boolean, the HTML attributes for the container tag. This is only used when the "label" option is specified.
	 *   If it is false, no container will be rendered. If it is an array or not, a "div" container will be rendered
	 *   around the the radio button.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting checkbox tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated checkbox tag
	 */
	public static function checkbox($name, $checked = false, $options = [])
	{
		$options['checked'] = (boolean)$checked;
		$value = array_key_exists('value', $options) ? $options['value'] : '1';
		if (isset($options['uncheck'])) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $options['uncheck']);
			unset($options['uncheck']);
		} else {
			$hidden = '';
		}
		if (isset($options['label'])) {
			$label = $options['label'];
			$labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
			$container = isset($options['container']) ? $options['container'] : ['class' => 'checkbox'];
			unset($options['label'], $options['labelOptions'], $options['container']);
			$content = static::label(static::input('checkbox', $name, $value, $options) . ' ' . $label, null, $labelOptions);
			if (is_array($container)) {
				return $hidden . static::tag('div', $content, $container);
			} else {
				return $hidden . $content;
			}
		} else {
			return $hidden . static::input('checkbox', $name, $value, $options);
		}
	}

	/**
	 * Generates a drop-down list.
	 * @param string $name the input name
	 * @param string $selection the selected value
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
	 * [
	 *     'value1' => ['disabled' => true],
	 *     'value2' => ['label' => 'value 2'],
	 * ];
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
	public static function dropDownList($name, $selection = null, $items = [], $options = [])
	{
		if (!empty($options['multiple'])) {
			return static::listBox($name, $selection, $items, $options);
		}
		$options['name'] = $name;
		$selectOptions = static::renderSelectOptions($selection, $items, $options);
		return static::tag('select', "\n" . $selectOptions . "\n", $options);
	}

	/**
	 * Generates a list box.
	 * @param string $name the input name
	 * @param string|array $selection the selected value(s)
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
	 * [
	 *     'value1' => ['disabled' => true],
	 *     'value2' => ['label' => 'value 2'],
	 * ];
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
	public static function listBox($name, $selection = null, $items = [], $options = [])
	{
		if (!array_key_exists('size', $options)) {
			$options['size'] = 4;
		}
		if (!empty($options['multiple']) && substr($name, -2) !== '[]') {
			$name .= '[]';
		}
		$options['name'] = $name;
		if (isset($options['unselect'])) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			if (substr($name, -2) === '[]') {
				$name = substr($name, 0, -2);
			}
			$hidden = static::hiddenInput($name, $options['unselect']);
			unset($options['unselect']);
		} else {
			$hidden = '';
		}
		$selectOptions = static::renderSelectOptions($selection, $items, $options);
		return $hidden . static::tag('select', "\n" . $selectOptions . "\n", $options);
	}

	/**
	 * Generates a list of checkboxes.
	 * A checkbox list allows multiple selection, like [[listBox()]].
	 * As a result, the corresponding submitted value is an array.
	 * @param string $name the name attribute of each checkbox.
	 * @param string|array $selection the selected value(s).
	 * @param array $items the data item used to generate the checkboxes.
	 * The array values are the labels, while the array keys are the corresponding checkbox values.
	 * @param array $options options (name => config) for the checkbox list container tag.
	 * The following options are specially handled:
	 *
	 * - tag: string, the tag name of the container element.
	 * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
	 *   This option is ignored if `item` option is set.
	 * - separator: string, the HTML code that separates items.
	 * - itemOptions: array, the options for generating the radio button tag using [[checkbox()]].
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $checked, $value)
	 * ~~~
	 *
	 * where $index is the zero-based index of the checkbox in the whole list; $label
	 * is the label for the checkbox; and $name, $value and $checked represent the name,
	 * value and the checked status of the checkbox input, respectively.
	 * @return string the generated checkbox list
	 */
	public static function checkboxList($name, $selection = null, $items = [], $options = [])
	{
		if (substr($name, -2) !== '[]') {
			$name .= '[]';
		}

		$formatter = isset($options['item']) ? $options['item'] : null;
		$itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
		$encode = !isset($options['encode']) || $options['encode'];
		$lines = [];
		$index = 0;
		foreach ($items as $value => $label) {
			$checked = $selection !== null &&
				(!is_array($selection) && !strcmp($value, $selection)
					|| is_array($selection) && in_array($value, $selection));
			if ($formatter !== null) {
				$lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
			} else {
				$lines[] = static::checkbox($name, $checked, array_merge($itemOptions, [
					'value' => $value,
					'label' => $encode ? static::encode($label) : $label,
				]));
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

		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag'], $options['unselect'], $options['encode'], $options['separator'], $options['item'], $options['itemOptions']);

		return $hidden . static::tag($tag, implode($separator, $lines), $options);
	}

	/**
	 * Generates a list of radio buttons.
	 * A radio button list is like a checkbox list, except that it only allows single selection.
	 * @param string $name the name attribute of each radio button.
	 * @param string|array $selection the selected value(s).
	 * @param array $items the data item used to generate the radio buttons.
	 * The array values are the labels, while the array keys are the corresponding radio button values.
	 * @param array $options options (name => config) for the radio button list. The following options are supported:
	 *
	 * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
	 *   By setting this option, a hidden input will be generated.
	 * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
	 *   This option is ignored if `item` option is set.
	 * - separator: string, the HTML code that separates items.
	 * - itemOptions: array, the options for generating the radio button tag using [[radio()]].
	 * - item: callable, a callback that can be used to customize the generation of the HTML code
	 *   corresponding to a single item in $items. The signature of this callback must be:
	 *
	 * ~~~
	 * function ($index, $label, $name, $checked, $value)
	 * ~~~
	 *
	 * where $index is the zero-based index of the radio button in the whole list; $label
	 * is the label for the radio button; and $name, $value and $checked represent the name,
	 * value and the checked status of the radio button input, respectively.
	 * @return string the generated radio button list
	 */
	public static function radioList($name, $selection = null, $items = [], $options = [])
	{
		$encode = !isset($options['encode']) || $options['encode'];
		$formatter = isset($options['item']) ? $options['item'] : null;
		$itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
		$lines = [];
		$index = 0;
		foreach ($items as $value => $label) {
			$checked = $selection !== null &&
				(!is_array($selection) && !strcmp($value, $selection)
					|| is_array($selection) && in_array($value, $selection));
			if ($formatter !== null) {
				$lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
			} else {
				$lines[] = static::radio($name, $checked, array_merge($itemOptions, [
					'value' => $value,
					'label' => $encode ? static::encode($label) : $label,
				]));
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

		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag'], $options['unselect'], $options['encode'], $options['separator'], $options['item'], $options['itemOptions']);

		return $hidden . static::tag($tag, implode($separator, $lines), $options);
	}

	/**
	 * Generates an unordered list.
	 * @param array|\Traversable $items the items for generating the list. Each item generates a single list item.
	 * Note that items will be automatically HTML encoded if `$options['encode']` is not set or true.
	 * @param array $options options (name => config) for the radio button list. The following options are supported:
	 *
	 * - encode: boolean, whether to HTML-encode the items. Defaults to true.
	 *   This option is ignored if the `item` option is specified.
	 * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is specified.
	 * - item: callable, a callback that is used to generate each individual list item.
	 *   The signature of this callback must be:
	 *
	 * ~~~
	 * function ($item, $index)
	 * ~~~
	 *
	 * where $index is the array key corresponding to `$item` in `$items`. The callback should return
	 * the whole list item tag.
	 *
	 * @return string the generated unordered list. An empty string is returned if `$items` is empty.
	 */
	public static function ul($items, $options = [])
	{
		if (empty($items)) {
			return '';
		}
		$tag = isset($options['tag']) ? $options['tag'] : 'ul';
		$encode = !isset($options['encode']) || $options['encode'];
		$formatter = isset($options['item']) ? $options['item'] : null;
		$itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
		unset($options['tag'], $options['encode'], $options['item'], $options['itemOptions']);
		$results = [];
		foreach ($items as $index => $item) {
			if ($formatter !== null) {
				$results[] = call_user_func($formatter, $item, $index);
			} else {
				$results[] = static::tag('li', $encode ? static::encode($item) : $item, $itemOptions);
			}
		}
		return static::tag($tag, "\n" . implode("\n", $results) . "\n", $options);
	}

	/**
	 * Generates an ordered list.
	 * @param array|\Traversable $items the items for generating the list. Each item generates a single list item.
	 * Note that items will be automatically HTML encoded if `$options['encode']` is not set or true.
	 * @param array $options options (name => config) for the radio button list. The following options are supported:
	 *
	 * - encode: boolean, whether to HTML-encode the items. Defaults to true.
	 *   This option is ignored if the `item` option is specified.
	 * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is specified.
	 * - item: callable, a callback that is used to generate each individual list item.
	 *   The signature of this callback must be:
	 *
	 * ~~~
	 * function ($item, $index)
	 * ~~~
	 *
	 * where $index is the array key corresponding to `$item` in `$items`. The callback should return
	 * the whole list item tag.
	 *
	 * @return string the generated ordered list. An empty string is returned if `$items` is empty.
	 */
	public static function ol($items, $options = [])
	{
		$options['tag'] = 'ol';
		return static::ul($items, $options);
	}

	/**
	 * Generates a label tag for the given model attribute.
	 * The label text is the label associated with the attribute, obtained via [[Model::getAttributeLabel()]].
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * The following options are specially handled:
	 *
	 * - label: this specifies the label to be displayed. Note that this will NOT be [[encoded()]].
	 *   If this is not set, [[Model::getAttributeLabel()]] will be called to get the label for display
	 *   (after encoding).
	 *
	 * @return string the generated label tag
	 */
	public static function activeLabel($model, $attribute, $options = [])
	{
		$for = array_key_exists('for', $options) ? $options['for'] : static::getInputId($model, $attribute);
		$attribute = static::getAttributeName($attribute);
		$label = isset($options['label']) ? $options['label'] : static::encode($model->getAttributeLabel($attribute));
		unset($options['label'], $options['for']);
		return static::label($label, $for, $options);
	}

	/**
	 * Generates a tag that contains the first validation error of the specified model attribute.
	 * Note that even if there is no validation error, this method will still return an empty error tag.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. The values will be HTML-encoded
	 * using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * The following options are specially handled:
	 *
	 * - tag: this specifies the tag name. If not set, "div" will be used.
	 *
	 * @return string the generated label tag
	 */
	public static function error($model, $attribute, $options = [])
	{
		$attribute = static::getAttributeName($attribute);
		$error = $model->getFirstError($attribute);
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag']);
		return Html::tag($tag, Html::encode($error), $options);
	}

	/**
	 * Generates an input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param string $type the input type (e.g. 'text', 'password')
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public static function activeInput($type, $model, $attribute, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$value = isset($options['value']) ? $options['value'] : static::getAttributeValue($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::input($type, $name, $value, $options);
	}

	/**
	 * Generates a text input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public static function activeTextInput($model, $attribute, $options = [])
	{
		return static::activeInput('text', $model, $attribute, $options);
	}

	/**
	 * Generates a hidden input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public static function activeHiddenInput($model, $attribute, $options = [])
	{
		return static::activeInput('hidden', $model, $attribute, $options);
	}

	/**
	 * Generates a password input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public static function activePasswordInput($model, $attribute, $options = [])
	{
		return static::activeInput('password', $model, $attribute, $options);
	}

	/**
	 * Generates a file input tag for the given model attribute.
	 * This method will generate the "name" and "value" tag attributes automatically for the model attribute
	 * unless they are explicitly specified in `$options`.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated input tag
	 */
	public static function activeFileInput($model, $attribute, $options = [])
	{
		// add a hidden field so that if a model only has a file field, we can
		// still use isset($_POST[$modelClass]) to detect if the input is submitted
		return static::activeHiddenInput($model, $attribute, ['id' => null, 'value' => ''])
			. static::activeInput('file', $model, $attribute, $options);
	}

	/**
	 * Generates a textarea tag for the given model attribute.
	 * The model attribute value will be used as the content in the textarea.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated textarea tag
	 */
	public static function activeTextarea($model, $attribute, $options = [])
	{
		$name = static::getInputName($model, $attribute);
		$value = static::getAttributeValue($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::textarea($name, $value, $options);
	}

	/**
	 * Generates a radio button tag for the given model attribute.
	 * This method will generate the "checked" tag attribute according to the model attribute value.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 * - label: string, a label displayed next to the radio button.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent XSS attacks.
	 *   When this option is specified, the radio button will be enclosed by a label tag.
	 * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is specified.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated radio button tag
	 */
	public static function activeRadio($model, $attribute, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$value = static::getAttributeValue($model, $attribute);

		if (!array_key_exists('value', $options)) {
			$options['value'] = '1';
		}
		if (!array_key_exists('uncheck', $options)) {
			$options['uncheck'] = '0';
		}

		$checked = "$value" === "{$options['value']}";

		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::radio($name, $checked, $options);
	}

	/**
	 * Generates a checkbox tag for the given model attribute.
	 * This method will generate the "checked" tag attribute according to the model attribute value.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
	 *
	 * - uncheck: string, the value associated with the uncheck state of the radio button. If not set,
	 *   it will take the default value '0'. This method will render a hidden input so that if the radio button
	 *   is not checked and is submitted, the value of this attribute will still be submitted to the server
	 *   via the hidden input.
	 * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
	 *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent XSS attacks.
	 *   When this option is specified, the checkbox will be enclosed by a label tag.
	 * - labelOptions: array, the HTML attributes for the label tag. This is only used when the "label" option is specified.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 *
	 * @return string the generated checkbox tag
	 */
	public static function activeCheckbox($model, $attribute, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$value = static::getAttributeValue($model, $attribute);

		if (!array_key_exists('value', $options)) {
			$options['value'] = '1';
		}
		if (!array_key_exists('uncheck', $options)) {
			$options['uncheck'] = '0';
		}

		$checked = "$value" === "{$options['value']}";

		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::checkbox($name, $checked, $options);
	}

	/**
	 * Generates a drop-down list for the given model attribute.
	 * The selection of the drop-down list is taken from the value of the model attribute.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
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
	 * [
	 *     'value1' => ['disabled' => true],
	 *     'value2' => ['label' => 'value 2'],
	 * ];
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
	public static function activeDropDownList($model, $attribute, $items, $options = [])
	{
		if (!empty($options['multiple'])) {
			return static::activeListBox($model, $attribute, $items, $options);
		}
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$selection = static::getAttributeValue($model, $attribute);
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::dropDownList($name, $selection, $items, $options);
	}

	/**
	 * Generates a list box.
	 * The selection of the list box is taken from the value of the model attribute.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
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
	 * [
	 *     'value1' => ['disabled' => true],
	 *     'value2' => ['label' => 'value 2'],
	 * ];
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
	public static function activeListBox($model, $attribute, $items, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$selection = static::getAttributeValue($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::listBox($name, $selection, $items, $options);
	}

	/**
	 * Generates a list of checkboxes.
	 * A checkbox list allows multiple selection, like [[listBox()]].
	 * As a result, the corresponding submitted value is an array.
	 * The selection of the checkbox list is taken from the value of the model attribute.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $items the data item used to generate the checkboxes.
	 * The array values are the labels, while the array keys are the corresponding checkbox values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param array $options options (name => config) for the checkbox list. The following options are specially handled:
	 *
	 * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
	 *   You may set this option to be null to prevent default value submission.
	 *   If this option is not set, an empty string will be submitted.
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
	public static function activeCheckboxList($model, $attribute, $items, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$selection = static::getAttributeValue($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::checkboxList($name, $selection, $items, $options);
	}

	/**
	 * Generates a list of radio buttons.
	 * A radio button list is like a checkbox list, except that it only allows single selection.
	 * The selection of the radio buttons is taken from the value of the model attribute.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
	 * about attribute expression.
	 * @param array $items the data item used to generate the radio buttons.
	 * The array keys are the labels, while the array values are the corresponding radio button values.
	 * Note that the labels will NOT be HTML-encoded, while the values will.
	 * @param array $options options (name => config) for the radio button list. The following options are specially handled:
	 *
	 * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
	 *   You may set this option to be null to prevent default value submission.
	 *   If this option is not set, an empty string will be submitted.
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
	public static function activeRadioList($model, $attribute, $items, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$selection = static::getAttributeValue($model, $attribute);
		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}
		return static::radioList($name, $selection, $items, $options);
	}

	/**
	 * Renders the option tags that can be used by [[dropDownList()]] and [[listBox()]].
	 * @param string|array $selection the selected value(s). This can be either a string for single selection
	 * or an array for multiple selections.
	 * @param array $items the option data items. The array keys are option values, and the array values
	 * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
	 * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
	 * If you have a list of data models, you may convert them into the format described above using
	 * [[\yii\helpers\ArrayHelper::map()]].
	 *
	 * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
	 * the labels will also be HTML-encoded.
	 * @param array $tagOptions the $options parameter that is passed to the [[dropDownList()]] or [[listBox()]] call.
	 * This method will take out these elements, if any: "prompt", "options" and "groups". See more details
	 * in [[dropDownList()]] for the explanation of these elements.
	 *
	 * @return string the generated list options
	 */
	public static function renderSelectOptions($selection, $items, &$tagOptions = [])
	{
		$lines = [];
		if (isset($tagOptions['prompt'])) {
			$prompt = str_replace(' ', '&nbsp;', static::encode($tagOptions['prompt']));
			$lines[] = static::tag('option', $prompt, ['value' => '']);
		}

		$options = isset($tagOptions['options']) ? $tagOptions['options'] : [];
		$groups = isset($tagOptions['groups']) ? $tagOptions['groups'] : [];
		unset($tagOptions['prompt'], $tagOptions['options'], $tagOptions['groups']);

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$groupAttrs = isset($groups[$key]) ? $groups[$key] : [];
				$groupAttrs['label'] = $key;
				$attrs = ['options' => $options, 'groups' => $groups];
				$content = static::renderSelectOptions($selection, $value, $attrs);
				$lines[] = static::tag('optgroup', "\n" . $content . "\n", $groupAttrs);
			} else {
				$attrs = isset($options[$key]) ? $options[$key] : [];
				$attrs['value'] = (string)$key;
				$attrs['selected'] = $selection !== null &&
						(!is_array($selection) && !strcmp($key, $selection)
						|| is_array($selection) && in_array($key, $selection));
				$lines[] = static::tag('option', str_replace(' ', '&nbsp;', static::encode($value)), $attrs);
			}
		}

		return implode("\n", $lines);
	}

	/**
	 * Renders the HTML tag attributes.
	 * Attributes whose values are of boolean type will be treated as
	 * [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
	 * And attributes whose values are null will not be rendered.
	 * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
	 * @return string the rendering result. If the attributes are not empty, they will be rendered
	 * into a string with a leading white space (so that it can be directly appended to the tag name
	 * in a tag. If there is no attribute, an empty string will be returned.
	 */
	public static function renderTagAttributes($attributes)
	{
		if (count($attributes) > 1) {
			$sorted = [];
			foreach (static::$attributeOrder as $name) {
				if (isset($attributes[$name])) {
					$sorted[$name] = $attributes[$name];
				}
			}
			$attributes = array_merge($sorted, $attributes);
		}

		$html = '';
		foreach ($attributes as $name => $value) {
			if (is_bool($value)) {
				if ($value) {
					$html .= " $name";
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
	 * - is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
	 *   is an absolute URL, it will be returned without any change further; Otherwise, the result
	 *   will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
	 * - is an array: the first array element is considered a route, while the rest of the name-value
	 *   pairs are treated as the parameters to be used for URL creation using [[\yii\web\Controller::createUrl()]].
	 *   For example: `['post/index', 'page' => 2]`, `['index']`.
	 *   In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function url($url)
	{
		if (is_array($url)) {
			if (isset($url[0])) {
				$route = $url[0];
				$params = array_splice($url, 1);
				if (Yii::$app->controller instanceof \yii\web\Controller) {
					return Yii::$app->controller->createUrl($route, $params);
				} else {
					return Yii::$app->getUrlManager()->createUrl($route, $params);
				}
			} else {
				throw new InvalidParamException('The array specifying a URL must contain at least one element.');
			}
		} elseif ($url === '') {
			return Yii::$app->getRequest()->getUrl();
		} else {
			$url = Yii::getAlias($url);
			if ($url !== '' && ($url[0] === '/' || $url[0] === '#' || strpos($url, '://') || !strncmp($url, './', 2))) {
				return $url;
			} else {
				return Yii::$app->getRequest()->getBaseUrl() . '/' . $url;
			}
		}
	}

	/**
	 * Adds a CSS class to the specified options.
	 * If the CSS class is already in the options, it will not be added again.
	 * @param array $options the options to be modified.
	 * @param string $class the CSS class to be added
	 */
	public static function addCssClass(&$options, $class)
	{
		if (isset($options['class'])) {
			$classes = ' ' . $options['class'] . ' ';
			if (($pos = strpos($classes, ' ' . $class . ' ')) === false) {
				$options['class'] .= ' ' . $class;
			}
		} else {
			$options['class'] = $class;
		}
	}

	/**
	 * Removes a CSS class from the specified options.
	 * @param array $options the options to be modified.
	 * @param string $class the CSS class to be removed
	 */
	public static function removeCssClass(&$options, $class)
	{
		if (isset($options['class'])) {
			$classes = array_unique(preg_split('/\s+/', $options['class'] . ' ' . $class, -1, PREG_SPLIT_NO_EMPTY));
			if (($index = array_search($class, $classes)) !== false) {
				unset($classes[$index]);
			}
			if (empty($classes)) {
				unset($options['class']);
			} else {
				$options['class'] = implode(' ', $classes);
			}
		}
	}

	/**
	 * Returns the real attribute name from the given attribute expression.
	 *
	 * An attribute expression is an attribute name prefixed and/or suffixed with array indexes.
	 * It is mainly used in tabular data input and/or input of array type. Below are some examples:
	 *
	 * - `[0]content` is used in tabular data input to represent the "content" attribute
	 *   for the first model in tabular input;
	 * - `dates[0]` represents the first array element of the "dates" attribute;
	 * - `[0]dates[0]` represents the first array element of the "dates" attribute
	 *   for the first model in tabular input.
	 *
	 * If `$attribute` has neither prefix nor suffix, it will be returned back without change.
	 * @param string $attribute the attribute name or expression
	 * @return string the attribute name without prefix and suffix.
	 * @throws InvalidParamException if the attribute name contains non-word characters.
	 */
	public static function getAttributeName($attribute)
	{
		if (preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			return $matches[2];
		} else {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
	}

	/**
	 * Returns the value of the specified attribute name or expression.
	 *
	 * For an attribute expression like `[0]dates[0]`, this method will return the value of `$model->dates[0]`.
	 * See [[getAttributeName()]] for more details about attribute expression.
	 *
	 * If an attribute value is an instance of [[ActiveRecordInterface]] or an array of such instances,
	 * the primary value(s) of the AR instance(s) will be returned instead.
	 *
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression
	 * @return string|array the corresponding attribute value
	 * @throws InvalidParamException if the attribute name contains non-word characters.
	 */
	public static function getAttributeValue($model, $attribute)
	{
		if (!preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
		$attribute = $matches[2];
		$value = $model->$attribute;
		if ($matches[3] !== '') {
			foreach (explode('][', trim($matches[3], '[]')) as $id) {
				if ((is_array($value) || $value instanceof \ArrayAccess) && isset($value[$id])) {
					$value = $value[$id];
				} else {
					return null;
				}
			}
		}

		// https://github.com/yiisoft/yii2/issues/1457
		if (is_array($value)) {
			foreach ($value as $i => $v) {
				if ($v instanceof ActiveRecordInterface) {
					$v = $v->getPrimaryKey(false);
					$value[$i] = is_array($v) ? json_encode($v) : $v;
				}
			}
		} elseif ($value instanceof ActiveRecordInterface) {
			$value = $value->getPrimaryKey(false);
			return is_array($value) ? json_encode($value) : $value;
		}
		return $value;
	}

	/**
	 * Generates an appropriate input name for the specified attribute name or expression.
	 *
	 * This method generates a name that can be used as the input name to collect user input
	 * for the specified attribute. The name is generated according to the [[Model::formName|form name]]
	 * of the model and the given attribute name. For example, if the form name of the `Post` model
	 * is `Post`, then the input name generated for the `content` attribute would be `Post[content]`.
	 *
	 * See [[getAttributeName()]] for explanation of attribute expression.
	 *
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression
	 * @return string the generated input name
	 * @throws InvalidParamException if the attribute name contains non-word characters.
	 */
	public static function getInputName($model, $attribute)
	{
		$formName = $model->formName();
		if (!preg_match('/(^|.*\])(\w+)(\[.*|$)/', $attribute, $matches)) {
			throw new InvalidParamException('Attribute name must contain word characters only.');
		}
		$prefix = $matches[1];
		$attribute = $matches[2];
		$suffix = $matches[3];
		if ($formName === '' && $prefix === '') {
			return $attribute . $suffix;
		} elseif ($formName !== '') {
			return $formName . $prefix . "[$attribute]" . $suffix;
		} else {
			throw new InvalidParamException(get_class($model) . '::formName() cannot be empty for tabular inputs.');
		}
	}

	/**
	 * Generates an appropriate input ID for the specified attribute name or expression.
	 *
	 * This method converts the result [[getInputName()]] into a valid input ID.
	 * For example, if [[getInputName()]] returns `Post[content]`, this method will return `post-content`.
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for explanation of attribute expression.
	 * @return string the generated input ID
	 * @throws InvalidParamException if the attribute name contains non-word characters.
	 */
	public static function getInputId($model, $attribute)
	{
		$name = strtolower(static::getInputName($model, $attribute));
		return str_replace(['[]', '][', '[', ']', ' '], ['', '-', '-', '', '-'], $name);
	}
}
