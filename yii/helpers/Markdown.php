<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * Markdown provides an ability to transform markdown into HTML.
 *
 * Basic usage is the following:
 *
 * ```php
 * $myHtml = Markdown::process($myText);
 * ```
 *
 * If you want to configure the parser:
 *
 * ```php
 * $myHtml = Markdown::process($myText, array(
 *     'fn_id_prefix' => 'footnote_',
 * ));
 * ```
 *
 * For more details please refer to [PHP Markdown library documentation](http://michelf.ca/projects/php-markdown/).
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Markdown extends base\Markdown
{
}
