<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Json;

/**
 * \yii\jui\Widget is the base class for all jQuery UI widgets.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Widget extends \yii\base\Widget
{
    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array the options for the underlying jQuery UI widget.
     * Please refer to the corresponding jQuery UI widget Web page for possible options.
     * For example, [this page](http://api.jqueryui.com/accordion/) shows
     * how to use the "Accordion" widget and the supported options (e.g. "header").
     */
    public $clientOptions = [];
    /**
     * @var array the event handlers for the underlying jQuery UI widget.
     * Please refer to the corresponding jQuery UI widget Web page for possible events.
     * For example, [this page](http://api.jqueryui.com/accordion/) shows
     * how to use the "Accordion" widget and the supported events (e.g. "create").
     * Keys are the event names and values are javascript code that is passed to the `.on()` function
     * as the event handler.
     *
     * For example you could write the following in your widget configuration:
     *
     * ```php
     * 'clientEvents' => [
     *     'change' => 'function () { alert('event "change" occured.'); }'
     * ],
     * ```
     */
    public $clientEvents = [];

    /**
     * @var array event names mapped to what should be specified in .on(
     * If empty, it is assumed that event passed to clientEvents is prefixed with widget name.
     */
    protected $clientEventMap = [];


    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Registers a specific jQuery UI widget options
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget
     */
    protected function registerClientOptions($name, $id)
    {
        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
            $js = "jQuery('#$id').$name($options);";
            $this->getView()->registerJs($js);
        }
    }

    /**
     * Registers a specific jQuery UI widget events
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget
     */
    protected function registerClientEvents($name, $id)
    {
        if (!empty($this->clientEvents)) {
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                if (isset($this->clientEventMap[$event])) {
                    $eventName = $this->clientEventMap[$event];
                } else {
                    $eventName = strtolower($name . $event);
                }
                $js[] = "jQuery('#$id').on('$eventName', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

    /**
     * Registers a specific jQuery UI widget asset bundle, initializes it with client options and registers related events
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget. If null, it will use the `id` value of [[options]].
     */
    protected function registerWidget($name, $id = null)
    {
        if ($id === null) {
            $id = $this->options['id'];
        }
        JuiAsset::register($this->getView());
        $this->registerClientOptions($name, $id);
        $this->registerClientEvents($name, $id);
    }
}
