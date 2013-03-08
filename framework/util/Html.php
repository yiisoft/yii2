<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use Yii;

/**
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
	 * @var boolean whether to render special attributes value. Defaults to true. Can be set to false for HTML5.
	 */
	public static $renderSpecialAttributesValue = true;


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
		return !static::$closeVoidElements && isset(static::$voidElements[strtolower($name)]) ? '' : "</$name>";
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
		return static::beginTag('style', $attributes)
			. "\n/*<![CDATA[*/\n{$content}\n/*]]>*/\n"
			. static::endTag('style');
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
		return static::beginTag('script', $attributes)
			. "\n/*<![CDATA[*/\n{$content}\n/*]]>*/\n"
			. static::endTag('script');
	}

	/**
	 * Registers a 'refresh' meta tag.
	 * This method can be invoked anywhere in a view. It will register a 'refresh'
	 * meta tag with {@link CClientScript} so that the page can be refreshed in
	 * the specified seconds.
	 * @param integer $seconds the number of seconds to wait before refreshing the page
	 * @param string $url the URL to which the page should be redirected to. If empty, it means the current page.
	 * @since 1.1.1
	 */
	public static function refresh($seconds, $url = '')
	{
		$content = "$seconds";
		if ($url !== '') {
			$content .= ';' . static::normalizeUrl($url);
		}
		Yii::app()->clientScript->registerMetaTag($content, null, 'refresh');
	}

	/**
	 * Links to the specified CSS file.
	 * @param string $url the CSS URL
	 * @param string $media the media that this CSS should apply to.
	 * @return string the CSS link.
	 */
	public static function cssFile($url, $media = '')
	{
		return CHtml::linkTag('stylesheet', 'text/css', $url, $media !== '' ? $media : null);
	}

	/**
	 * Encloses the given JavaScript within a script tag.
	 * @param string $text the JavaScript to be enclosed
	 * @return string the enclosed JavaScript
	 */
	public static function script($text)
	{
		return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</script>";
	}

	/**
	 * Includes a JavaScript file.
	 * @param string $url URL for the JavaScript file
	 * @return string the JavaScript file tag
	 */
	public static function scriptFile($url)
	{
		return '<script type="text/javascript" src="' . static::encode($url) . '"></script>';
	}

	/**
	 * Generates an opening form tag.
	 * This is a shortcut to {@link beginForm}.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string $method form method (e.g. post, get)
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 */
	public static function form($action = '', $method = 'post', $htmlOptions = array())
	{
		return static::beginForm($action, $method, $htmlOptions);
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string $method form method (e.g. post, get)
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 * @see endForm
	 */
	public static function beginForm($action = '', $method = 'post', $htmlOptions = array())
	{
		$htmlOptions['action'] = $url = static::normalizeUrl($action);
		$htmlOptions['method'] = $method;
		$form = static::tag('form', $htmlOptions, false, false);
		$hiddens = array();
		if (!strcasecmp($method, 'get') && ($pos = strpos($url, '?')) !== false) {
			foreach (explode('&', substr($url, $pos + 1)) as $pair) {
				if (($pos = strpos($pair, '=')) !== false) {
					$hiddens[] = static::hiddenField(urldecode(substr($pair, 0, $pos)), urldecode(substr($pair, $pos + 1)), array('id' => false));
				} else {
					$hiddens[] = static::hiddenField(urldecode($pair), '', array('id' => false));
				}
			}
		}
		$request = Yii::app()->request;
		if ($request->enableCsrfValidation && !strcasecmp($method, 'post')) {
			$hiddens[] = static::hiddenField($request->csrfTokenName, $request->getCsrfToken(), array('id' => false));
		}
		if ($hiddens !== array()) {
			$form .= "\n" . static::tag('div', array('style' => 'display:none'), implode("\n", $hiddens));
		}
		return $form;
	}

	/**
	 * Generates a closing form tag.
	 * @return string the generated tag
	 * @see beginForm
	 */
	public static function endForm()
	{
		return '</form>';
	}

	/**
	 * Generates a hyperlink tag.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param mixed $url a URL or an action route that can be used to create a URL.
	 * See {@link normalizeUrl} for more details about how to specify this parameter.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated hyperlink
	 * @see normalizeUrl
	 * @see clientChange
	 */
	public static function link($text, $url = '#', $htmlOptions = array())
	{
		if ($url !== '') {
			$htmlOptions['href'] = static::normalizeUrl($url);
		}
		static::clientChange('click', $htmlOptions);
		return static::tag('a', $htmlOptions, $text);
	}

	/**
	 * Generates a mailto link.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param string $email email address. If this is empty, the first parameter (link body) will be treated as the email address.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated mailto link
	 * @see clientChange
	 */
	public static function mailto($text, $email = '', $htmlOptions = array())
	{
		if ($email === '') {
			$email = $text;
		}
		return static::link($text, 'mailto:' . $email, $htmlOptions);
	}

	/**
	 * Generates an image tag.
	 * @param string $src the image URL
	 * @param string $alt the alternative text display
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated image tag
	 */
	public static function image($src, $alt = '', $htmlOptions = array())
	{
		$htmlOptions['src'] = $src;
		$htmlOptions['alt'] = $alt;
		return static::tag('img', $htmlOptions);
	}

	/**
	 * Generates a button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function button($name, $label = 'button', $htmlOptions = array())
	{
		if (!isset($htmlOptions['name'])) {
			if (!array_key_exists('name', $htmlOptions)) {
				$htmlOptions['name'] = static::ID_PREFIX . static::$count++;
			}
		}
		if (!isset($htmlOptions['type'])) {
			$htmlOptions['type'] = 'button';
		}
		if (!isset($htmlOptions['value'])) {
			$htmlOptions['value'] = $label;
		}
		static::clientChange('click', $htmlOptions);
		return static::tag('input', $htmlOptions);
	}

	/**
	 * Generates a button using HTML button tag.
	 * This method is similar to {@link button} except that it generates a 'button'
	 * tag instead of 'input' tag.
	 * @param string $label the button label. Note that this value will be directly inserted in the button element
	 * without being HTML-encoded.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function htmlButton($label = 'button', $htmlOptions = array())
	{
		if (!isset($htmlOptions['name'])) {
			$htmlOptions['name'] = static::ID_PREFIX . static::$count++;
		}
		if (!isset($htmlOptions['type'])) {
			$htmlOptions['type'] = 'button';
		}
		static::clientChange('click', $htmlOptions);
		return static::tag('button', $htmlOptions, $label);
	}

	/**
	 * Generates a submit button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function submitButton($label = 'submit', $htmlOptions = array())
	{
		$htmlOptions['type'] = 'submit';
		return static::button($label, $htmlOptions);
	}

	/**
	 * Generates a reset button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function resetButton($label = 'reset', $htmlOptions = array())
	{
		$htmlOptions['type'] = 'reset';
		return static::button($label, $htmlOptions);
	}

	/**
	 * Generates an image submit button.
	 * @param string $src the image URL
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function imageButton($src, $htmlOptions = array())
	{
		$htmlOptions['src'] = $src;
		$htmlOptions['type'] = 'image';
		return static::button('submit', $htmlOptions);
	}

	/**
	 * Generates a link submit button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function linkButton($label = 'submit', $htmlOptions = array())
	{
		if (!isset($htmlOptions['submit'])) {
			$htmlOptions['submit'] = isset($htmlOptions['href']) ? $htmlOptions['href'] : '';
		}
		return static::link($label, '#', $htmlOptions);
	}

	/**
	 * Generates a label tag.
	 * @param string $label label text. Note, you should HTML-encode the text if needed.
	 * @param string $for the ID of the HTML element that this label is associated with.
	 * If this is false, the 'for' attribute for the label tag will not be rendered.
	 * @param array $htmlOptions additional HTML attributes.
	 * The following HTML option is recognized:
	 * <ul>
	 * <li>required: if this is set and is true, the label will be styled
	 * with CSS class 'required' (customizable with CHtml::$requiredCss),
	 * and be decorated with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.</li>
	 * </ul>
	 * @return string the generated label tag
	 */
	public static function label($label, $for, $htmlOptions = array())
	{
		if ($for === false) {
			unset($htmlOptions['for']);
		} else {
			$htmlOptions['for'] = $for;
		}
		if (isset($htmlOptions['required'])) {
			if ($htmlOptions['required']) {
				if (isset($htmlOptions['class'])) {
					$htmlOptions['class'] .= ' ' . static::$requiredCss;
				} else {
					$htmlOptions['class'] = static::$requiredCss;
				}
				$label = static::$beforeRequiredLabel . $label . static::$afterRequiredLabel;
			}
			unset($htmlOptions['required']);
		}
		return static::tag('label', $htmlOptions, $label);
	}

	/**
	 * Generates a text field input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see inputField
	 */
	public static function textField($name, $value = '', $htmlOptions = array())
	{
		static::clientChange('change', $htmlOptions);
		return static::inputField('text', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a hidden input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated input field
	 * @see inputField
	 */
	public static function hiddenField($name, $value = '', $htmlOptions = array())
	{
		return static::inputField('hidden', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a password field input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see inputField
	 */
	public static function passwordField($name, $value = '', $htmlOptions = array())
	{
		static::clientChange('change', $htmlOptions);
		return static::inputField('password', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a file input.
	 * Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
	 * After the form is submitted, the uploaded file information can be obtained via $_FILES[$name] (see
	 * PHP documentation).
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated input field
	 * @see inputField
	 */
	public static function fileField($name, $value = '', $htmlOptions = array())
	{
		return static::inputField('file', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a text area input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated text area
	 * @see clientChange
	 * @see inputField
	 */
	public static function textArea($name, $value = '', $htmlOptions = array())
	{
		$htmlOptions['name'] = $name;
		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = static::getIdByName($name);
		} elseif ($htmlOptions['id'] === false) {
			unset($htmlOptions['id']);
		}
		static::clientChange('change', $htmlOptions);
		return static::tag('textarea', $htmlOptions, isset($htmlOptions['encode']) && !$htmlOptions['encode'] ? $value : static::encode($value));
	}

	/**
	 * Generates a radio button.
	 * @param string $name the input name
	 * @param boolean $checked whether the radio button is checked
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the radio button is not checked. When set, a hidden field is rendered so that
	 * when the radio button is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated radio button
	 * @see clientChange
	 * @see inputField
	 */
	public static function radioButton($name, $checked = false, $htmlOptions = array())
	{
		if ($checked) {
			$htmlOptions['checked'] = 'checked';
		} else {
			unset($htmlOptions['checked']);
		}
		$value = isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
		static::clientChange('click', $htmlOptions);

		if (array_key_exists('uncheckValue', $htmlOptions)) {
			$uncheck = $htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		} else {
			$uncheck = null;
		}

		if ($uncheck !== null) {
			// add a hidden field so that if the radio button is not selected, it still submits a value
			if (isset($htmlOptions['id']) && $htmlOptions['id'] !== false) {
				$uncheckOptions = array('id' => static::ID_PREFIX . $htmlOptions['id']);
			} else {
				$uncheckOptions = array('id' => false);
			}
			$hidden = static::hiddenField($name, $uncheck, $uncheckOptions);
		} else {
			$hidden = '';
		}

		// add a hidden field so that if the radio button is not selected, it still submits a value
		return $hidden . static::inputField('radio', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a check box.
	 * @param string $name the input name
	 * @param boolean $checked whether the check box is checked
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the checkbox is not checked. When set, a hidden field is rendered so that
	 * when the checkbox is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated check box
	 * @see clientChange
	 * @see inputField
	 */
	public static function checkBox($name, $checked = false, $htmlOptions = array())
	{
		if ($checked) {
			$htmlOptions['checked'] = 'checked';
		} else {
			unset($htmlOptions['checked']);
		}
		$value = isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
		static::clientChange('click', $htmlOptions);

		if (array_key_exists('uncheckValue', $htmlOptions)) {
			$uncheck = $htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		} else {
			$uncheck = null;
		}

		if ($uncheck !== null) {
			// add a hidden field so that if the check box is not checked, it still submits a value
			if (isset($htmlOptions['id']) && $htmlOptions['id'] !== false) {
				$uncheckOptions = array('id' => static::ID_PREFIX . $htmlOptions['id']);
			} else {
				$uncheckOptions = array('id' => false);
			}
			$hidden = static::hiddenField($name, $uncheck, $uncheckOptions);
		} else {
			$hidden = '';
		}

		// add a hidden field so that if the check box is not checked, it still submits a value
		return $hidden . static::inputField('checkbox', $name, $value, $htmlOptions);
	}

	/**
	 * Generates a drop down list.
	 * @param string $name the input name
	 * @param string $select the selected value
	 * @param array $data data for generating the list options (value=>display).
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported specifically for dropdown list:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * Since 1.1.13, a special option named 'unselectValue' is available. It can be used to set the value
	 * that will be returned when no option is selected in multiple mode. When set, a hidden field is
	 * rendered so that if no option is selected in multiple mode, we can still obtain the posted
	 * unselect value. If 'unselectValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated drop down list
	 * @see clientChange
	 * @see inputField
	 * @see listData
	 */
	public static function dropDownList($name, $select, $data, $htmlOptions = array())
	{
		$htmlOptions['name'] = $name;

		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = static::getIdByName($name);
		} elseif ($htmlOptions['id'] === false) {
			unset($htmlOptions['id']);
		}

		static::clientChange('change', $htmlOptions);
		$options = "\n" . static::listOptions($select, $data, $htmlOptions);
		$hidden = '';

		if (isset($htmlOptions['multiple'])) {
			if (substr($htmlOptions['name'], -2) !== '[]') {
				$htmlOptions['name'] .= '[]';
			}

			if (isset($htmlOptions['unselectValue'])) {
				$hiddenOptions = isset($htmlOptions['id']) ? array('id' => static::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
				$hidden = static::hiddenField(substr($htmlOptions['name'], 0, -2), $htmlOptions['unselectValue'], $hiddenOptions);
				unset($htmlOptions['unselectValue']);
			}
		}
		// add a hidden field so that if the option is not selected, it still submits a value
		return $hidden . static::tag('select', $htmlOptions, $options);
	}

	/**
	 * Generates a list box.
	 * @param string $name the input name
	 * @param mixed $select the selected value(s). This can be either a string for single selection or an array for multiple selections.
	 * @param array $data data for generating the list options (value=>display)
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported specifically for list box:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * @return string the generated list box
	 * @see clientChange
	 * @see inputField
	 * @see listData
	 */
	public static function listBox($name, $select, $data, $htmlOptions = array())
	{
		if (!isset($htmlOptions['size'])) {
			$htmlOptions['size'] = 4;
		}
		if (isset($htmlOptions['multiple'])) {
			if (substr($name, -2) !== '[]') {
				$name .= '[]';
			}
		}
		return static::dropDownList($name, $select, $data, $htmlOptions);
	}

	/**
	 * Generates a check box list.
	 * A check box list allows multiple selection, like {@link listBox}.
	 * As a result, the corresponding POST value is an array.
	 * @param string $name name of the check box list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param mixed $select selection of the check boxes. This can be either a string
	 * for single selection or an array for multiple selections.
	 * @param array $data value-label pairs used to generate the check box list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions additional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * check box input tag while "{label}" be replaced by the corresponding check box label.</li>
	 * <li>separator: string, specifies the string that separates the generated check boxes.</li>
	 * <li>checkAll: string, specifies the label for the "check all" checkbox.
	 * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
	 * this checkbox will cause all checkboxes checked or unchecked.</li>
	 * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
	 * displayed at the end of the checkbox list. If this option is not set (default)
	 * or is false, the 'check all' checkbox will be displayed at the beginning of
	 * the checkbox list.</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the checkboxes enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * <li>baseID: string, specifies the base ID prefix to be used for checkboxes in the list.
	 * This option is available since version 1.1.13.</li>
	 * </ul>
	 * @return string the generated check box list
	 */
	public static function checkBoxList($name, $select, $data, $htmlOptions = array())
	{
		$template = isset($htmlOptions['template']) ? $htmlOptions['template'] : '{input} {label}';
		$separator = isset($htmlOptions['separator']) ? $htmlOptions['separator'] : "<br/>\n";
		$container = isset($htmlOptions['container']) ? $htmlOptions['container'] : 'span';
		unset($htmlOptions['template'], $htmlOptions['separator'], $htmlOptions['container']);

		if (substr($name, -2) !== '[]') {
			$name .= '[]';
		}

		if (isset($htmlOptions['checkAll'])) {
			$checkAllLabel = $htmlOptions['checkAll'];
			$checkAllLast = isset($htmlOptions['checkAllLast']) && $htmlOptions['checkAllLast'];
		}
		unset($htmlOptions['checkAll'], $htmlOptions['checkAllLast']);

		$labelOptions = isset($htmlOptions['labelOptions']) ? $htmlOptions['labelOptions'] : array();
		unset($htmlOptions['labelOptions']);

		$items = array();
		$baseID = isset($htmlOptions['baseID']) ? $htmlOptions['baseID'] : static::getIdByName($name);
		unset($htmlOptions['baseID']);
		$id = 0;
		$checkAll = true;

		foreach ($data as $value => $label) {
			$checked = !is_array($select) && !strcmp($value, $select) || is_array($select) && in_array($value, $select);
			$checkAll = $checkAll && $checked;
			$htmlOptions['value'] = $value;
			$htmlOptions['id'] = $baseID . '_' . $id++;
			$option = static::checkBox($name, $checked, $htmlOptions);
			$label = static::label($label, $htmlOptions['id'], $labelOptions);
			$items[] = strtr($template, array('{input}' => $option, '{label}' => $label));
		}

		if (isset($checkAllLabel)) {
			$htmlOptions['value'] = 1;
			$htmlOptions['id'] = $id = $baseID . '_all';
			$option = static::checkBox($id, $checkAll, $htmlOptions);
			$label = static::label($checkAllLabel, $id, $labelOptions);
			$item = strtr($template, array('{input}' => $option, '{label}' => $label));
			if ($checkAllLast) {
				$items[] = $item;
			} else {
				array_unshift($items, $item);
			}
			$name = strtr($name, array('[' => '\\[', ']' => '\\]'));
			$js = <<<EOD
jQuery('#$id').click(function() {
	jQuery("input[name='$name']").prop('checked', this.checked);
});
jQuery("input[name='$name']").click(function() {
	jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
});
jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
EOD;
			$cs = Yii::app()->getClientScript();
			$cs->registerCoreScript('jquery');
			$cs->registerScript($id, $js);
		}

		if (empty($container)) {
			return implode($separator, $items);
		} else {
			return static::tag($container, array('id' => $baseID), implode($separator, $items));
		}
	}

	/**
	 * Generates a radio button list.
	 * A radio button list is like a {@link checkBoxList check box list}, except that
	 * it only allows single selection.
	 * @param string $name name of the radio button list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param string $select selection of the radio buttons.
	 * @param array $data value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions additional HTML options. The options will be applied to
	 * each radio button input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each radio button is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons. Defaults to new line (<br/>).</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the radio buttons enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * <li>baseID: string, specifies the base ID prefix to be used for radio buttons in the list.
	 * This option is available since version 1.1.13.</li>
	 * </ul>
	 * @return string the generated radio button list
	 */
	public static function radioButtonList($name, $select, $data, $htmlOptions = array())
	{
		$template = isset($htmlOptions['template']) ? $htmlOptions['template'] : '{input} {label}';
		$separator = isset($htmlOptions['separator']) ? $htmlOptions['separator'] : "<br/>\n";
		$container = isset($htmlOptions['container']) ? $htmlOptions['container'] : 'span';
		unset($htmlOptions['template'], $htmlOptions['separator'], $htmlOptions['container']);

		$labelOptions = isset($htmlOptions['labelOptions']) ? $htmlOptions['labelOptions'] : array();
		unset($htmlOptions['labelOptions']);

		$items = array();
		$baseID = isset($htmlOptions['baseID']) ? $htmlOptions['baseID'] : static::getIdByName($name);
		unset($htmlOptions['baseID']);
		$id = 0;
		foreach ($data as $value => $label) {
			$checked = !strcmp($value, $select);
			$htmlOptions['value'] = $value;
			$htmlOptions['id'] = $baseID . '_' . $id++;
			$option = static::radioButton($name, $checked, $htmlOptions);
			$label = static::label($label, $htmlOptions['id'], $labelOptions);
			$items[] = strtr($template, array('{input}' => $option, '{label}' => $label));
		}
		if (empty($container)) {
			return implode($separator, $items);
		} else {
			return static::tag($container, array('id' => $baseID), implode($separator, $items));
		}
	}

	/**
	 * Normalizes the input parameter to be a valid URL.
	 *
	 * If the input parameter is an empty string, the currently requested URL will be returned.
	 *
	 * If the input parameter is a non-empty string, it is treated as a valid URL and will
	 * be returned without any change.
	 *
	 * If the input parameter is an array, it is treated as a controller route and a list of
	 * GET parameters, and the {@link CController::createUrl} method will be invoked to
	 * create a URL. In this case, the first array element refers to the controller route,
	 * and the rest key-value pairs refer to the additional GET parameters for the URL.
	 * For example, <code>array('post/list', 'page'=>3)</code> may be used to generate the URL
	 * <code>/index.php?r=post/list&page=3</code>.
	 *
	 * @param mixed $url the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 */
	public static function normalizeUrl($url)
	{
		if (is_array($url)) {
			if (isset($url[0])) {
				if (($c = Yii::app()->getController()) !== null) {
					$url = $c->createUrl($url[0], array_splice($url, 1));
				} else {
					$url = Yii::app()->createUrl($url[0], array_splice($url, 1));
				}
			} else {
				$url = '';
			}
		}
		return $url === '' ? Yii::app()->getRequest()->getUrl() : $url;
	}

	/**
	 * Generates an input HTML tag.
	 * This method generates an input HTML tag based on the given input name and value.
	 * @param string $type the input type (e.g. 'text', 'radio')
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes for the HTML tag (see {@link tag}).
	 * @return string the generated input tag
	 */
	protected static function inputField($type, $name, $value, $htmlOptions)
	{
		$htmlOptions['type'] = $type;
		$htmlOptions['value'] = $value;
		$htmlOptions['name'] = $name;
		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = static::getIdByName($name);
		} elseif ($htmlOptions['id'] === false) {
			unset($htmlOptions['id']);
		}
		return static::tag('input', $htmlOptions);
	}

	/**
	 * Generates the list options.
	 * @param mixed $selection the selected value(s). This can be either a string for single selection or an array for multiple selections.
	 * @param array $listData the option data (see {@link listData})
	 * @param array $htmlOptions additional HTML attributes. The following two special attributes are recognized:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * <li>key: string, specifies the name of key attribute of the selection object(s).
	 * This is used when the selection is represented in terms of objects. In this case,
	 * the property named by the key option of the objects will be treated as the actual selection value.
	 * This option defaults to 'primaryKey', meaning using the 'primaryKey' property value of the objects in the selection.
	 * This option has been available since version 1.1.3.</li>
	 * </ul>
	 * @return string the generated list options
	 */
	public static function listOptions($selection, $listData, &$htmlOptions)
	{
		$raw = isset($htmlOptions['encode']) && !$htmlOptions['encode'];
		$content = '';
		if (isset($htmlOptions['prompt'])) {
			$content .= '<option value="">' . strtr($htmlOptions['prompt'], array('<' => '&lt;', '>' => '&gt;')) . "</option>\n";
			unset($htmlOptions['prompt']);
		}
		if (isset($htmlOptions['empty'])) {
			if (!is_array($htmlOptions['empty'])) {
				$htmlOptions['empty'] = array('' => $htmlOptions['empty']);
			}
			foreach ($htmlOptions['empty'] as $value => $label) {
				$content .= '<option value="' . static::encode($value) . '">' . strtr($label, array('<' => '&lt;', '>' => '&gt;')) . "</option>\n";
			}
			unset($htmlOptions['empty']);
		}

		if (isset($htmlOptions['options'])) {
			$options = $htmlOptions['options'];
			unset($htmlOptions['options']);
		} else {
			$options = array();
		}

		$key = isset($htmlOptions['key']) ? $htmlOptions['key'] : 'primaryKey';
		if (is_array($selection)) {
			foreach ($selection as $i => $item) {
				if (is_object($item)) {
					$selection[$i] = $item->$key;
				}
			}
		} elseif (is_object($selection)) {
			$selection = $selection->$key;
		}

		foreach ($listData as $key => $value) {
			if (is_array($value)) {
				$content .= '<optgroup label="' . ($raw ? $key : static::encode($key)) . "\">\n";
				$dummy = array('options' => $options);
				if (isset($htmlOptions['encode'])) {
					$dummy['encode'] = $htmlOptions['encode'];
				}
				$content .= static::listOptions($selection, $value, $dummy);
				$content .= '</optgroup>' . "\n";
			} else {
				$attributes = array('value' => (string)$key, 'encode' => !$raw);
				if (!is_array($selection) && !strcmp($key, $selection) || is_array($selection) && in_array($key, $selection)) {
					$attributes['selected'] = 'selected';
				}
				if (isset($options[$key])) {
					$attributes = array_merge($attributes, $options[$key]);
				}
				$content .= static::tag('option', $attributes, $raw ? (string)$value : static::encode((string)$value)) . "\n";
			}
		}

		unset($htmlOptions['key']);

		return $content;
	}

	/**
	 * Renders the HTML tag attributes.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
	 * properly based on their corresponding boolean value.
	 * @param array $attributes attributes to be rendered
	 * @return string the rendering result
	 */
	public static function renderAttributes($attributes)
	{
		static $specialAttributes = array(
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

		if ($attributes === array()) {
			return '';
		}

		$html = '';
		if (isset($attributes['encode'])) {
			$raw = !$attributes['encode'];
			unset($attributes['encode']);
		} else {
			$raw = false;
		}

		foreach ($attributes as $name => $value) {
			if (isset($specialAttributes[$name])) {
				if ($value) {
					$html .= ' ' . $name;
					if (static::$renderSpecialAttributesValue) {
						$html .= '="' . $name . '"';
					}
				}
			} elseif ($value !== null) {
				$html .= ' ' . $name . '="' . ($raw ? $value : static::encode($value)) . '"';
			}
		}

		return $html;
	}
}
