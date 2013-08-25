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
 * Slider renders a slider jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Slider::widget(array(
 *     'model' => $model,
 *     'attribute' => 'amount',
 *     'clientOptions' => array(
 *         'min' => 1,
 *         'max' => 10,
 *     ),
 * ));
 * ```
 *
 * The following example will use the name property instead:
 *
 * ```php
 * echo Slider::widget(array(
 *     'name'  => 'amount',
 *     'clientOptions' => array(
 *         'min' => 1,
 *         'max' => 10,
 *     ),
 * ));
 *```
 *
 * @see http://api.jqueryui.com/slider/
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Slider extends InputWidget
{
	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderWidget();
		$this->registerWidget('slider', SliderAsset::className());
	}

	/**
	 * Renders the Slider widget.
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
