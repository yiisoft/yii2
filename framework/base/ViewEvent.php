<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewEvent represents events triggered by the [[View]] component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewEvent extends Event
{
    /**
     * @var string the view file being rendered.
     */
    public $viewFile;
    /**
     * @var array the parameter array passed to the [[View::render()]] method.
     */
    public $params;
    /**
     * @var string the rendering result of [[View::renderFile()]].
     * Event handlers may modify this property and the modified output will be
     * returned by [[View::renderFile()]]. This property is only used
     * by [[View::EVENT_AFTER_RENDER]] event.
     */
    public $output;
    /**
     * @var bool whether to continue rendering the view file. Event handlers of
     * [[View::EVENT_BEFORE_RENDER]] may set this property to decide whether
     * to continue rendering the current view file.
     */
    public $isValid = true;
}
