<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Michelf\MarkdownExtra;

/**
 * AbstractMarkdown provides concrete implementation for [[Markdown]].
 *
 * Do not use AbstractMarkdown. Use [[Markdown]] instead.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
abstract class AbstractMarkdown
{
	/**
	 * @var MarkdownExtra
	 */
	protected static $markdown;

	/**
	 * Converts markdown into HTML
	 *
	 * @param string $content
	 * @param array $config
	 * @return string
	 */
	public static function process($content, $config = array())
	{
		if (static::$markdown === null) {
			static::$markdown = new MarkdownExtra();
		}
		foreach ($config as $name => $value) {
			static::$markdown->{$name} = $value;
		}
		return static::$markdown->transform($content);
	}
}
