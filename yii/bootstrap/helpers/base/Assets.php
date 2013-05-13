<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;

use Yii;
use yii\helpers\Json;
use yii\web\JsExpression;


/**
 * Assets provides methods to register bootstrap assets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Assets
{
	public static function registerBundle($responsive = false)
	{
		$bundle = $responsive ? 'yii/bootstrap' : 'yii/bootstrap-responsive';

		Yii::$app->getView()->registerAssetBundle($bundle);
	}

	/**
	 * Registers plugin events with the API.
	 *
	 * @param string $selector the CSS selector.
	 * @param string[] $events  the JavaScript event configuration (name=>handler).
	 * @param int $position the position of the JavaScript code.
	 * @return boolean whether the events were registered.
	 */
	public static function registerEvents($selector, $events = array(), $position = View::POS_END)
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
			Yii::$app->getView()>registerJs($script, array('position' => $position), static::getUniqueScriptId());
	}

	/**
	 * Registers a specific Bootstrap plugin using the given selector and options.
	 *
	 * @param string $selector the CSS selector.
	 * @param string $name the name of the plugin
	 * @param array $options the JavaScript options for the plugin.
	 * @param int $position the position of the JavaScript code.
	 */
	public static function registerPlugin($selector, $name, $options = array(), $position = View::POS_END)
	{
		$options = !empty($options) ? Json::encode($options) : '';
		$script = ";jQuery(document).ready(function (){jQuery('{$selector}').{$name}({$options});});";
		Yii::$app->getView()->registerJs($script, array('position'=>$position));
	}

	/**
	 * Generates a "somewhat" random id string.
	 * @return string the id.
	 */
	public static function getUniqueScriptId()
	{
		return uniqid(time() . '#', true);
	}
}