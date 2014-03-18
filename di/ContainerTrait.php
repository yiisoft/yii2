<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use Yii;
use Closure;
use yii\base\InvalidConfigException;

/**
 * ContainerTrait implements the [[ContainerInterface]] that can turn a class into a service locator as well as a dependency injection container.
 *
 * By calling [[set()]] or [[setComponents()]], you can register with the container the components
 * that may be later instantiated or accessed via [[get()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ContainerTrait
{
    /**
     * @var array shared component instances indexed by their IDs or types
     */
    private $_components = [];
    /**
     * @var array component definitions indexed by their IDs or types
     */
    private $_definitions = [];


    /**
     * Returns a value indicating whether the container has the specified component definition or has instantiated the shared component.
     * This method may return different results depending on the value of `$checkInstance`.
     *
     * - If `$checkInstance` is false (default), the method will return a value indicating whether the container has the specified
     * component definition.
     * - If `$checkInstance` is true, the method will return a value indicating whether the container has
     * instantiated the specified shared component.
     *
     * @param string $typeOrID component type (a fully qualified namespaced class/interface name, e.g. `yii\db\Connection`) or ID (e.g. `db`).
     * When a class/interface name is given, make sure it does NOT have a leading backslash.
     * @param boolean $checkInstance whether the method should check if the component is shared and instantiated.
     * @return boolean whether the container has the component definition of the specified type or ID
     * @see set()
     */
    public function has($typeOrID, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_components[$typeOrID]) : isset($this->_definitions[$typeOrID]);
    }

    private $_building = [];

    /**
     * Returns an instance of a component with the specified type or ID.
     *
     * If a component is registered as a shared component via [[set()]], this method will return
     * the same component instance each time it is called.
     * If a component is not shared, this method will create a new instance every time.
     *
     * @param string $typeOrID component type (a fully qualified namespaced class/interface name, e.g. `yii\db\Connection`)
     * or ID (e.g. `db`). When a class/interface name is given, make sure it does NOT have a leading backslash.
     * @param boolean $throwException whether to throw an exception if `$typeOrID` is not registered with the container before.
     * @return object|null the component of the specified type or ID. If `$throwException` is false and `$typeOrID`
     * is not registered before, null will be returned.
     * @throws InvalidConfigException if `$typeOrID` refers to a nonexistent component ID
     * or if there is cyclic dependency detected
     * @see has()
     * @see set()
     */
    public function get($typeOrID, $throwException = true)
    {
        if (isset($this->_components[$typeOrID])) {
            return $this->_components[$typeOrID];
        }

        if (!isset($this->_definitions[$typeOrID])) {
            if (strpos($typeOrID, '\\') !== false) {
                // a class name
                return $this->buildComponent($typeOrID);
            } elseif (!$throwException) {
                return null;
            } else {
                throw new InvalidConfigException("Unknown component ID: $typeOrID");
            }
        }

        if (isset($this->_building[$typeOrID])) {
            throw new InvalidConfigException("A cyclic dependency of \"$typeOrID\" is detected.");
        }

        $this->_building[$typeOrID] = true;
        $definition = $this->_definitions[$typeOrID];
        if (is_string($definition)) {
            // a type or ID
            $component = $this->get($definition);
        } elseif ($definition instanceof Closure || is_array($definition) && isset($definition[0], $definition[1])) {
            // a PHP callable
            $component = call_user_func($definition, $typeOrID, $this);
        } elseif (is_object($definition)) {
            // an object
            $component = $definition;
        } else {
            // a configuration array
            $component = $this->buildComponent($definition);
        }
        unset($this->_building[$typeOrID]);

        if (array_key_exists($typeOrID, $this->_components)) {
            // a shared component
            $this->_components[$typeOrID] = $component;
        }

        return $component;
    }

    /**
     * Registers a component definition with this container.
     *
     * For example,
     *
     * ```php
     * // a shared component identified by a class name.
     * $container->set('yii\db\Connection', ['dsn' => '...']);
     *
     * // a non-shared component identified by a class name.
     * $container->set('*yii\db\Connection', ['dsn' => '...']);
     *
     * // a shared component identified by an interface.
     * $container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');
     *
     * // a shared component identified by an ID.
     * $container->set('db', ['class' => 'yii\db\Connection', 'dsn' => '...']);
     *
     * // a shared component defined by an anonymous function
     * $container->set('db', function ($container) {
     *     return new \yii\db\Connection;
     * });
     * ```
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
     * Note that when a class/interface name is given, make sure it does NOT have a leading backslash.
     *
     * @param mixed $definition the component definition to be registered with this container.
     * It can be one of the followings:
     *
     * - a PHP callable: either an anonymous function or an array representing a class method (e.g. `['Foo', 'bar']`).
     *   The callable will be called by [[get()]] to return an object associated with the specified component type.
     *   The signature of the function should be: `function ($type, $container)`, where
     *   `$type` is the type or ID of the component to be created, and `$container` is this container.
     * - an object: When [[get()]] is called, this object will be returned. No new object will be created.
     *   This essentially makes the component a shared one, regardless how it is specified in `$typeOrID`.
     * - a configuration array: the array contains name-value pairs that will be used to initialize the property
     *   values of the newly created object when [[get()]] is called. The `class` element stands for the
     *   the class of the object to be created. If `class` is not specified, `$typeOrID` will be used as the class name.
     * - a string: either a class name or a component ID that is registered with this container.
     *
     * If the parameter is null, the component definition will be removed from the container.
     * @throws InvalidConfigException if the definition is an invalid configuration array
     */
    public function set($typeOrID, $definition)
    {
        if ($notShared = $typeOrID[0] === '*') {
            $typeOrID = substr($typeOrID, 1);
        }

        if ($definition === null) {
            unset($this->_components[$typeOrID], $this->_definitions[$typeOrID]);
            return;
        }

        if (is_object($definition) || is_array($definition) && isset($definition[0], $definition[1])) {
            // an object or a PHP callable
            $this->_definitions[$typeOrID] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->_definitions[$typeOrID] = $definition;
            } elseif (strpos($typeOrID, '\\')) {
                $definition['class'] = $typeOrID;
                $this->_definitions[$typeOrID] = $definition;
            } else {
                throw new InvalidConfigException("The configuration for the \"$typeOrID\" component must contain a \"class\" element.");
            }
        } else {
            // a type or ID
            $this->_definitions[$typeOrID] = $definition;
        }

        if ($notShared) {
            unset($this->_components[$typeOrID]);
        } else {
            $this->_components[$typeOrID] = null;
        }
    }

    /**
     * Returns the list of the component definitions or the loaded shared component instances.
     * @param boolean $returnDefinitions whether to return component definitions or the loaded shared component instances.
     * @return array the list of the component definitions or the loaded shared component instances (type or ID => definition or instance).
     */
    public function getComponents($returnDefinitions = true)
    {
        return $returnDefinitions ? $this->_definitions : $this->_components;
    }

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
     * The following is an example for registering two component definitions:
     *
     * ~~~
     * [
     *     'db' => [
     *         'class' => 'yii\db\Connection',
     *         'dsn' => 'sqlite:path/to/file.db',
     *     ],
     *     'cache' => [
     *         'class' => 'yii\caching\DbCache',
     *         'db' => 'db',
     *     ],
     * ]
     * ~~~
     *
     * @param array $components component definitions or instances
     */
    public function setComponents($components)
    {
        foreach ($components as $typeOrID => $component) {
            $this->set($typeOrID, $component);
        }
    }

    /**
     * Builds a new component instance based on the given class name or configuration array.
     * This method is mainly called by [[get()]].
     * @param string|array $type a class name or configuration array
     * @return object the new component instance
     */
    protected function buildComponent($type)
    {
        return Yii::createObject($type);
    }
}
