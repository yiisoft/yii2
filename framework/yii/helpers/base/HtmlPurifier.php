<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */
namespace yii\helpers\base;

/**
 * HtmlPurifier is the concrete implementation of the [[yii\helpers\HtmlPurifier]] class.
 *
 * You should use [[yii\helpers\HtmlPurifier]] instead of this class in your application.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class HtmlPurifier
{
	/**
	 * Passes markup through HTMLPurifier making it safe to output to end user
	 *
	 * @param string $content
	 * @param array|null $config
	 * @return string
	 */
	public static function process($content, $config = null)
	{
		$configInstance = \HTMLPurifier_Config::create($config);
		$configInstance->autoFinalize = false;
		$purifier=\HTMLPurifier::instance($configInstance);
		$purifier->config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
		return $purifier->purify($content);
	}
}
