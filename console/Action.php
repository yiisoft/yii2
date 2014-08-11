<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\helpers\Console;

/**
 * Action is the base class for all controller action classes.
 *
 * @inheritdoc
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Action extends \yii\base\Action
{
    public function getDescription()
    {
        $class = new \ReflectionClass($this);
        $docLines = preg_split('~(\n|\r|\r\n)~', $class->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], ' *');
        }
        return '';
    }

    public function getHelp()
    {
        $class = new \ReflectionClass($this);
        $comment = strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($class->getDocComment(), '/'))), "\r", '');
        if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
            $comment = trim(substr($comment, 0, $matches[0][1]));
        }
        if ($comment !== '') {
            return rtrim(Console::renderColoredString(Console::markdownToAnsi($comment)));
        }
        return '';
    }
}
