<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * MaskedInput generates a masked text input.
 *
 * MaskedInput is similar to [[Html::textInput()]] except that
 * an input mask will be used to force users to enter properly formatted data,
 * such as phone numbers, social security numbers.
 *
 * To use MaskedInput, you must set the [[mask]] property. The following example
 * shows how to use MaskedInput to collect phone numbers:
 *
 * ~~~
 * echo MaskedInput::widget([
 *     'name' => 'phone',
 *     'mask' => '999-999-9999',
 * ]);
 * ~~~
 *
 * The masked text field is implemented based on the [jQuery masked input plugin](http://digitalbush.com/projects/masked-input-plugin).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MaskedInput extends InputWidget
{
	/**
	 * @var string the input mask (e.g. '99/99/9999' for date input). The following characters are predefined:
	 *
	 * - `a`: represents an alpha character (A-Z, a-z)
	 * - `9`: represents a numeric character (0-9)
	 * - `*`: represents an alphanumeric character (A-Z, a-z, 0-9)
	 * - `?`: anything listed after '?' within the mask is considered optional user input
	 *
	 * Additional characters can be defined by specifying the [[charMap]] property.
	 */
	public $mask;
	/**
	 * @var array the mapping between mask characters and the corresponding patterns.
	 * For example, `['~' => '[+-]']` specifies that the '~' character expects '+' or '-' input.
	 * Defaults to null, meaning using the map as described in [[mask]].
	 */
	public $charMap;
	/**
	 * @var string the character prompting for user input. Defaults to underscore '_'.
	 */
	public $placeholder;
	/**
	 * @var string a JavaScript function callback that will be invoked when user finishes the input.
	 */
	public $completed;


	/**
	 * Initializes the widget.
	 * @throws InvalidConfigException if the "mask" property is not set.
	 */
	public function init()
	{
		parent::init();
		if (empty($this->mask)) {
			throw new InvalidConfigException('The "mask" property must be set.');
		}
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		if ($this->hasModel()) {
			echo Html::activeTextInput($this->model, $this->attribute, $this->options);
		} else {
			echo Html::textInput($this->name, $this->value, $this->options);
		}
		$this->registerClientScript();
	}

	/**
	 * Registers the needed JavaScript.
	 */
	public function registerClientScript()
	{
		$options = $this->getClientOptions();
		$options = empty($options) ? '' : ',' . Json::encode($options);
		$js = '';
		if (is_array($this->charMap) && !empty($this->charMap)) {
			$js .= 'jQuery.mask.definitions=' . Json::encode($this->charMap) . ";\n";
		}
		$id = $this->options['id'];
		$js .= "jQuery(\"#{$id}\").mask(\"{$this->mask}\"{$options});";
		$view = $this->getView();
		MaskedInputAsset::register($view);
		$view->registerJs($js);
	}

	/**
	 * @return array the options for the text field
	 */
	protected function getClientOptions()
	{
		$options = [];
		if ($this->placeholder !== null) {
			$options['placeholder'] = $this->placeholder;
		}

		if ($this->completed !== null) {
			if ($this->completed instanceof JsExpression) {
				$options['completed'] = $this->completed;
			} else {
				$options['completed'] = new JsExpression($this->completed);
			}
		}

		return $options;
	}
}
