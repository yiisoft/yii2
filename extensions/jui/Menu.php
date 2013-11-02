<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use Yii;
use yii\helpers\Json;

/**
 * Menu renders a menu jQuery UI widget.
 *
 * @see http://api.jqueryui.com/menu/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Menu extends \yii\widgets\Menu
{
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
	 */
	public $clientEvents = [];


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
	 * Renders the widget.
	 */
	public function run()
	{
		parent::run();

		$view = $this->getView();
		MenuAsset::register($view);
		/** @var \yii\web\AssetBundle $themeAsset */
		$themeAsset = Widget::$theme;
		$themeAsset::register($view);

		$id = $this->options['id'];
		if ($this->clientOptions !== false) {
			$options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
			$js = "jQuery('#$id').menu($options);";
			$view->registerJs($js);
		}

		if (!empty($this->clientEvents)) {
			$js = [];
			foreach ($this->clientEvents as $event => $handler) {
				$js[] = "jQuery('#$id').on('menu$event', $handler);";
			}
			$view->registerJs(implode("\n", $js));
		}
	}
}
