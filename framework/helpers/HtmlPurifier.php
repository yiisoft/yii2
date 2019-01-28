<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * HtmlPurifier 提供了一种从任何不良的代码中清除 HTML 的能力。
 *
 * 基本用法如下：
 *
 * ```php
 * echo HtmlPurifier::process($html);
 * ```
 *
 * 如果你想配置它：
 *
 * ```php
 * echo HtmlPurifier::process($html, [
 *     'Attr.EnableID' => true,
 * ]);
 * ```
 *
 * 获取更多详情请参阅 [HTMLPurifier documentation](http://htmlpurifier.org/)。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class HtmlPurifier extends BaseHtmlPurifier
{
}
