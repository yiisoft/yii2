<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * WidgetEvent represents the event parameter used for a widget event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the widget.
 *
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2.0
 */
class WidgetEvent extends Event {
    
    /**
     * @var Widget the widget currently being executed
     */
    public $widget;
    /**
     * @var mixed the widget result. Event handlers may modify this property to change the widget result.
     */
    public $result;
    /**
     * @var boolean whether to continue running the widget. Event handlers of
     * [[Widget::EVENT_BEFORE_RUN]] may set this property to decide whether
     * to continue running the current widget.
     */
    public $isValid = true;


    /**
     * Constructor.
     * @param Widget $widget the widget associated with this widget event.
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($widget, $config = [])
    {
        $this->widget = $widget;
        parent::__construct($config);
    }
}
