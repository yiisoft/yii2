<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * Markdown 类提供了将 markdown 转换为 HTML 的功能。
 *
 * 基本用法如下：
 *
 * ```php
 * $myHtml = Markdown::process($myText); // use original markdown flavor
 * $myHtml = Markdown::process($myText, 'gfm'); // use github flavored markdown
 * $myHtml = Markdown::process($myText, 'extra'); // use markdown extra
 * ```
 *
 * 您可以使用 [[$flavors]] 属性配置多种风格。
 *
 * 获取更多详情信息请参阅 [Markdown library documentation](https://github.com/cebe/markdown#readme)。
 *
 * > 注意：Markdown 库可以与 PHPDoc 注释一起使用，如果你同时使用它们的话。
 * > PHP `opcache` 确保 [it does not strip comments](http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments)。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Markdown extends BaseMarkdown
{
}
