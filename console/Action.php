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
     * Returns a short description (one line) of information about the action.
     *
     * The default implementation returns help information retrieved from the PHPDoc comments.
     *
     * @return string
     */
    public function getDescription()
    {
        return null;
    }

    /**
     * Returns help information for the action.
     *
     * The default implementation returns help information retrieved from the PHPDoc comments.
     * @return string
     */
    public function getHelp()
    {
        return null;
    }
}
