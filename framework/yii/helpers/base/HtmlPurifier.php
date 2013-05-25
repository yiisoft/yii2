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
	public static function process($content, $config = null)
	{
		$purifier=\HTMLPurifier::instance($config);
		$purifier->config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
		return $purifier->purify($content);
	}
}
