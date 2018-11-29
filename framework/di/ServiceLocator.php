<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use Closure;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * ServiceLocator 实现 [service locator](http://en.wikipedia.org/wiki/Service_locator_pattern)。
 *
 * 要使用 ServiceLocator，
 * 首先需要通过调用 [[set()]] 或 [[setComponents()]] 向定位器注册具有相应组件定义的组件 IDs。
 * 然后你可以通过调用 [[get()]] 去检索具有指定 ID 的组件。
 * 定位器将根据定义自动实例化和配置组件。
 *
 * 例如，
 *
 * ```php
 * $locator = new \yii\di\ServiceLocator;
 * $locator->setComponents([
 *     'db' => [
 *         'class' => 'yii\db\Connection',
 *         'dsn' => 'sqlite:path/to/file.db',
 *     ],
 *     'cache' => [
 *         'class' => 'yii\caching\DbCache',
 *         'db' => 'db',
 *     ],
 * ]);
 *
 * $db = $locator->get('db');  // or $locator->db
 * $cache = $locator->get('cache');  // or $locator->cache
 * ```
 *
 * 因为 [[\yii\base\Module]] 从 ServiceLocator 继承，所以模型和应用程序都是服务定位器。
 * 模块添加 [tree traversal](guide:concept-service-locator#tree-traversal) 用于服务解析。
 *
 * 关于 ServiceLocator 更多的细节和用法，请参阅 [guide article on service locators](guide:concept-service-locator)。
 *
 * @property array $components 组件定义列表或加载的
 * 组件实例（ID => definition or instance）。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ServiceLocator extends Component
{
    /**
     * @var array 由 IDs 索引的共享主键实例
     */
    private $_components = [];
    /**
     * @var array 由 IDs 索引的组件定义
     */
    private $_definitions = [];


    /**
     * Getter 魔术方法。
     * 重写此方法以支持访问诸如读取属性之类的组件。
     * @param string $name 组件或属性名称
     * @return mixed 命名的属性值
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        return parent::__get($name);
    }

    /**
     * 检查属性值是否为 null。
     * 此方法通过检查是否已加载命名组件来覆盖父类的实现。
     * @param string $name 属性名或事件名
     * @return bool 是否属性名为 null
     */
    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * 返回一个值，该值表示定位器是否具有指定的组件定义或是否已实例化该组件。
     * 此方法根据 `$checkInstance` 的值返回不同的结果。
     *
     * - 如果 `$checkInstance` 为 false（default），
     *   此方法将返回一个表示定位器是否具有指定组件定义的值。
     * - 如果 `$checkInstance` 为 true，
     *   此方法会返回一个表示定位器是否已经实例化指定组件的值。
     *
     * @param string $id 组件 ID（e.g. `db`）。
     * @param bool $checkInstance 是否应检查组件是否已共享和实例化。
     * @return bool 是否定位器具有指定的组件定义或已实例化组件。
     * @see set()
     */
    public function has($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_components[$id]) : isset($this->_definitions[$id]);
    }

    /**
     * 返回具有指定 ID 的组件实例。
     *
     * @param string $id 组件 ID（e.g. `db`）。
     * @param bool $throwException 如果 `$id` 之前未在定位器中注册，是否抛出一个异常。
     * @return object|null 指定 ID 的组件。如果 `$throwException` 为 false 并且
     * `$id` 在之前没有被注册，将会返回 null。
     * @throws InvalidConfigException 如果 `$id` 为不存在的组件 ID 抛出的异常。
     * @see has()
     * @see set()
     */
    public function get($id, $throwException = true)
    {
        if (isset($this->_components[$id])) {
            return $this->_components[$id];
        }

        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->_components[$id] = $definition;
            }

            return $this->_components[$id] = Yii::createObject($definition);
        } elseif ($throwException) {
            throw new InvalidConfigException("Unknown component ID: $id");
        }

        return null;
    }

    /**
     * 用定位器注册一个组件。
     *
     * 例如，
     *
     * ```php
     * // 类名
     * $locator->set('cache', 'yii\caching\FileCache');
     *
     * // 配置数组
     * $locator->set('db', [
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // 匿名函数
     * $locator->set('cache', function ($params) {
     *     return new \yii\caching\FileCache;
     * });
     *
     * // 实例
     * $locator->set('cache', new \yii\caching\FileCache);
     * ```
     *
     * 如果具有相同 ID 的组件定义已经存在，则将覆盖它。
     *
     * @param string $id 组件 ID（e.g. `db`）。
     * @param mixed $definition 使用定位器注册的组件。
     * 它可以是以下之一：
     *
     * - 类名
     * - 配置数组：数组包含键值对，当调用 [[get()]] 时，
     *   将用于初始化新创建的对象的属性值。
     *   `class` 是必须的，代表要创建的对象的类。
     * - PHP 回调：匿名函数或表示类方法的数组（e.g. `['Foo', 'bar']`）。
     *   回调将会由 [[get()]] 调用，以返回与指定组件ID关联的对象。
     * - 对象：当调用 [[get()]] 时，将会返回对象。
     *
     * @throws InvalidConfigException 如果定义是无效的配置数组抛出的异常
     */
    public function set($id, $definition)
    {
        unset($this->_components[$id]);

        if ($definition === null) {
            unset($this->_definitions[$id]);
            return;
        }

        if (is_object($definition) || is_callable($definition, true)) {
            // 对象，类名或者是 PHP 回调。
            $this->_definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // 配置数组
            if (isset($definition['class'])) {
                $this->_definitions[$id] = $definition;
            } else {
                throw new InvalidConfigException("The configuration for the \"$id\" component must contain a \"class\" element.");
            }
        } else {
            throw new InvalidConfigException("Unexpected configuration type for the \"$id\" component: " . gettype($definition));
        }
    }

    /**
     * 从定位器移除组件。
     * @param string $id 组件 ID
     */
    public function clear($id)
    {
        unset($this->_definitions[$id], $this->_components[$id]);
    }

    /**
     * 返回组件定义列表或已加载的组件实例。
     * @param bool $returnDefinitions 是否返回组件定义而不是已加载的组件实例。
     * @return array the 组件定义列表或加载的组件实例（ID => definition or instance）。
     */
    public function getComponents($returnDefinitions = true)
    {
        return $returnDefinitions ? $this->_definitions : $this->_components;
    }

    /**
     * 在定位器中注册一组组件定义。
     *
     * 这是 [[set()]] 的批量版本。
     * 参数应该是一个数组，其键是组件 IDs，并且值是相应的组件定义。
     *
     * 有关如何指定组件 IDs 和定义的更多详细信息，请参阅 [[set()]]。
     *
     * 如果具有相同 ID 的组件定义已经存在，则将覆盖它。
     *
     * 以下是注册两个组件定义的示例：
     *
     * ```php
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
     * ```
     *
     * @param array $components 组件定义或实例
     */
    public function setComponents($components)
    {
        foreach ($components as $id => $component) {
            $this->set($id, $component);
        }
    }
}
