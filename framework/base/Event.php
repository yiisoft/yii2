<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\helpers\StringHelper;

/**
 * Event is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The [[sender]] property describes who raises the event.
 * And the [[handled]] property indicates if the event is handled.
 * If an event handler sets [[handled]] to be `true`, the rest of the
 * uninvoked handlers will no longer be called to handle the event.
 *
 * Additionally, when attaching an event handler, extra data may be passed
 * and be available via the [[data]] property when the event handler is invoked.
 *
 * For more details and usage information on Event, see the [guide article on events](guide:concept-events).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Event extends BaseObject
{
    /**
     * @var string the event name. This property is set by [[Component::trigger()]] and [[trigger()]].
     * Event handlers may use this property to check what event it is handling.
     */
    public $name;
    /**
     * @var object the sender of this event. If not set, this property will be
     * set as the object whose `trigger()` method is called.
     * This property may also be a `null` when this event is a
     * class-level event which is triggered in a static context.
     */
    public $sender;
    /**
     * @var bool whether the event is handled. Defaults to `false`.
     * When a handler sets this to be `true`, the event processing will stop and
     * ignore the rest of the uninvoked event handlers.
     */
    public $handled = false;
    /**
     * @var mixed the data that is passed to [[Component::on()]] when attaching an event handler.
     * Note that this varies according to which event handler is currently executing.
     */
    public $data;

    /**
     * @var array contains all globally registered event handlers.
     */
    private static $_events = [];
    /**
     * @var array the globally registered event handlers attached for wildcard patterns (event name wildcard => handlers)
     * @since 2.0.14
     */
    private static $_eventWildcards = [];


    /**
     * Attaches an event handler to a class-level event.
     *
     * When a class-level event is triggered, event handlers attached
     * to that class and all parent classes will be invoked.
     *
     * For example, the following code attaches an event handler to `ActiveRecord`'s
     * `afterInsert` event:
     *
     * ```php
     * Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
     *     Yii::trace(get_class($event->sender) . ' is inserted.');
     * });
     * ```
     *
     * The handler will be invoked for EVERY successful ActiveRecord insertion.
     *
     * Since 2.0.14 you can specify either class name or event name as a wildcard pattern:
     *
     * ```php
     * Event::on('app\models\db\*', '*Insert', function ($event) {
     *     Yii::trace(get_class($event->sender) . ' is inserted.');
     * });
     * ```
     *
     * For more details about how to declare an event handler, please refer to [[Component::on()]].
     *
     * @param string $class the fully qualified class name to which the event handler needs to attach.
     * @param string $name the event name.
     * @param callable $handler the event handler.
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * @param bool $append whether to append new event handler to the end of the existing
     * handler list. If `false`, the new handler will be inserted at the beginning of the existing
     * handler list.
     * @see off()
     */
    public static function on($class, $name, $handler, $data = null, $append = true)
    {
        $class = ltrim($class, '\\');

        if (strpos($class, '*') !== false || strpos($name, '*') !== false) {
            if ($append || empty(self::$_eventWildcards[$name][$class])) {
                self::$_eventWildcards[$name][$class][] = [$handler, $data];
            } else {
                array_unshift(self::$_eventWildcards[$name][$class], [$handler, $data]);
            }
            return;
        }

        if ($append || empty(self::$_events[$name][$class])) {
            self::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     * Detaches an event handler from a class-level event.
     *
     * This method is the opposite of [[on()]].
     *
     * Note: in case wildcard pattern is passed for class name or event name, only the handlers registered with this
     * wildcard will be removed, while handlers registered with plain names matching this wildcard will remain.
     *
     * @param string $class the fully qualified class name from which the event handler needs to be detached.
     * @param string $name the event name.
     * @param callable $handler the event handler to be removed.
     * If it is `null`, all handlers attached to the named event will be removed.
     * @return bool whether a handler is found and detached.
     * @see on()
     */
    public static function off($class, $name, $handler = null)
    {
        $class = ltrim($class, '\\');
        if (empty(self::$_events[$name][$class]) && empty(self::$_eventWildcards[$name][$class])) {
            return false;
        }
        if ($handler === null) {
            unset(self::$_events[$name][$class]);
            unset(self::$_eventWildcards[$name][$class]);
            return true;
        }

        // plain event names
        if (isset(self::$_events[$name][$class])) {
            $removed = false;
            foreach (self::$_events[$name][$class] as $i => $event) {
                if ($event[0] === $handler) {
                    unset(self::$_events[$name][$class][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                self::$_events[$name][$class] = array_values(self::$_events[$name][$class]);
                return $removed;
            }
        }

        // wildcard event names
        $removed = false;
        foreach (self::$_eventWildcards[$name][$class] as $i => $event) {
            if ($event[0] === $handler) {
                unset(self::$_eventWildcards[$name][$class][$i]);
                $removed = true;
            }
        }
        if ($removed) {
            self::$_eventWildcards[$name][$class] = array_values(self::$_eventWildcards[$name][$class]);
            // remove empty wildcards to save future redundant regex checks :
            if (empty(self::$_eventWildcards[$name][$class])) {
                unset(self::$_eventWildcards[$name][$class]);
                if (empty(self::$_eventWildcards[$name])) {
                    unset(self::$_eventWildcards[$name]);
                }
            }
        }

        return $removed;
    }

    /**
     * Detaches all registered class-level event handlers.
     * @see on()
     * @see off()
     * @since 2.0.10
     */
    public static function offAll()
    {
        self::$_events = [];
        self::$_eventWildcards = [];
    }

    /**
     * Returns a value indicating whether there is any handler attached to the specified class-level event.
     * Note that this method will also check all parent classes to see if there is any handler attached
     * to the named event.
     * @param string|object $class the object or the fully qualified class name specifying the class-level event.
     * @param string $name the event name.
     * @return bool whether there is any handler attached to the event.
     */
    public static function hasHandlers($class, $name)
    {
        if (empty(self::$_eventWildcards) && empty(self::$_events[$name])) {
            return false;
        }

        if (is_object($class)) {
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        // regular events
        foreach ($classes as $class) {
            if (!empty(self::$_events[$name][$class])) {
                return true;
            }
        }

        // wildcard events
        foreach (self::$_eventWildcards as $nameWildcard => $classHandlers) {
            if (!StringHelper::matchWildcard($nameWildcard, $name)) {
                continue;
            }
            foreach ($classHandlers as $classWildcard => $handlers) {
                if (empty($handlers)) {
                    continue;
                }
                foreach ($classes as $class) {
                    if (!StringHelper::matchWildcard($classWildcard, $class)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Triggers a class-level event.
     * This method will cause invocation of event handlers that are attached to the named event
     * for the specified class and all its parent classes.
     * @param string|object $class the object or the fully qualified class name specifying the class-level event.
     * @param string $name the event name.
     * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
     */
    public static function trigger($class, $name, $event = null)
    {
        $wildcardEventHandlers = [];
        foreach (self::$_eventWildcards as $nameWildcard => $classHandlers) {
            if (!StringHelper::matchWildcard($nameWildcard, $name)) {
                continue;
            }
            $wildcardEventHandlers = array_merge($wildcardEventHandlers, $classHandlers);
        }

        if (empty(self::$_events[$name]) && empty($wildcardEventHandlers)) {
            return;
        }

        if ($event === null) {
            $event = new static();
        }
        $event->handled = false;
        $event->name = $name;

        if (is_object($class)) {
            if ($event->sender === null) {
                $event->sender = $class;
            }
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            $eventHandlers = [];
            foreach ($wildcardEventHandlers as $classWildcard => $handlers) {
                if (StringHelper::matchWildcard($classWildcard, $class)) {
                    $eventHandlers = array_merge($eventHandlers, $handlers);
                    unset($wildcardEventHandlers[$classWildcard]);
                }
            }

            if (!empty(self::$_events[$name][$class])) {
                $eventHandlers = array_merge($eventHandlers, self::$_events[$name][$class]);
            }

            foreach ($eventHandlers as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                if ($event->handled) {
                    return;
                }
            }
        }
    }
}
