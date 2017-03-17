<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\log\Logger;

/**
 * Profiler is the class for application profiling.
 *
 * Profiler is used as a core component of Yii application.
 *
 * @see [[Yii::beginProfile()]]
 * @see [[Yii::endProfile()]]
 *
 * @author cronfy <cronfy@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.12
 */
class Profiler extends Component
{

    /**
     * @event Event an event that is triggered when profiling should be started
     */
    const EVENT_BEGIN = 'beginProfile';
    /**
     * @event Event an event that is triggered when profiling should be ended
     */
    const EVENT_END = 'endProfile';

    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[end()]] with the same category name.
     * The begin- and end- calls must also be properly nested.
     *
     * @param string $token token for the code block
     * @param string $category the category of this profiler message (used for logging)
     * @param mixed $context arbitrary data that can be used by profiler event listeners
     * @see end()
     */
    public function begin($token, $category = 'application', $context = null)
    {
        $event = new ProfilerEvent(compact('token', 'category', 'context'));
        Yii::$app->trigger(static::EVENT_BEGIN, $event);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[begin()]] with the same category name.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @param mixed $context arbitrary data that can be used by profiler event listeners
     * @see begin()
     */
    public function end($token, $category = 'application', $context = null)
    {
        $event = new ProfilerEvent(compact('token', 'category', 'context'));
        Yii::$app->trigger(static::EVENT_END, $event);
    }

}