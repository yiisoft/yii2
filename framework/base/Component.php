<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\StringHelper;

/**
 * Component is the base class that implements the *property*, *event* and *behavior* features.
 *
 * Component provides the *event* and *behavior* features, in addition to the *property* feature which is implemented in
 * its parent class [[\yii\base\BaseObject|BaseObject]].
 *
 * Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
 * an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
 * is triggered (i.e. comment will be added), our custom code will be executed.
 *
 * An event is identified by a name that should be unique within the class it is defined at. Event names are *case-sensitive*.
 *
 * One or multiple PHP callbacks, called *event handlers*, can be attached to an event. You can call [[trigger()]] to
 * raise an event. When an event is raised, the event handlers will be invoked automatically in the order they were
 * attached.
 *
 * To attach an event handler to an event, call [[on()]]:
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // send email notification
 * });
 * ```
 *
 * In the above, an anonymous function is attached to the "update" event of the post. You may attach
 * the following types of event handlers:
 *
 * - anonymous function: `function ($event) { ... }`
 * - object method: `[$object, 'handleAdd']`
 * - static class method: `['Page', 'handleAdd']`
 * - global function: `'handleAdd'`
 *
 * The signature of an event handler should be like the following:
 *
 * ```php
 * function foo($event)
 * ```
 *
 * where `$event` is an [[Event]] object which includes parameters associated with the event.
 *
 * You can also attach a handler to an event when configuring a component with a configuration array.
 * The syntax is like the following:
 *
 * ```php
 * [
 *     'on add' => function ($event) { ... }
 * ]
 * ```
 *
 * where `on add` stands for attaching an event to the `add` event.
 *
 * Sometimes, you may want to associate extra data with an event handler when you attach it to an event
 * and then access it when the handler is invoked. You may do so by
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // the data can be accessed via $event->data
 * }, $data);
 * ```
 *
 * A behavior is an instance of [[Behavior]] or its child class. A component can be attached with one or multiple
 * behaviors. When a behavior is attached to a component, its public properties and methods can be accessed via the
 * component directly, as if the component owns those properties and methods.
 *
 * To attach a behavior to a component, declare it in [[behaviors()]], or explicitly call [[attachBehavior]]. Behaviors
 * declared in [[behaviors()]] are automatically attached to the corresponding component.
 *
 * One can also attach a behavior to a component when configuring it with a configuration array. The syntax is like the
 * following:
 *
 * ```php
 * [
 *     'as tree' => [
 *         'class' => 'Tree',
 *     ],
 * ]
 * ```
 *
 * where `as tree` stands for attaching a behavior named `tree`, and the array will be passed to [[\Yii::createObject()]]
 * to create the behavior object.
 *
 * For more details and usage information on Component, see the [guide article on components](guide:concept-components).
 *
 * @property-read Behavior[] $behaviors List of behaviors attached to this component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends BaseObject
{
    /**
     * @var array the attached event handlers (event name => handlers)
     */
    private $_events = [];
    /**
     * @var array the event handlers attached for wildcard patterns (event name wildcard => handlers)
     * @since 2.0.14
     */
    private $_eventWildcards = [];
    /**
     * @var Behavior[]|null the attached behaviors (behavior name => behavior). This is `null` when not initialized.
     */
    private $_behaviors;


    /**
     * Returns the value of a component property.
     *
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a getter: return the getter result
     *  - a property of a behavior: return the behavior property value
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $component->property;`.
     * @param string $name the property name
     * @return mixed the property value or the value of a behavior's property
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only.
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            return $this->$getter();
        }

        // behavior property
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $behavior) {
            if ($behavior->canGetProperty($name)) {
                return $behavior->$name;
            }
        }

        if (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Sets the value of a component property.
     *
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: set the property value
     *  - an event in the format of "on xyz": attach the handler to the event "xyz"
     *  - a behavior in the format of "as xyz": attach the behavior named as "xyz"
     *  - a property of a behavior: set the behavior property value
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$component->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is read-only.
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            // set property
            $this->$setter($value);

            return;
        } elseif (strncmp($name, 'on ', 3) === 0) {
            // on event: attach event handler
            $this->on(trim(substr($name, 3)), $value);

            return;
        } elseif (strncmp($name, 'as ', 3) === 0) {
            // as behavior: attach behavior
            $name = trim(substr($name, 3));
            if ($value instanceof Behavior) {
                $this->attachBehavior($name, $value);
            } elseif ($value instanceof \Closure) {
                $this->attachBehavior($name, call_user_func($value));
            } elseif (isset($value['__class']) && is_subclass_of($value['__class'], Behavior::class)) {
                $this->attachBehavior($name, Yii::createObject($value));
            } elseif (!isset($value['__class']) && isset($value['class']) && is_subclass_of($value['class'], Behavior::class)) {
                $this->attachBehavior($name, Yii::createObject($value));
            } elseif (is_string($value) && is_subclass_of($value, Behavior::class, true)) {
                $this->attachBehavior($name, Yii::createObject($value));
            } else {
                throw new InvalidConfigException('Class is not of type ' . Behavior::class . ' or its subclasses');
            }

            return;
        }

        // behavior property
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $behavior) {
            if ($behavior->canSetProperty($name)) {
                $behavior->$name = $value;
                return;
            }
        }

        if (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        }

        throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Checks if a property is set, i.e. defined and not null.
     *
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: return whether the property is set
     *  - a property of a behavior: return whether the property is set
     *  - return `false` for non existing properties
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($component->property)`.
     * @param string $name the property name or the event name
     * @return bool whether the named property is set
     * @see https://www.php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        // behavior property
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $behavior) {
            if ($behavior->canGetProperty($name)) {
                return $behavior->$name !== null;
            }
        }

        return false;
    }

    /**
     * Sets a component property to be null.
     *
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: set the property value to be null
     *  - a property of a behavior: set the property value to be null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($component->property)`.
     * @param string $name the property name
     * @throws InvalidCallException if the property is read only.
     * @see https://www.php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
            return;
        }

        // behavior property
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $behavior) {
            if ($behavior->canSetProperty($name)) {
                $behavior->$name = null;
                return;
            }
        }

        throw new InvalidCallException('Unsetting an unknown or read-only property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Calls the named method which is not a class method.
     *
     * This method will check if any attached behavior has
     * the named method and will execute it if available.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * @param string $name the method name
     * @param array $params method parameters
     * @return mixed the method return value
     * @throws UnknownMethodException when calling unknown method
     */
    public function __call($name, $params)
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $object) {
            if ($object->hasMethod($name)) {
                return call_user_func_array([$object, $name], $params);
            }
        }
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * This method is called after the object is created by cloning an existing one.
     * It removes all behaviors because they are attached to the old object.
     */
    public function __clone()
    {
        $this->_events = [];
        $this->_eventWildcards = [];
        $this->_behaviors = null;
    }

    /**
     * Returns a value indicating whether a property is defined for this component.
     *
     * A property is defined if:
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkVars whether to treat member variables as properties
     * @param bool $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return bool whether the property is defined
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return $this->canGetProperty($name, $checkVars, $checkBehaviors) || $this->canSetProperty($name, false, $checkBehaviors);
    }

    /**
     * Returns a value indicating whether a property can be read.
     *
     * A property can be read if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a readable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkVars whether to treat member variables as properties
     * @param bool $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return bool whether the property can be read
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns a value indicating whether a property can be set.
     *
     * A property can be written if:
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a writable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkVars whether to treat member variables as properties
     * @param bool $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return bool whether the property can be written
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns a value indicating whether a method is defined.
     *
     * A method is defined if:
     *
     * - the class has a method with the specified name
     * - an attached behavior has a method with the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkBehaviors whether to treat behaviors' methods as methods of this component
     * @return bool whether the method is defined
     */
    public function hasMethod($name, $checkBehaviors = true)
    {
        if (method_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->hasMethod($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     *
     * ```php
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ```
     *
     * Note that a behavior class must extend from [[Behavior]]. Behaviors can be attached using a name or anonymously.
     * When a name is used as the array key, using this name, the behavior can later be retrieved using [[getBehavior()]]
     * or be detached using [[detachBehavior()]]. Anonymous behaviors can not be retrieved or detached.
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     *
     * @return array the behavior configurations.
     *
     * @phpstan-return array<int|string, class-string|array{class: class-string, ...}>
     * @psalm-return array<int|string, class-string|array{class: class-string, ...}>
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Returns a value indicating whether there is any handler attached to the named event.
     * @param string $name the event name
     * @return bool whether there is any handler attached to the event.
     */
    public function hasEventHandlers($name)
    {
        $this->ensureBehaviors();

        if (!empty($this->_events[$name])) {
            return true;
        }

        foreach ($this->_eventWildcards as $wildcard => $handlers) {
            if (!empty($handlers) && StringHelper::matchWildcard($wildcard, $name)) {
                return true;
            }
        }

        return Event::hasHandlers($this, $name);
    }

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
     * Since 2.0.14 you can specify event name as a wildcard pattern:
     *
     * ```php
     * $component->on('event.group.*', function ($event) {
     *     Yii::trace($event->name . ' is triggered.');
     * });
     * ```
     *
     * @param string $name the event name
     * @param callable $handler the event handler
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * @param bool $append whether to append new event handler to the end of the existing
     * handler list. If false, the new handler will be inserted at the beginning of the existing
     * handler list.
     * @see off()
     */
    public function on($name, $handler, $data = null, $append = true)
    {
        $this->ensureBehaviors();

        if (strpos($name, '*') !== false) {
            if ($append || empty($this->_eventWildcards[$name])) {
                $this->_eventWildcards[$name][] = [$handler, $data];
            } else {
                array_unshift($this->_eventWildcards[$name], [$handler, $data]);
            }
            return;
        }

        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = [$handler, $data];
        } else {
            array_unshift($this->_events[$name], [$handler, $data]);
        }
    }

    /**
     * Detaches an existing event handler from this component.
     *
     * This method is the opposite of [[on()]].
     *
     * Note: in case wildcard pattern is passed for event name, only the handlers registered with this
     * wildcard will be removed, while handlers registered with plain names matching this wildcard will remain.
     *
     * @param string $name event name
     * @param callable|null $handler the event handler to be removed.
     * If it is null, all handlers attached to the named event will be removed.
     * @return bool if a handler is found and detached
     * @see on()
     */
    public function off($name, $handler = null)
    {
        $this->ensureBehaviors();
        if (empty($this->_events[$name]) && empty($this->_eventWildcards[$name])) {
            return false;
        }
        if ($handler === null) {
            unset($this->_events[$name], $this->_eventWildcards[$name]);
            return true;
        }

        $removed = false;
        // plain event names
        if (isset($this->_events[$name])) {
            foreach ($this->_events[$name] as $i => $event) {
                if ($event[0] === $handler) {
                    unset($this->_events[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_events[$name] = array_values($this->_events[$name]);
                return true;
            }
        }

        // wildcard event names
        if (isset($this->_eventWildcards[$name])) {
            foreach ($this->_eventWildcards[$name] as $i => $event) {
                if ($event[0] === $handler) {
                    unset($this->_eventWildcards[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_eventWildcards[$name] = array_values($this->_eventWildcards[$name]);
                // remove empty wildcards to save future redundant regex checks:
                if (empty($this->_eventWildcards[$name])) {
                    unset($this->_eventWildcards[$name]);
                }
            }
        }

        return $removed;
    }

    /**
     * Triggers an event.
     *
     * This method represents the happening of an event. It invokes all attached handlers for the event
     * including class-level handlers.
     *
     * @param string $name the event name
     * @param Event|null $event the event instance. If not set, a default [[Event]] object will be created.
     */
    public function trigger($name, ?Event $event = null)
    {
        $this->ensureBehaviors();

        $eventHandlers = [];
        foreach ($this->_eventWildcards as $wildcard => $handlers) {
            if (StringHelper::matchWildcard($wildcard, $name)) {
                $eventHandlers[] = $handlers;
            }
        }
        if (!empty($this->_events[$name])) {
            $eventHandlers[] = $this->_events[$name];
        }

        if (!empty($eventHandlers)) {
            $eventHandlers = call_user_func_array('array_merge', $eventHandlers);
            if ($event === null) {
                $event = new Event();
            }
            if ($event->sender === null) {
                $event->sender = $this;
            }
            $event->handled = false;
            $event->name = $name;
            foreach ($eventHandlers as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                // stop further handling if the event is handled
                if ($event->handled) {
                    return;
                }
            }
        }

        // invoke class-level attached handlers
        Event::trigger($this, $name, $event);
    }

    /**
     * Returns the named behavior object.
     * @param string $name the behavior name
     * @return Behavior|null the behavior object, or null if the behavior does not exist
     */
    public function getBehavior($name)
    {
        $this->ensureBehaviors();
        return isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
    }

    /**
     * Returns all behaviors attached to this component.
     * @return Behavior[] list of behaviors attached to this component
     */
    public function getBehaviors()
    {
        $this->ensureBehaviors();
        return $this->_behaviors;
    }

    /**
     * Attaches a behavior to this component.
     * This method will create the behavior object based on the given
     * configuration. After that, the behavior object will be attached to
     * this component by calling the [[Behavior::attach()]] method.
     * @param string $name the name of the behavior.
     * @param string|array|Behavior $behavior the behavior configuration. This can be one of the following:
     *
     *  - a [[Behavior]] object
     *  - a string specifying the behavior class
     *  - an object configuration array that will be passed to [[Yii::createObject()]] to create the behavior object.
     *
     * @return Behavior the behavior object
     * @see detachBehavior()
     */
    public function attachBehavior($name, $behavior)
    {
        $this->ensureBehaviors();
        return $this->attachBehaviorInternal($name, $behavior);
    }

    /**
     * Attaches a list of behaviors to the component.
     * Each behavior is indexed by its name and should be a [[Behavior]] object,
     * a string specifying the behavior class, or an configuration array for creating the behavior.
     * @param array $behaviors list of behaviors to be attached to the component
     * @see attachBehavior()
     */
    public function attachBehaviors($behaviors)
    {
        $this->ensureBehaviors();
        foreach ($behaviors as $name => $behavior) {
            $this->attachBehaviorInternal($name, $behavior);
        }
    }

    /**
     * Detaches a behavior from the component.
     * The behavior's [[Behavior::detach()]] method will be invoked.
     * @param string $name the behavior's name.
     * @return Behavior|null the detached behavior. Null if the behavior does not exist.
     */
    public function detachBehavior($name)
    {
        $this->ensureBehaviors();
        if (isset($this->_behaviors[$name])) {
            $behavior = $this->_behaviors[$name];
            unset($this->_behaviors[$name]);
            $behavior->detach();
            return $behavior;
        }

        return null;
    }

    /**
     * Detaches all behaviors from the component.
     */
    public function detachBehaviors()
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $name => $behavior) {
            $this->detachBehavior($name);
        }
    }

    /**
     * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
     */
    public function ensureBehaviors()
    {
        if ($this->_behaviors === null) {
            $this->_behaviors = [];
            foreach ($this->behaviors() as $name => $behavior) {
                $this->attachBehaviorInternal($name, $behavior);
            }
        }
    }

    /**
     * Attaches a behavior to this component.
     * @param string|int $name the name of the behavior. If this is an integer, it means the behavior
     * is an anonymous one. Otherwise, the behavior is a named one and any existing behavior with the same name
     * will be detached first.
     * @param string|array|Behavior $behavior the behavior to be attached
     * @return Behavior the attached behavior.
     */
    private function attachBehaviorInternal($name, $behavior)
    {
        if (!($behavior instanceof Behavior)) {
            $behavior = Yii::createObject($behavior);
        }
        if (is_int($name)) {
            $behavior->attach($this);
            $this->_behaviors[] = $behavior;
        } else {
            if (isset($this->_behaviors[$name])) {
                $this->_behaviors[$name]->detach();
            }
            $behavior->attach($this);
            $this->_behaviors[$name] = $behavior;
        }

        return $behavior;
    }
}
