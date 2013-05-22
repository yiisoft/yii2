<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * TypeAhead renders a typehead bootstrap javascript component.
 *
 * For example,
 *
 * ```php
 * echo TypeAhead::widget(array(
 *     'form' => $form,
 *     'model' => $model,
 *     'attribute' => 'country',
 *     'pluginOptions' => array(
 *         'source' => array('USA', 'ESP'),
 *     ),
 * ));
 * ```
 *
 * The following example will use the name property instead
 *
 * ```php
 * echo TypeAhead::widget(array(
 *     'name'  => 'country',
 *     'pluginOptions' => array(
 *         'source' => array('USA', 'ESP'),
 *     ),
 * ));
 *```
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#typeahead
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class TypeAhead extends Widget
{
	/**
	 * @var \yii\base\Model the data model that this widget is associated with
	 */
	public $model;
	/**
	 * @var string the model attribute that this widget is associated with
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
	 * Renders the widget
	 */
	public function run()
	{
		$this->getView()->registerAssetBundle('yii/bootstrap/typeahead');
		echo $this->renderField();
		$this->registerPlugin('typeahead');
	}

	/**
	 * Renders the TypeAhead field. If [[model]] has been specified then it will render an active field.
	 * If [[model]] is null or not from an [[Model]] instance, then the field will be rendered according to
	 * the [[name]] attribute.
	 * @return string the rendering result
	 * @throws InvalidConfigException when none of the required attributes are set to render the textInput. That is,
	 * if [[model]] and [[attribute]] are not set, then [[name]] is required.
	 */
	public function renderField()
	{
		if ($this->model instanceof Model && $this->attribute !== null) {
			return Html::activeTextInput($this->model, $this->attribute, $this->options);
		} elseif ($this->name !== null) {
			return Html::textInput($this->name, $this->value, $this->options);
		} else {
			throw new InvalidConfigException('Either "name" or "model" and "attribute" properties must be specified.');
		}
	}
}
