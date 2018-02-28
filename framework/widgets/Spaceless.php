<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Widget;

/**
 * Spaceless widget removes whitespace characters between HTML tags. Whitespaces within HTML tags
 * or in a plain text are always left untouched.
 *
 * Usage example:
 *
 * ```php
 * <body>
 *     <?php Spaceless::begin(); ?>
 *         <div class="nav-bar">
 *             <!-- tags -->
 *         </div>
 *         <div class="content">
 *             <!-- tags -->
 *         </div>
 *     <?php Spaceless::end(); ?>
 * </body>
 * ```
 *
 * This example will generate the following HTML:
 *
 * ```html
 * <body>
 *     <div class="nav-bar"><!-- tags --></div><div class="content"><!-- tags --></div></body>
 * ```
 *
 * This method is not designed for content compression (you should use `gzip` output compression to
 * achieve it). Main intention is to strip out extra whitespace characters between HTML tags in order
 * to avoid browser rendering quirks in some circumstances (e.g. newlines between inline-block elements).
 *
 * Note, never use this method with `pre` or `textarea` tags. It's not that trivial to deal with such tags
 * as it may seem at first sight. For this case you should consider using
 * [HTML Tidy Project](http://tidy.sourceforge.net/) instead.
 *
 * @see http://tidy.sourceforge.net/
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class Spaceless extends Widget
{
    /**
     * Starts capturing an output to be cleaned from whitespace characters between HTML tags.
     */
    public function init()
    {
        parent::init();
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Marks the end of content to be cleaned from whitespace characters between HTML tags.
     * Stops capturing an output and returns cleaned result.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        return trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }
}
