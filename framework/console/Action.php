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
 * @property \yii\console\Controller $controller
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Action extends \yii\base\Action
{
    /**
     * Returns one-line short summary describing this action.
     *
     * You may override this method to return customized summary.
     * The default implementation returns first line from the PHPDoc comment.
     *
     * @return string
     */
    public function getHelpSummary()
    {
        return HelpParser::getSummary(new \ReflectionClass($this));
    }

    /**
     * Returns help information for this action.
     *
     * You may override this method to return customized help.
     * The default implementation returns help information retrieved from the PHPDoc comment.
     * @return string
     */
    public function getHelp()
    {
        return HelpParser::getDescriptionForConsole(new \ReflectionClass($this));
    }
}
