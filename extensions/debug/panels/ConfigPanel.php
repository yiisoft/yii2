<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;

/**
 * Debugger panel that collects and displays application configuration and environment.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ConfigPanel extends Panel
{
	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Configuration';
	}

	/**
	 * @inheritdoc
	 */
	public function getSummary()
	{
		return Yii::$app->view->render('panels/config/summary', ['panel' => $this]);
	}

	/**
	 * @inheritdoc
	 */
	public function getDetail()
	{
		return Yii::$app->view->render('panels/config/detail', ['panel' => $this]);
	}

	/**
	 * Returns data about extensions
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		$data = [];
		foreach ($this->data['extensions'] as $extension) {
			$data[$extension['name']] = $extension['version'];
		}
		return $data;
	}

	/**
	 * Returns the BODY contents of the phpinfo() output
	 *
	 * @return array
	 */
	public function getPhpInfo ()
	{
		ob_start();
		phpinfo();
		$pinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
		$phpinfo = str_replace('<table ', '<table class="table table-condensed table-bordered table-striped table-hover"', $phpinfo);

		return $phpinfo;
	}

	/**
	 * @inheritdoc
	 */
	public function save()
	{
		return [
			'phpVersion' => PHP_VERSION,
			'yiiVersion' => Yii::getVersion(),
			'application' => [
				'yii' => Yii::getVersion(),
				'name' => Yii::$app->name,
				'env' => YII_ENV,
				'debug' => YII_DEBUG,
			],
			'php' => [
				'version' => PHP_VERSION,
				'xdebug' => extension_loaded('xdebug'),
				'apc' => extension_loaded('apc'),
				'memcache' => extension_loaded('memcache'),
			],
			'extensions' => Yii::$app->extensions,
		];
	}
}
