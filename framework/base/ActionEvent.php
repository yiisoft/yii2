<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent 表示用于动作事件的事件参数。
 *
 * 通过设置 [[isValid]] 属性，可以控制是否继续运行此动作。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionEvent extends Event
{
    /**
     * @var Action 目前正在执行的动作
     */
    public $action;
    /**
     * @var mixed 动作结果。事件处理程序可以修改此属性以更改动作结果。
     */
    public $result;
    /**
     * @var bool 是否继续运行该动作。
     * [[Controller::EVENT_BEFORE_ACTION]]
     * 的事件处理程序可以设置此属性以决定是否继续运行当前动作。
     */
    public $isValid = true;


    /**
     * 构造函数
     * @param Action $action 与此动作事件关联的动作。
     * @param array $config 将用于初始化对象属性的键值对
     */
    public function __construct($action, $config = [])
    {
        $this->action = $action;
        parent::__construct($config);
    }
}
