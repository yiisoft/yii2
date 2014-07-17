<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

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
    ];

    /**
     * @inheritDoc
     */
    protected function identifyLine($lines, $current)
    {
        if (isset($lines[$current]) && (strncmp($lines[$current], '```', 3) === 0 || strncmp($lines[$current], '~~~', 3) === 0)) {
            return 'fencedCode';
        }
        return parent::identifyLine($lines, $current);
    }

    /**
     * Consume lines for a fenced code block
     */
    protected function consumeFencedCode($lines, $current)
    {
        // consume until ```
        $block = [
            'type' => 'code',
            'content' => [],
        ];
        $line = rtrim($lines[$current]);
        $fence = substr($line, 0, $pos = strrpos($line, $line[0]) + 1);
        $language = substr($line, $pos);
        if (!empty($language)) {
            $block['language'] = $language;
        }
        for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
            if (rtrim($line = $lines[$i]) !== $fence) {
                $block['content'][] = $line;
            } else {
                break;
            }
        }
        return [$block, $i];
    }

    /**
     * Renders a code block
     */
    protected function renderCode($block)
    {
        return Console::ansiFormat(implode("\n", $block['content']), [Console::BG_GREY]) . "\n";
    }

    protected function renderParagraph($block)
    {
        return rtrim($this->parseInline(implode("\n", $block['content']))) . "\n";
    }

    /**
     * @inheritDoc
     */
    protected function inlineMarkers()
    {
        return [
            '*'     => 'parseEmphStrong',
            '_'     => 'parseEmphStrong',
            '\\'    => 'parseEscape',
            '`'     => 'parseCode',
            '~~'    => 'parseStrike',
        ];
    }

    /**
     * Parses an inline code span `` ` ``.
     */
    protected function parseCode($text)
    {
        // skip fenced code
        if (strncmp($text, '```', 3) === 0) {
            return [$text[0], 1];
        }
        if (preg_match('/^(`+) (.+?) \1/', $text, $matches)) { // code with enclosed backtick
            return [
                Console::ansiFormat($matches[2], [Console::UNDERLINE]),
                strlen($matches[0])
            ];
        } elseif (preg_match('/^`(.+?)`/', $text, $matches)) {
            return [
                Console::ansiFormat($matches[1], [Console::UNDERLINE]),
                strlen($matches[0])
            ];
        }
        return [$text[0], 1];
    }

    /**
     * Parses empathized and strong elements.
     */
    protected function parseEmphStrong($text)
    {
        $marker = $text[0];

        if (!isset($text[1])) {
            return [$text[0], 1];
        }

        if ($marker == $text[1]) { // strong
            if ($marker == '*' && preg_match('/^[*]{2}((?:[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s', $text, $matches) ||
                $marker == '_' && preg_match('/^__((?:[^_]|_[^_]*_)+?)__(?!_)/us', $text, $matches)) {

                return [Console::ansiFormat($this->parseInline($matches[1]), Console::BOLD), strlen($matches[0])];
            }
        } else { // emph
            if ($marker == '*' && preg_match('/^[*]((?:[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s', $text, $matches) ||
                $marker == '_' && preg_match('/^_((?:[^_]|__[^_]*__)+?)_(?!_)\b/us', $text, $matches)) {
                return [Console::ansiFormat($this->parseInline($matches[1]), Console::ITALIC), strlen($matches[0])];
            }
        }
        return [$text[0], 1];
    }

    /**
     * Parses the strikethrough feature.
     */
    protected function parseStrike($markdown)
    {
        if (preg_match('/^~~(.+?)~~/', $markdown, $matches)) {
            return [
                Console::ansiFormat($this->parseInline($matches[1]), [Console::CROSSED_OUT]),
                strlen($matches[0])
            ];
        }
        return [$markdown[0] . $markdown[1], 2];
    }

    /**
     * Parses escaped special characters.
     */
    protected function parseEscape($text)
    {
        if (isset($text[1]) && in_array($text[1], $this->escapeCharacters)) {
            return [$text[1], 2];
        }
        return [$text[0], 1];
    }
}