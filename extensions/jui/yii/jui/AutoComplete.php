<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use Yii;
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
class AutoComplete extends InputWidget
{
	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderWidget();
		$this->registerWidget('autocomplete');
	}

	/**
	 * Renders the AutoComplete widget.
	 * @return string the rendering result.
	 */
	public function renderWidget()
	{
		if ($this->hasModel()) {
			return Html::activeTextInput($this->model, $this->attribute, $this->options);
		} else {
			return Html::textInput($this->name, $this->value, $this->options);
		}
	}
}
