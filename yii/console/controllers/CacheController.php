<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

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
	public function actionIndex()
	{
		$this->forward('help/index', array('-args' => array('cache/flush')));
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
		$cache = \Yii::$app->getComponent($component);
		if (!$cache || !$cache instanceof Cache) {
			throw new Exception('Application component "'.$component.'" is not defined or not a cache.');
		}

		if (!$cache->flush()) {
			throw new Exception('Unable to flush cache.');
		}

		echo "\nDone.\n";
	}
}
