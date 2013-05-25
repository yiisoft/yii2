<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * AutoComplete renders an autocomplete jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo AutoComplete::widget(array(
 *     'model' => $model,
 *     'attribute' => 'country',
 *     'clientOptions' => array(
 *         'source' => array('USA', 'RUS'),
 *     ),
 * ));
 * ```
 *
 * The following example will use the name property instead:
 *
 * ```php
 * echo AutoComplete::widget(array(
 *     'name'  => 'country',
 *     'clientOptions' => array(
 *         'source' => array('USA', 'RUS'),
 *     ),
 * ));
 *```
 *
 * @see http://api.jqueryui.com/autocomplete/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class AutoComplete extends Widget
{
	/**
	 * @var \yii\base\Model the data model that this widget is associated with.
	 */
	public $model;
	/**
	 * @var string the model attribute that this widget is associated with.
	 */
	public $attribute;
	/**
	 * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
	 */
	public $name;
	/**
	 * @var string the input value.
	 */
	public $value;


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderField();
		$this->registerWidget('autocomplete');
	}

	/**
	 * Renders the AutoComplete field. If [[model]] has been specified then it will render an active field.
	 * If [[model]] is null or not from an [[Model]] instance, then the field will be rendered according to
	 * the [[name]] attribute.
	 * @return string the rendering result.
	 * @throws InvalidConfigException when none of the required attributes are set to render the textInput.
	 * That is, if [[model]] and [[attribute]] are not set, then [[name]] is required.
	 */
	public function renderField()
	{
		if ($this->model instanceof Model && $this->attribute !== null) {
			return Html::activeTextInput($this->model, $this->attribute, $this->options);
		} elseif ($this->name !== null) {
			return Html::textInput($this->name, $this->value, $this->options);
		} else {
			throw new InvalidConfigException("Either 'name' or 'model' and 'attribute' properties must be specified.");
		}
	}
}
