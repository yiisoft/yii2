<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewRenderer 是视图渲染类的基类。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class ViewRenderer extends Component
{
    /**
     * 渲染视图文件。
     *
     * 当它尝试渲染视图时，[[View]] 都会调用此方法。
     * 子类必须实现此方法才能渲染给定的视图文件。
     *
     * @param View $view 用于渲染文件的视图对象。
     * @param string $file 视图文件。
     * @param array $params 要传递给视图文件的参数。
     * @return string 渲染结果
     */
    abstract public function render($view, $file, $params);
}
