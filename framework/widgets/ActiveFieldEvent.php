<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Event;

/**
 * ActiveFieldEvent represents the event parameter used for an active field event.
 *
 * @property ActiveForm $target the sender of this event.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class ActiveFieldEvent extends Event
{
    /**
     * @var ActiveField related active field instance
     */
    public $field;


    /**
     * Constructor.
     * @param ActiveField $field the active field associated with this event.
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($field, $config = [])
    {
        $this->field = $field;
        parent::__construct($config);
    }
}