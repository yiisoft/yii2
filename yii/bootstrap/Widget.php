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
	 * @var string the widget name (ie. modal, typeahead, tab)
	 */
	protected $name;

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
	 * @param int $position the position of the JavaScript code.
	 * @return boolean whether the events were registered.
	 * @todo To be discussed
	 */
	protected function registerEvents($selector, $events = array(), $position = View::POS_END)
	{
		if (empty($events))
			return;

		$script = '';
		foreach ($events as $name => $handler) {
			$handler = ($handler instanceof JsExpression)
				? $handler
				: new JsExpression($handler);

			$script .= ";jQuery(document).ready(function (){jQuery('{$selector}').on('{$name}', {$handler});});";
		}
		if (!empty($script))
			$this->view->registerJs($script, array('position' => $position));
	}

	/**
	 * Registers a specific Bootstrap plugin using the given selector and options.
	 *
	 * @param string $selector the CSS selector
	 * @param array $options the Javascript options for the plugin
	 * @param int $position the position of the JavaScript code
	 */
	public function registerPlugin($selector, $options = array(), $position = View::POS_END)
	{
		$options = !empty($options) ? Json::encode($options) : '';
		$script = ";jQuery(document).ready(function (){jQuery('{$selector}').{$this->name}({$options});});";
		$this->view->registerJs($script, array('position' => $position));
	}

	/**
	 * Registers bootstrap bundle
	 * @param bool $responsive
	 */
	public function registerBundle($responsive = false)
	{
		$bundle = $responsive ? 'yii/bootstrap' : 'yii/bootstrap-responsive';

		$this->view->registerAssetBundle($bundle);
	}


	/**
	 * Adds a new option. If the key does not exists, it will create one, if it exists it will append the value
	 * and also makes sure the uniqueness of them.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $glue
	 * @return array
	 */
	protected function addOption($key, $value, $glue = ' ')
	{
		if (isset($this->options[$key])) {
			if (!is_array($this->options[$key]))
				$this->options[$key] = explode($glue, $this->options[$key]);
			$this->options[$key][] = $value;
			$this->options[$key] = array_unique($this->options[$key]);
			$this->options[$key] = implode($glue, $this->options[$key]);
		} else
			$this->options[$key] = $value;
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