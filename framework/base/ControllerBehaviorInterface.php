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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.12
 */
interface ControllerBehaviorInterface
{
    /**
     * @return array the view path that may be prefixed to a relative view name.
     */
    public function getActions();
}
