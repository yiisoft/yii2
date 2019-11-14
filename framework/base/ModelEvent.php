<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelEvent 表示 [[Model]] 事件所需的参数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelEvent extends Event
{
    /**
     * @var bool 模型是否处于有效状态。默认为 true。
     * 如果模型通过验证或某些检查，则模型处于有效状态。
     */
    public $isValid = true;
}
