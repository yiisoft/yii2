<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\base\View;
use yii\helpers\Json;


/**
 * \yii\bootstrap\Widget is the base class for all bootstrap widgets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends \yii\base\Widget
{
	/**
	 * @var boolean whether to use the responsive version of Bootstrap.
	 */
	public static $responsive = true;
	/**
	 * @var boolean whether to use the transition effects.
	 */
	public static $transition = true;
	/**
	 * @var array the HTML attributes for the widget container tag.
	 */
	public $options = array();
	/**
	 * @var array the options for the underlying Bootstrap JS plugin.
	 * Please refer to the corresponding Bootstrap plugin Web page for possible options.
	 * For example, [this page](http://twitter.github.io/bootstrap/javascript.html#modals) shows
	 * how to use the "Modal" plugin and the supported options (e.g. "remote").
	 */
	public $pluginOptions = array();
	/**
	 * @var array the event handlers for the underlying Bootstrap JS plugin.
	 * Please refer to the corresponding Bootstrap plugin Web page for possible events.
	 * For example, [this page](http://twitter.github.io/bootstrap/javascript.html#modals) shows
	 * how to use the "Modal" plugin and the supported events (e.g. "shown").
	 */
	public $pluginEvents = array();


	/**
	 * Initializes the widget.
	 * This method will register the bootstrap asset bundle. If you override this method,
	 * make sure you call the parent implementation first.
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}
	}

	/**
	 * Registers a specific Bootstrap plugin and the related events
	 * @param string $name the name of the Bootstrap plugin
	 */
	protected function registerPlugin($name)
	{
		$id = $this->options['id'];
		$view = $this->getView();
		$view->registerAssetBundle(static::$responsive ? 'yii/bootstrap/responsive' : 'yii/bootstrap');

		if (static::$transition) {
			$view->registerAssetBundle('yii/bootstrap/transition');
		}

		if ($this->pluginOptions !== false) {
			$options = empty($this->pluginOptions) ? '' : Json::encode($this->pluginOptions);
			$js = "jQuery('#$id').$name($options);";
			$view->registerJs($js);
		}

		if (!empty($this->pluginEvents)) {
			$js = array();
			foreach ($this->pluginEvents as $event => $handler) {
				$js[] = "jQuery('#$id').on('$event', $handler);";
			}
			$view->registerJs(implode("\n", $js));
		}
	}

	/**
	 * Adds a CSS class to the specified options.
	 * This method will ensure that the CSS class is unique and the "class" option is properly formatted.
	 * @param array $options the options to be modified.
	 * @param string $class the CSS class to be added
	 */
	protected function addCssClass(&$options, $class)
	{
		if (isset($options['class'])) {
			$classes = preg_split('/\s+/', $options['class'] . ' ' . $class, -1, PREG_SPLIT_NO_EMPTY);
			$options['class'] = implode(' ', array_unique($classes));
		} else {
			$options['class'] = $class;
		}
	}
}
