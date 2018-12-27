<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Event;

/**
 * AfterSaveEvent 表示在 [[ActiveRecord::EVENT_AFTER_INSERT]] 和 [[ActiveRecord::EVENT_AFTER_UPDATE]] 中可用的信息。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class AfterSaveEvent extends Event
{
    /**
     * @var array 已更改并保存的属性值。
     */
    public $changedAttributes;
}
