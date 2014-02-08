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
 *     'attribute' => 'amount',
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
	protected $clientEventMap = [
		'change' => 'slidechange',
		'create' => 'slidecreate',
		'slide' => 'slide',
		'start' => 'slidestart',
		'stop' => 'slidestop',
	];
	/**
	 * @var array the HTML attributes for the container tag.
	 */
	public $containerOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->containerOptions['id'])) {
			$this->containerOptions['id'] = $this->options['id'] . '-container';
		}
	}

	/**
	 * Executes the widget.
	 */
	public function run()
	{
		echo Html::tag('div', '', $this->containerOptions);

		if ($this->hasModel()) {
			echo Html::activeHiddenInput($this->model, $this->attribute, $this->options);
		} else {
			echo Html::hiddenInput($this->name, $this->value, $this->options);
		}

		if (!isset($this->clientEvents['slide'])) {
			$this->clientEvents['slide'] = 'function(event, ui) {
				$("#' . $this->options['id'] . '").val(ui.value);
			}';
		}

		$this->registerWidget('slider', SliderAsset::className(), $this->containerOptions['id']);
		$this->getView()->registerJs('$("#' . $this->options['id'] . '").val($("#' . $this->id . '").slider("value"));');
	}
}
