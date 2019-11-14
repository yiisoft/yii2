<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewEvent 表示由 [[View]] 组件触发的事件。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewEvent extends Event
{
    /**
     * @var string 正在渲染的视图文件。
     */
    public $viewFile;
    /**
     * @var array 传递给 [[View::render()]] 方法的参数数组。
     */
    public $params;
    /**
     * @var string [[View::renderFile()]] 的渲染结果。
     * 事件处理程序可以修改此属性，修改后的输出将由 [[View::renderFile()]] 返回。
     * 此属性仅由 [[View::EVENT_AFTER_RENDER]]
     * 事件使用。
     */
    public $output;
    /**
     * @var bool 是否继续渲染视图文件。
     * [[View::EVENT_BEFORE_RENDER]]
     * 的事件处理程序可以设置此属性以决定是否继续渲染当前视图文件。
     */
    public $isValid = true;
}
