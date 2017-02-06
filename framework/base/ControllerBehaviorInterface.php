<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii\base;
/**
 * ControllerBehaviorInterface is the class that should be implemented by behaviors who want to add actions to the controller.
 *
 * @author Derek Gifford <derekisbusy@gmail.com>
 * @since 2.0.11.2
 */
interface ControllerBehaviorInterface
{
    /**
     * @return array the array of actions to be included into the owner.
     */
    public function getActions();
}
