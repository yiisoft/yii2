<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * WidgetEvent 表示用于小部件事件的事件参数。
 *
 * 通过设置 [[isValid]] 属性，可以控制是否继续运行小部件。
 *
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2.0.11
 */
class WidgetEvent extends Event
{
    /**
     * @var mixed 小部件结果。事件处理程序可以修改此属性以更改小部件结果。
     */
    public $result;
    /**
     * @var bool 是否继续运行小部件。
     * [[Widget::EVENT_BEFORE_RUN]] 的事件处理程序可以设置此属性
     * 以决定是否继续运行当前小部件。
     */
    public $isValid = true;
}
