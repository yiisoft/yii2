<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\helpers\Console;

/**
 * HelpParser contains methods used to get help information from phpDoc.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class HelpParser
{
    /**
     * Returns the first line of docblock.
     *
     * @param \Reflector $reflector
     * @return string
     */
    public static function getSummary(\Reflector $reflector)
    {
        $docLines = preg_split('~\R~', $reflector->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], ' *');
        }
        return '';
    }

    /**
     * Returns full description from the docblock.
     *
     * @param \Reflector $reflector
     * @return string
     */
    public static function getDetail(\Reflector $reflector)
    {
        $comment = strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($reflector->getDocComment(), '/'))), "\r", '');
        if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
            $comment = trim(substr($comment, 0, $matches[0][1]));
        }
        if ($comment !== '') {
            return rtrim(Console::renderColoredString(Console::markdownToAnsi($comment)));
        }
        return '';
    }
} 