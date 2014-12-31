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
 * echo SliderInput::widget([
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
 * echo SliderInput::widget([
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
    /**
     * @var array the HTML attributes for the container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $containerOptions = [];

    /**
     * @inheritDoc
     */
    protected $clientEventMap = [
        'change' => 'slidechange',
        'create' => 'slidecreate',
        'slide' => 'slide',
        'start' => 'slidestart',
        'stop' => 'slidestop',
    ];


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
            $this->clientOptions['value'] = $this->model{$this->attribute};
        } else {
            echo Html::hiddenInput($this->name, $this->value, $this->options);
            $this->clientOptions['value'] = $this->value;
        }

        if (!isset($this->clientEvents['slide'])) {
            $this->clientEvents['slide'] = 'function (event, ui) {
                $("#' . $this->options['id'] . '").val(ui.value);
            }';
        }

        $this->registerWidget('slider', $this->containerOptions['id']);
    }
}
