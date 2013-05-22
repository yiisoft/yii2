<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\caching\Cache;

/**
 * This command allows you to flush cache.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class CacheController extends Controller
{
	/**
	 * Lists the caches that can be flushed.
	 */
	public function actionIndex()
	{
		$caches = array();
		$components = Yii::$app->getComponents();
		foreach ($components as $name => $component) {
			if ($component instanceof Cache) {
				$caches[$name] = get_class($component);
			} elseif (is_array($component) && isset($component['class']) && strpos($component['class'], 'Cache') !== false) {
				$caches[$name] = $component['class'];
			}
		}
		if (!empty($caches)) {
			echo "The following caches can be flushed:\n\n";
			foreach ($caches as $name => $class) {
				echo " * $name: $class\n";
			}
		} else {
			echo "No cache is used.\n";
		}
	}

	/**
	 * Flushes cache.
	 * @param string $component Name of the cache application component to use.
	 *
	 * @throws \yii\console\Exception
	 */
	public function actionFlush($component = 'cache')
	{
		/** @var $cache Cache */
		$cache = Yii::$app->getComponent($component);
		if (!$cache || !$cache instanceof Cache) {
			throw new Exception('Application component "'.$component.'" is not defined or not a cache.');
		}

		if (!$cache->flush()) {
			throw new Exception('Unable to flush cache.');
		}

		echo "\nDone.\n";
	}
}
