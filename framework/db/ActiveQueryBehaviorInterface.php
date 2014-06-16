<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveQueryBehaviorInterface allows behavior declared for the [[ActiveRecord]] attach relative
 * behavior to the [[ActiveQuery]] once it is created.
 *
 * @author Klimov Paul <klimov@zfort.com>
 * @since 2.0
 */
interface ActiveQueryBehaviorInterface
{
    /**
     * Returns definition of the behavior, which should be attached to the [[ActiveQuery]] instance.
     * @return mixed behavior instance or configuration.
     */
    public function queryBehavior();
}