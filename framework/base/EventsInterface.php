<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 */
interface EventsInterface extends VirtualPropertiesInterface
{

    public function prepareEvents();

    /**
     * Returns a value indicating whether there is any handler attached to the named event.
     * @param string $name the event name
     * @return boolean whether there is any handler attached to the event.
     */
    public function hasEventHandlers($name);

    /**
     * Attaches an event handler to an event.
     *
     * The event handler must be a valid PHP callback. The following are
     * some examples:
     *
     * ```
     * function ($event) { ... }         // anonymous function
     * [$object, 'handleClick']          // $object->handleClick()
     * ['Page', 'handleClick']           // Page::handleClick()
     * 'handleClick'                     // global function handleClick()
     * ```
     *
     * The event handler must be defined with the following signature,
     *
     * ```
     * function ($event)
     * ```
     *
     * where `$event` is an [[Event]] object which includes parameters associated with the event.
     *
     * @param string $name the event name
     * @param callable $handler the event handler
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * @param boolean $append whether to append new event handler to the end of the existing
     * handler list. If false, the new handler will be inserted at the beginning of the existing
     * handler list.
     * @see off()
     */
    public function on($name, $handler, $data = null, $append = true);

    /**
     * Detaches an existing event handler from this component.
     * This method is the opposite of [[on()]].
     * @param string $name event name
     * @param callable $handler the event handler to be removed.
     * If it is null, all handlers attached to the named event will be removed.
     * @return boolean if a handler is found and detached
     * @see on()
     */
    public function off($name, $handler = null);

    /**
     * Triggers an event.
     * This method represents the happening of an event. It invokes
     * all attached handlers for the event including class-level handlers.
     * @param string $name the event name
     * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
     */
    public function trigger($name, Event $event = null);
}
