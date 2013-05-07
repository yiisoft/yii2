<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

\Yii::setAlias('@Michelf', \Yii::getAlias('@yii').'/../vendor/michelf/php-markdown/Michelf');
use Michelf\MarkdownExtra;

/**
 * Markdown provides an ability to transform markdown into HTML.
 *
 * Basic usage is the following:
 *
 * ```php
 * $my_html = Markdown::process($my_text);
 * ```
 *
 * If you want to configure the parser:
 *
 * ```php
 * $my_html = Markdown::process($my_text, array(
 *     'fn_id_prefix' => 'footnote_',
 * ));
 * ```
 *
 * For more details please refer to [PHP Markdown library documentation](http://michelf.ca/projects/php-markdown/).
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Markdown
{
	/**
	 * @var MarkdownExtra
	 */
	protected static $markdown;

	public static function process($content, $config = array())
	{
		if (static::$markdown===null) {
			static::$markdown = new MarkdownExtra();
		}
		foreach ($config as $name => $value) {
			static::$markdown->{$name} = $value;
		}
		return static::$markdown->transform($content);
	}
}
