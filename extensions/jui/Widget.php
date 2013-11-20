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
 * \yii\jui\Widget is the base class for all jQuery UI widgets.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Widget extends \yii\base\Widget
{
	/**
	 * @var string the jQuery UI theme. This refers to an asset bundle class
	 * representing the JUI theme. The default theme is the official "Smoothness" theme.
	 */
	public static $theme = 'yii\jui\ThemeAsset';
	/**
	 * @var array the HTML attributes for the widget container tag.
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
	 * Registers a specific jQuery UI widget and the related events
	 * @param string $name the name of the jQuery UI widget
	 * @param string $assetBundle the asset bundle for the widget
	 */
	protected function registerWidget($name, $assetBundle)
	{
		$view = $this->getView();
		/** @var \yii\web\AssetBundle $assetBundle */
		$assetBundle::register($view);
		/** @var \yii\web\AssetBundle $themeAsset */
		$themeAsset = static::$theme;
		$themeAsset::register($view);

		$id = $this->options['id'];
		if ($this->clientOptions !== false) {
			$options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
			$js = "jQuery('#$id').$name($options);";
			$view->registerJs($js);
		}

		if (!empty($this->clientEvents)) {
			$js = [];
			foreach ($this->clientEvents as $event => $handler) {
				$js[] = "jQuery('#$id').on('$name$event', $handler);";
			}
			$view->registerJs(implode("\n", $js));
		}
	}
}
