<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\InvalidArgumentException;

/**
 * BaseMarkdown 为 [[Markdown]] 提供了具体的实现。
 *
 * 不要使用类 BaseMarkdown。使用 [[Markdown]] 来替代。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseMarkdown
{
    /**
     * @var array 将标记风格名称映射到相应解析器类配置的映射。
     */
    public static $flavors = [
        'original' => [
            'class' => 'cebe\markdown\Markdown',
            'html5' => true,
        ],
        'gfm' => [
            'class' => 'cebe\markdown\GithubMarkdown',
            'html5' => true,
        ],
        'gfm-comment' => [
            'class' => 'cebe\markdown\GithubMarkdown',
            'html5' => true,
            'enableNewlines' => true,
        ],
        'extra' => [
            'class' => 'cebe\markdown\MarkdownExtra',
            'html5' => true,
        ],
    ];
    /**
     * @var 在没有显式指定时使用的 markdown 风格字符串。
     * 默认风格设置为 `original`。
     * @see $flavors
     */
    public static $defaultFlavor = 'original';


    /**
     * 将 markdown 转变成 HTML。
     *
     * @param string $markdown 要解析的 markdown 文本
     * @param string $flavor 关于 markdown 使用的风格。请参考 [[$flavors]] 可用的值。
     * 默认为 [[$defaultFlavor]]，如果没有设置的情况下。
     * @return string 解析的 HTML 输出
     * @throws InvalidArgumentException 当指定的风格不存在时发生异常。
     */
    public static function process($markdown, $flavor = null)
    {
        $parser = static::getParser($flavor);

        return $parser->parse($markdown);
    }

    /**
     * 将 markdown 转换为 HTML 但是只解析内联元素。
     *
     * 这对于解析小注释或描述行非常有用。
     *
     * @param string $markdown 要解析的 markdown 文本
     * @param string $flavor 关于 markdown 使用的风格。请参考 [[$flavors]] 可用的值。
     * 默认为 [[$defaultFlavor]]，如果没有设置的情况下。
     * @return string 解析的 HTML 输出
     * @throws InvalidArgumentException 当指定的风格不存在时发生异常。
     */
    public static function processParagraph($markdown, $flavor = null)
    {
        $parser = static::getParser($flavor);

        return $parser->parseParagraph($markdown);
    }

    /**
     * @param string $flavor 关于 markdown 使用的风格。请参考 [[$flavors]] 可用的值。
     * 默认为 [[$defaultFlavor]]，如果没有设置的情况下。
     * @return \cebe\markdown\Parser
     * @throws InvalidArgumentException 当指定的风格不存在时发生异常。
     */
    protected static function getParser($flavor)
    {
        if ($flavor === null) {
            $flavor = static::$defaultFlavor;
        }
        /* @var $parser \cebe\markdown\Markdown */
        if (!isset(static::$flavors[$flavor])) {
            throw new InvalidArgumentException("Markdown flavor '$flavor' is not defined.'");
        } elseif (!is_object($config = static::$flavors[$flavor])) {
            static::$flavors[$flavor] = Yii::createObject($config);
        }

        return static::$flavors[$flavor];
    }
}
