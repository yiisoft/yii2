<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * SliderInput renders a slider jQuery UI widget that writes its value into hidden input.
 *
 * For example,
 *
 * ```
 * echo Slider::widget([
 *     'model' => $model,
 *     'attrbute' => 'amount',
 *     'clientOptions' => [
 *         'min' => 1,
 *         'max' => 10,
 *     ],
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 *
 * ```
 * echo Slider::widget([
 *     'name' => 'amount',
 *     'clientOptions' => [
 *         'min' => 1,
 *         'max' => 10,
 *     ],
 * ]);
 * ```
 *
 * @see http://api.jqueryui.com/slider/
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class SliderInput extends InputWidget
{
	protected $clientEventsMap = [
		'change' => 'slidechange',
		'create' => 'slidecreate',
		'slide' => 'slide',
		'start' => 'slidestart',
		'stop' => 'slidestop',
	];

	/**
	 * Executes the widget.
	 */
	public function run()
	{
		echo Html::tag('div', '', $this->options);

		$inputId = $this->id.'-input';
		$inputOptions = $this->options;
		$inputOptions['id'] = $inputId;
		if ($this->hasModel()) {
			echo Html::activeHiddenInput($this->model, $this->attribute, $inputOptions);
		} else {
			echo Html::hiddenInput($this->name, $this->value, $inputOptions);
		}

		if (!isset($this->clientEvents['slide'])) {
			$this->clientEvents['slide'] = 'function(event, ui) {
				$("#'.$inputId.'").val(ui.value);
			}';
		}

		$this->registerWidget('slider', SliderAsset::className());
		$this->getView()->registerJs('$("#'.$inputId.'").val($("#'.$this->id.'").slider("value"));');
	}
}