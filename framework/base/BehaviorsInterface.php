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
 * @since 2.0.11
 */
interface BehaviorsInterface extends EventsInterface
{

    /**
     * Returns a value indicating whether a property is defined for this component.
     * A property is defined if:
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property is defined
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true);

    /**
     * Returns a value indicating whether a property can be read.
     * A property can be read if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a readable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property can be read
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true);

    /**
     * Returns a value indicating whether a property can be set.
     * A property can be written if:
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a writable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property can be written
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true);

    /**
     * Returns a value indicating whether a method is defined.
     * A method is defined if:
     *
     * - the class has a method with the specified name
     * - an attached behavior has a method with the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkBehaviors whether to treat behaviors' methods as methods of this component
     * @return boolean whether the property is defined
     */
    public function hasMethod($name, $checkBehaviors = true);

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
     */
    public function behaviors();

    /**
     * Returns the named behavior object.
     * @param string $name the behavior name
     * @return null|Behavior the behavior object, or null if the behavior does not exist
     */
    public function getBehavior($name);

    /**
     * Returns all behaviors attached to this component.
     * @return Behavior[] list of behaviors attached to this component
     */
    public function getBehaviors();

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
     *  - an object configuration array that will be passed to [[\Yii::createObject()]] to create the behavior object.
     *
     * @return Behavior the behavior object
     * @see detachBehavior()
     */
    public function attachBehavior($name, $behavior);

    /**
     * Attaches a list of behaviors to the component.
     * Each behavior is indexed by its name and should be a [[Behavior]] object,
     * a string specifying the behavior class, or an configuration array for creating the behavior.
     * @param array $behaviors list of behaviors to be attached to the component
     * @see attachBehavior()
     */
    public function attachBehaviors($behaviors);

    /**
     * Detaches a behavior from the component.
     * The behavior's [[Behavior::detach()]] method will be invoked.
     * @param string $name the behavior's name.
     * @return null|Behavior the detached behavior. Null if the behavior does not exist.
     */
    public function detachBehavior($name);

    /**
     * Detaches all behaviors from the component.
     */
    public function detachBehaviors();

    /**
     * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
     */
    public function ensureBehaviors();
}
