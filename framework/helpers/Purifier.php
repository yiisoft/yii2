<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * Purifier provides an ability to clean up HTML from any harmful code.
 *
 * Basic usage is the following:
 *
 * ```php
 * $my_html = Purifier::process($my_text);
 * ```
 *
 * If you want to configure it:
 *
 * ```php
 * $my_html = Purifier::process($my_text, array(
 *     'Attr.EnableID' => true,
 * ));
 * ```
 *
 * For more details please refer to HTMLPurifier documentation](http://htmlpurifier.org/).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Purifier extends base\Purifier
{
}
