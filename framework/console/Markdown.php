<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use cebe\markdown\block\FencedCodeTrait;
use cebe\markdown\inline\CodeTrait;
use cebe\markdown\inline\EmphStrongTrait;
use cebe\markdown\inline\StrikeoutTrait;
use yii\helpers\Console;

/**
 * 一个 Markdown 解析器，它增强了用于在控制台环境中读取的 markdown。
 *
 * 基于 [cebe/markdown](https://github.com/cebe/markdown)。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Markdown extends \cebe\markdown\Parser
{
    use FencedCodeTrait;
    use CodeTrait;
    use EmphStrongTrait;
    use StrikeoutTrait;

    /**
     * @var array 这些是 "escapeable" 字符。当使用其中一个带反斜杠的前缀时，
     * 字符将不带反斜杠输出，并且不会被解释为
     * markdown。
     */
    protected $escapeCharacters = [
        '\\', // backslash
        '`', // backtick
        '*', // asterisk
        '_', // underscore
        '~', // tilde
    ];


    /**
     * 渲染代码块。
     *
     * @param array $block
     * @return string
     */
    protected function renderCode($block)
    {
        return Console::ansiFormat($block['content'], [Console::NEGATIVE]) . "\n\n";
    }

    /**
     * 渲染段落块。
     *
     * @param string $block
     * @return string
     */
    protected function renderParagraph($block)
    {
        return rtrim($this->renderAbsy($block['content'])) . "\n\n";
    }

    /**
     * 渲染内联代码范围 `` ` ``。
     * @param array $element
     * @return string
     */
    protected function renderInlineCode($element)
    {
        return Console::ansiFormat($element[1], [Console::UNDERLINE]);
    }

    /**
     * 渲染强调元素。
     * @param array $element
     * @return string
     */
    protected function renderEmph($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::ITALIC]);
    }

    /**
     * 渲染增强元素。
     * @param array $element
     * @return string
     */
    protected function renderStrong($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::BOLD]);
    }

    /**
     * 渲染删除功能。
     * @param array $element
     * @return string
     */
    protected function renderStrike($element)
    {
        return Console::ansiFormat($this->parseInline($this->renderAbsy($element[1])), [Console::CROSSED_OUT]);
    }
}
