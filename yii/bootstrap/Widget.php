<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\base\View;


/**
 * Bootstrap is the base class for bootstrap widgets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Widget extends \yii\base\Widget
{

	/**
	 * @var bool whether to register the asset
	 */
	public static $responsive = true;

	/**
	 * @var array the HTML attributes for the widget container tag.
	 */
	public $options = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		// ensure bundle
		$this->registerBundle(static::$responsive);
	}

	/**
	 * Registers plugin events with the API.
	 * @param string $selector the CSS selector.
	 * @param string[] $events  the JavaScript event configuration (name=>handler).
	 * @return boolean whether the events were registered.
	 * @todo To be discussed
	 */
	protected function registerEvents($selector, $events = array())
	{
		if (empty($events))
			return;

		$script = '';
		foreach ($events as $name => $handler) {
			$handler = ($handler instanceof JsExpression)
				? $handler
				: new JsExpression($handler);

			$script .= ";jQuery('{$selector}').on('{$name}', {$handler});";
		}
		if (!empty($script))
			$this->view->registerJs($script);
	}

	/**
	 * Registers a specific Bootstrap plugin using the given selector and options.
	 *
	 * @param string $name the name of the javascript widget to initialize
	 * @param array $options the Javascript options for the plugin
	 */
	public function registerPlugin($name, $options = array())
	{
		$selector = '#' . ArrayHelper::getValue($this->options, 'id');
		$options = !empty($options) ? Json::encode($options) : '';
		$script = ";jQuery('{$selector}').{$name}({$options});";
		$this->view->registerJs($script);
	}

	/**
	 * Registers bootstrap bundle
	 * @param bool $responsive
	 */
	public function registerBundle($responsive = false)
	{
		$bundle = $responsive ? 'yii/bootstrap-responsive' : 'yii/bootstrap';
		$this->view->registerAssetBundle($bundle);
	}


	/**
	 * Adds a new class to options. If the class key does not exists, it will create one, if it exists it will append
	 * the value and also makes sure the uniqueness of them.
	 *
	 * @param string $class
	 * @return array
	 */
	protected function addClassName($class)
	{
		if (isset($this->options['class'])) {
			if (!is_array($this->options['class']))
				$this->options['class'] = explode(' ', $this->options['class']);
			$this->options['class'][] = $class;
			$this->options['class'] = array_unique($this->options['class']);
			$this->options['class'] = implode(' ', $this->options['class']);
		} else
			$this->options['class'] = $class;
		return $this->options;
	}

	/**
	 * Sets the default value for an item if not set.
	 * @param string $key the name of the item.
	 * @param mixed $value the default value.
	 * @return array
	 */
	protected function defaultOption($key, $value)
	{
		if (!isset($this->options[$key]))
			$this->options[$key] = $value;
		return $this->options;
	}
}