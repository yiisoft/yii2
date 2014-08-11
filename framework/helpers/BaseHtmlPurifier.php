<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * BaseHtmlPurifier provides concrete implementation for [[HtmlPurifier]].
 *
 * Do not use BaseHtmlPurifier. Use [[HtmlPurifier]] instead.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseHtmlPurifier
{
    /**
     * Passes markup through HTMLPurifier making it safe to output to end user
     * 
     * Anonymous function config example,
     * 
     * ~~~
     * // Allow the HTML5 data attribute `data-type` on `img` elements.
     * HtmlPurifier::process($content, function($config) {
     *  $def = $config->getHTMLDefinition(true);
     *  $def->addAttribute('img', 'data-type', 'Text');
     * })
     * ~~~
     *
     * @param string $content The HTML content to purify
     * @param array|\Closure|null $config if not specified or null the default config will be used.
     * Use an array or an anonymous function to provide configuration options. The anonymous function signature should be:
     * `function($config)` where `$config` will be an instance of HTMLPurifier_Config.
     * @return string
     */
    public static function process($content, $config = null)
    {
        $configInstance = \HTMLPurifier_Config::create($config);
        $configInstance->autoFinalize = false;
        $purifier=\HTMLPurifier::instance($configInstance);
        $purifier->config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
        
        if ($config instanceof \Closure) {
            $config($configInstance);
        }

        return $purifier->purify($content);
    }
}
