<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

/**
 * ContainerInterface specifies the interface that a dependency injection container should implement.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ContainerInterface
{
    /**
     * Returns the list of the loaded shared component instances.
     * @return array the list of the loaded shared component instances (type or ID => component).
     */
    public function getComponents();

    /**
     * Returns the component definitions registered with this container.
     * @return array the component definitions registered with this container (type or ID => definition).
     */
    public function getComponentDefinitions();

    /**
     * Registers a set of component definitions in this container.
     *
     * This is the bulk version of [[set()]]. The parameter should be an array
     * whose keys are component types or IDs and values the corresponding component definitions.
     *
     * For more details on how to specify component types/IDs and definitions, please
     * refer to [[set()]].
     *
     * If a component definition with the same type/ID already exists, it will be overwritten.
     *
     * @param array $components component definitions or instances
     */
    public function setComponents($components);

    /**
     * Returns a value indicating whether the container has the component definition of the specified type or ID.
     * @param string $typeOrID component type (a fully qualified namespaced class/interface name, e.g. `yii\db\Connection`) or ID (e.g. `db`).
     * @return boolean whether the container has the component definition of the specified type or ID
     * @see set()
     */
    public function has($typeOrID);

    /**
     * Returns an instance of a component with the specified type or ID.
     *
     * If a component is registered as a shared component via [[set()]], this method will return
     * the same component instance each time it is called.
     * If a component is not shared, this method will create a new instance every time.
     *
     * @param string $typeOrID component type (a fully qualified namespaced class/interface name, e.g. `yii\db\Connection`) or ID (e.g. `db`).
     * @param array $params the named parameters (name => value) to be passed to the object constructor
     * if the method needs to create a new object instance.
     * @param boolean $create whether to create an instance of a component if it is not previously created.
     * This is mainly useful for shared instance.
     * @return object|null the component of the specified type or ID, null if the component `$create` is false
     * and the component was not instantiated before.
     * @throws \yii\base\InvalidConfigException if `$typeOrID` refers to a nonexistent component ID
     * or if there is cyclic dependency detected
     * @see has()
     * @see set()
     */
    public function get($typeOrID, $params = [], $create = true);

    /**
     * Registers a component definition with this container.
     *
     * If a component definition with the same type/ID already exists, it will be overwritten.
     *
     * @param string $typeOrID component type or ID. This can be in one of the following three formats:
     *
     * - a fully qualified namespaced class/interface name: e.g. `yii\db\Connection`.
     *   This declares a shared component. Only a single instance of this class will be created and injected
     *   into different objects who depend on this class. If this is an interface name, the class name will
     *   be obtained from `$definition`.
     * - a fully qualified namespaced class/interface name prefixed with an asterisk `*`: e.g. `*yii\db\Connection`.
     *   This declares a non-shared component. That is, if each time the container is injecting a dependency
     *   of this class, a new instance of this class will be created and used. If this is an interface name,
     *   the class name will be obtained from `$definition`.
     * - an ID: e.g. `db`. This declares a shared component with an ID. The class name should
     *   be declared in `$definition`. When [[get()]] is called, the same component instance will be returned.
     *
     * @param mixed $definition the component definition to be registered with this container.
     * It can be one of the followings:
     *
     * - a PHP callable: either an anonymous function or an array representing a class method (e.g. `['Foo', 'bar']`).
     *   The callable will be called by [[get()]] to return an object associated with the specified component type.
     *   The signature of the function should be: `function ($container)`, where `$container` is this container.
     * - an object: When [[get()]] is called, this object will be returned. No new object will be created.
     *   This essentially makes the component a shared one, regardless how it is specified in `$typeOrID`.
     * - a configuration array: the array contains name-value pairs that will be used to initialize the property
     *   values of the newly created object when [[get()]] is called. The `class` element stands for the
     *   the class of the object to be created. If `class` is not specified, `$typeOrID` will be used as the class name.
     * - a string: either a class name or a component ID that is registered with this container.
     *
     * If the parameter is null, the component definition will be removed from the container.
     */
    public function set($typeOrID, $definition);
}
