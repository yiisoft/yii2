<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ProfilerEvent represents the parameter needed by [[Profiler]] events.
 *
 * @author cronfy <cronfy@gmail.com>
 * @since 2.0.12
 */
class ProfilerEvent extends Event
{
    /**
     * @var string used to mark profiling event to distinguish it from others
     */
    public $token;
    /**
     * @var string event category
     */
    public $category;
    /**
     * @var mixed context of this event
     */
    public $context;
}