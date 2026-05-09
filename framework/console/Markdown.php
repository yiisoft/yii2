<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\console;

use cebe\markdown\block\FencedCodeTrait;
use cebe\markdown\inline\CodeTrait;
use cebe\markdown\inline\EmphStrongTrait;
use cebe\markdown\inline\StrikeoutTrait;
use yii\helpers\Console;

/**
 * A Markdown parser that enhances markdown for reading in console environments.
 *
 * Based on [cebe/markdown](https://github.com/cebe/markdown).
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
     * @var array these are "escapeable" characters. When using one of these prefixed with a
     * backslash, the character will be outputted without the backslash and is not interpreted
     * as markdown.
     */
    protected $escapeCharacters = [
        '\\', // backslash
        '`', // backtick
        '*', // asterisk
        '_', // underscore
        '~', // tilde
    ];


    /**
     * Renders a code block.
     *
     * @param array $block
     * @return string
     */
    protected function renderCode($block)
    {
        return Console::ansiFormat($block['content'], [Console::NEGATIVE]) . "\n\n";
    }

    /**
     * Render a paragraph block.
     *
     * @param array $block
     * @return string
     */
    protected function renderParagraph($block)
    {
        return rtrim($this->renderAbsy($block['content'])) . "\n\n";
    }

    /**
     * Renders an inline code span `` ` ``.
     * @param array $element
     * @return string
     */
    protected function renderInlineCode($element)
    {
        return Console::ansiFormat($element[1], [Console::UNDERLINE]);
    }

    /**
     * Renders empathized elements.
     * @param array $element
     * @return string
     */
    protected function renderEmph($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::ITALIC]);
    }

    /**
     * Renders strong elements.
     * @param array $element
     * @return string
     */
    protected function renderStrong($element)
    {
        return Console::ansiFormat($this->renderAbsy($element[1]), [Console::BOLD]);
    }

    /**
     * Renders the strike through feature.
     * @param array $element
     * @return string
     */
    protected function renderStrike($element)
    {
        return Console::ansiFormat($this->parseInline($this->renderAbsy($element[1])), [Console::CROSSED_OUT]);
    }
}
