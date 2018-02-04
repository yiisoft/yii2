<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Event;

/**
 * AfterExecuteEvent represents the information available in [[Command::EVENT_AFTER_EXECUTE]].
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.14
 */
class AfterExecuteEvent extends Event
{
    /**
     * @var Command command that was executed
     */
    public $command;
}
