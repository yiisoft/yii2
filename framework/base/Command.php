<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Command incapsulates parameters and logic for performing an action and is useful for:
 *
 * - Decoupling the action itself from when it's executed i.e. scheduling for later execution.
 * - Moving context out of the action so it can be reused in many different contexts.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.6
 */
interface Command
{
    public function execute();
}
