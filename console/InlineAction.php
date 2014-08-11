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
 * InlineAction represents an action that is defined as a controller method.
 *
 * @inheritdoc
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class InlineAction extends \yii\base\InlineAction
{
    public function getDescription()
    {
        $class = new \ReflectionMethod($this->controller, $this->actionMethod);
        $docLines = preg_split('~(\n|\r|\r\n)~', $class->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], ' *');
        }
        return '';
    }

    public function getHelp()
    {
        $class = new \ReflectionMethod($this->controller, $this->actionMethod);
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
