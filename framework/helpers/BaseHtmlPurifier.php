<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * BaseHtmlPurifier 为 [[HtmlPurifier]] 提供了具体的实现方法。
 *
 * 不要使用 BaseHtmlPurifier 类。使用 [[HtmlPurifier]] 类来代替。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseHtmlPurifier
{
    /**
     * 通过 HTMLPurifier 来传递标记使其安全传输给最终的用户。
     *
     * @param string $content 需要过滤的 HTML 内容
     * @param array|\Closure|null $config 为 HtmlPurifier 提供使用的配置。
     * 如果未指定或为 `null` 则使用默认配置。
     * 这里你将可以使用数组或匿名函数提供配置选项：
     *
     * - 以数组的形式将传递给 `HTMLPurifier_Config::create()` 方法。
     * - 一种匿名函数将在创建配置后调用。
     *   签名应是这样的：`function($config)` 中的 `$config`
     *   将是 `HTMLPurifier_Config` 的一个实例。
     *
     *   下面是这样一个函数的使用示例：
     *
     *   ```php
     *   // Allow the HTML5 data attribute `data-type` on `img` elements.
     *   $content = HtmlPurifier::process($content, function ($config) {
     *     $config->getHTMLDefinition(true)
     *            ->addAttribute('img', 'data-type', 'Text');
     *   });
     * ```
     *
     * @return string 需要过滤的 HTML 内容。
     */
    public static function process($content, $config = null)
    {
        $configInstance = \HTMLPurifier_Config::create($config instanceof \Closure ? null : $config);
        $configInstance->autoFinalize = false;
        $purifier = \HTMLPurifier::instance($configInstance);
        $purifier->config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
        $purifier->config->set('Cache.SerializerPermissions', 0775);

        static::configure($configInstance);
        if ($config instanceof \Closure) {
            call_user_func($config, $configInstance);
        }

        return $purifier->purify($content);
    }

    /**
     * 允许这个扩展的 HtmlPurifier 类去设置默认的配置选项。
     * @param \HTMLPurifier_Config $config
     * @since 2.0.3
     */
    protected static function configure($config)
    {
    }
}
