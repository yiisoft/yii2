<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * TypeAhead renders a typehead bootstrap javascript component.
 *
 * For example,
 *
 * ```php
 * echo TypeAhead::widget(array(
 * 	'form' => $form,
 *	'model' => $model,
 *		'attribute' => 'country',
 * 	'pluginOptions' => array(
 *		'source' => array('USA', 'ESP'),
 * 	),
 * ));
 * ```
 *
 * The following example will use the name property instead
 *
 * ```php
 * echo TypeAhead::widget(array(
 *	'name'  => 'country',
 * 		'pluginOptions' => array(
 *			'source' => array('USA', 'ESP'),
 * 	),
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
	 * @var ActiveForm the form that the TypeAhead field is associated with. If no form is associated with the widget
	 * then the id will be used instead
	 */
	public $form;
	/**
	 * @var \yii\base\Model the data model that this field is associated with
	 */
	public $model;
	/**
	 * @var string the model attribute that this field is associated with
	 */
	public $attribute;

	/**
	 * @var string the input name. This must be set if [[form]] is not set.
	 */
	public $name;

	/**
	 * Initializes the widget.
	 * Renders the input field.
	 */
	public function init()
	{
		parent::init();
		echo "\n" . $this->renderField() . "\n";
	}

	/**
	 * Registers the plugin.
	 */
	public function run()
	{
		$this->registerPlugin('typeahead');
	}

	/**
	 * Renders the TypeAhead field. If [[form]] has been specified then it will render an active field.
	 * Please, note that function will only check whether the form has been set, model and attributes will not.
	 * If [[form]] is null not from an [[ActiveForm]] instance, then the field will be rendered according to
	 * the `name` key setting of [[options]] array attribute.
	 * @return string the rendering result
	 * @throws InvalidParamException when none of the required attributes are set to render the textInput. That is,
	 * if [[form]], [[model]] and [[attribute]] are not set, then [[name]] is required.
	 */
	public function renderField()
	{
		if ($this->form instanceof ActiveForm) {

			$this->options['id'] = $this->id = Html::getInputId($this->model, $this->attribute);

			return Yii::createObject(
				array(
					'class' => 'yii\widgets\ActiveField',
					'model' => $this->model,
					'attribute' => $this->attribute,
					'form' => $this->form,
				)
			)->textInput();
		}

		if ($this->name === null)
		{
			throw new InvalidParamException(
				get_class($this) . ' must specify "form", "model" and "attribute" or "name" property values'
			);
		}

		return Html::textInput($this->name, '', $this->options);
	}
}
