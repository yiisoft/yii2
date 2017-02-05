<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModuleBehaviorInterface is the class that should be implemented by behaviors who want to add controllers to the module.
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.12
 */
interface ModuleBehaviorInterface
{
    /**
     * @return array an array of controllers to be included in the controller map.
     */
    public function controllers();
}
