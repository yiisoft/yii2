<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use ReflectionClass;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Container 实现 [依赖注入](http://en.wikipedia.org/wiki/Dependency_injection) 容器。
 *
 * 依赖注入（DI）容器是一个知道如何实例化和配置对象
 * 以及其所有依赖对象的对象。想要了解更多关于 DI 的信息，请参考
 * [Martin Fowler 的文章](http://martinfowler.com/articles/injection.html)。
 *
 * Container 支持构造函数注入以及属性注入。
 *
 * 要使用 Container，首先需要通过调用 [[set()]] 来设置类依赖项。
 * 然后调用 [[get()]] 去创建一个新的类对象。
 * Container 将自动实例化依赖对象，将它们注入正在创建的对象，配置并且最终返回新创建的对象。
 *
 * 默认情况下，[[\Yii::$container]] 引用 [[\Yii::createObject()]] 来创建新对象实例的
 * Container 实例。在创建新对象时，
 * 您可以使用此方法替换 `new` 运算符，
 * 这将为您提供了自动依赖项解析和默认属性配置的便利。
 *
 * 下面是使用 Container 的一个例子：
 *
 * ```php
 * namespace app\models;
 *
 * use yii\base\BaseObject;
 * use yii\db\Connection;
 * use yii\di\Container;
 *
 * interface UserFinderInterface
 * {
 *     function findUser();
 * }
 *
 * class UserFinder extends BaseObject implements UserFinderInterface
 * {
 *     public $db;
 *
 *     public function __construct(Connection $db, $config = [])
 *     {
 *         $this->db = $db;
 *         parent::__construct($config);
 *     }
 *
 *     public function findUser()
 *     {
 *     }
 * }
 *
 * class UserLister extends BaseObject
 * {
 *     public $finder;
 *
 *     public function __construct(UserFinderInterface $finder, $config = [])
 *     {
 *         $this->finder = $finder;
 *         parent::__construct($config);
 *     }
 * }
 *
 * $container = new Container;
 * $container->set('yii\db\Connection', [
 *     'dsn' => '...',
 * ]);
 * $container->set('app\models\UserFinderInterface', [
 *     'class' => 'app\models\UserFinder',
 * ]);
 * $container->set('userLister', 'app\models\UserLister');
 *
 * $lister = $container->get('userLister');
 *
 * // which is equivalent to:
 *
 * $db = new \yii\db\Connection(['dsn' => '...']);
 * $finder = new UserFinder($db);
 * $lister = new UserLister($finder);
 * ```
 *
 * 想要获取更多关于 Container 的细节和用法，请参阅 [guide article on di-containers](guide:concept-di-container)。
 *
 * @property array $definitions 对象定义列表或加载共享的对象（type or ID => definition or instance）。
 * 这个属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Container extends Component
{
    /**
     * @var array 由自身类型索引的单例对象
     */
    private $_singletons = [];
    /**
     * @var array 对象定义由其类型索引
     */
    private $_definitions = [];
    /**
     * @var array 由对象类型索引的构造函数参数
     */
    private $_params = [];
    /**
     * @var array 由类/接口名称索引的高速缓存反射类对象
     */
    private $_reflections = [];
    /**
     * @var array 由类/接口名称索引的缓存依赖项。
     * 每个类名都与构造函数参数类型或默认值的列表相关联。
     */
    private $_dependencies = [];


    /**
     * 返回所请求类的实例。
     *
     * 你可以提供构造函数参数 (`$params`) 和对象配置 (`$config`)，
     * 这些参数将在创建实例期间使用。
     *
     * 如果类实现 [[\yii\base\Configurable]]，则 `$config` 参数将作为最后一个参数
     * 传递给构造函数；否则，
     * 配置将在对象被实例化 *之后* 被应用。
     *
     * 注意如果通过调用 [[setSingleton()]] 将类声明为单例，
     * 则每次调用此方法时都将返回该类的相同实例。
     * 在这种情况下，
     * 只有在第一次实例化类时，才会使用构造函数参数和对象配置。
     *
     * @param string $class 先前通过 [[set()]] 或
     * [[setSingleton()]] 注册的类名或别名（e.g. `foo`）。
     * @param array $params 构造函数参数值列表。
     * 参数应该按照它们在构造函数声明中出现的顺序提供。
     * 如果你想略过某些参数，你应该将剩下的索引用整数表示它们在构造函数参数列表中的位置。
     * @param array $config 将用于初始化对象属性的名键值对的列表。
     * @return object 请求类的实例。
     * @throws InvalidConfigException 如果类不能识别或对应于无效定抛出的异常
     * @throws NotInstantiableException 从 2.0.9 版本开始，如果解析为抽象类或接口抛出的异常
     */
    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            return $this->_singletons[$class];
        } elseif (!isset($this->_definitions[$class])) {
            return $this->build($class, $params, $config);
        }

        $definition = $this->_definitions[$class];

        if (is_callable($definition, true)) {
            $params = $this->resolveDependencies($this->mergeParams($class, $params));
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];
            unset($definition['class']);

            $config = array_merge($definition, $config);
            $params = $this->mergeParams($class, $params);

            if ($concrete === $class) {
                $object = $this->build($class, $params, $config);
            } else {
                $object = $this->get($concrete, $params, $config);
            }
        } elseif (is_object($definition)) {
            return $this->_singletons[$class] = $definition;
        } else {
            throw new InvalidConfigException('Unexpected object definition type: ' . gettype($definition));
        }

        if (array_key_exists($class, $this->_singletons)) {
            // singleton
            $this->_singletons[$class] = $object;
        }

        return $object;
    }

    /**
     * 使用容器注册类的定义。
     *
     * 例如：
     *
     * ```php
     * // 按原样注册一个类名。这可以跳过。
     * $container->set('yii\db\Connection');
     *
     * // 注册一个接口
     * // 当一个类依赖于接口时，
     * // 相应的类将被实例化为依赖对象
     * $container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');
     *
     * // 注册一个别名。你可以使用 $container->get('foo')
     * // 去创建连接实例。
     * $container->set('foo', 'yii\db\Connection');
     *
     * // 通过配置来注册一个类。
     * // 当类通过 get() 来实例化时，将应用此配置
     * $container->set('yii\db\Connection', [
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // 使用类配置注册别名
     * // 在这种情况下，需要用 "class" 来指定类
     * $container->set('db', [
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // 注册一个 PHP 回调
     * // 当 $container->get('db') 被调用时，回调将会执行
     * $container->set('db', function ($container, $params, $config) {
     *     return new \yii\db\Connection($config);
     * });
     * ```
     *
     * 如果已经存在具有相同名称的类定义，则将使用新的类定义覆盖它。
     * 你可以使用 [[has()]] 去检查是否类的定义已经存在。
     *
     * @param string $class 类名，接口名或别名
     * @param mixed $definition 与 `$class`相关的定义。它可以是下列之一：
     *
     * - PHP 回调：当 [[get()]] 被触发时将会执行这个回调。
     *   回调的方法应该是 `function ($container, $params, $config)`，其中 `$params` 表示构造函数的参数列表，
     *  `$config` 是对象的配置，`$container` 是容器对象。
     *   回调函数的返回值将由 [[get()]] 作为请求的对象实例返回。
     * - 配置数组：数组包含的键值对用在调用 [[get()]] 时初始化新创建的对象的属性值。
     *   `class` 元素代表要创建对象的类。
     *   如果 `class` 没有指定，`$class` 将做为类名。
     * - 字符串：一个类名，一个接口名或者一别名。
     * @param array $params 构造函数参数列表。
     * 调用 [[get()]] 时，参数将传递给类的构造函数。
     * @return $this 容器本身
     */
    public function set($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        unset($this->_singletons[$class]);
        return $this;
    }

    /**
     * 使用容器注册类定义并将该类标记为单例类。
     *
     * 这个方法跟 [[set()]] 相似，除了通过此方法注册的类只有一个实例。
     * 每次 [[get()]] 被调用时，将返回指定类的相同实例。
     *
     * @param string $class 类名，接口名或别名
     * @param mixed $definition 与 `$class` 相关的定义。查看关于 [[set()]] 更多的细节。
     * @param array $params 构造函数参数的列表。
     * 当 [[get()]] 被调用时，参数将传递给类的构造函数。
     * @return $this 容器本身
     * @see set()
     */
    public function setSingleton($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        $this->_singletons[$class] = null;
        return $this;
    }

    /**
     * 返回一个指示容器是否具有指定名称的定义的值。
     * @param string $class 类名，接口名或别名
     * @return bool whether 容器是否有具有指定名称的定义.
     * @see set()
     */
    public function has($class)
    {
        return isset($this->_definitions[$class]);
    }

    /**
     * 返回一个指示给定名称是否对应于已注册的单例的值。
     * @param string $class 类名，接口名或别名。
     * @param bool $checkInstance 是否检查单例是否已实例化。
     * @return bool 给定名称是否已注册了单例。
     * 如果 `$checkInstance` 为真，该方法应返回一个值，表明单例是否已实例化。
     */
    public function hasSingleton($class, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_singletons[$class]) : array_key_exists($class, $this->_singletons);
    }

    /**
     * 删除指定名称的定义。
     * @param string $class 类名，接口名或者别名。
     */
    public function clear($class)
    {
        unset($this->_definitions[$class], $this->_singletons[$class]);
    }

    /**
     * 规范化类定义。
     * @param string $class 类名
     * @param string|array|callable $definition 类的定义
     * @return array 规范化的类定义
     * @throws InvalidConfigException 如果定义无效抛出的异常。
     */
    protected function normalizeDefinition($class, $definition)
    {
        if (empty($definition)) {
            return ['class' => $class];
        } elseif (is_string($definition)) {
            return ['class' => $definition];
        } elseif (is_callable($definition, true) || is_object($definition)) {
            return $definition;
        } elseif (is_array($definition)) {
            if (!isset($definition['class'])) {
                if (strpos($class, '\\') !== false) {
                    $definition['class'] = $class;
                } else {
                    throw new InvalidConfigException('A class definition requires a "class" member.');
                }
            }

            return $definition;
        }

        throw new InvalidConfigException("Unsupported definition type for \"$class\": " . gettype($definition));
    }

    /**
     * 返回对象定义列表或加载的共享对象。
     * @return array 对象定义列表或加载的共享对象（type or ID => definition or instance）。
     */
    public function getDefinitions()
    {
        return $this->_definitions;
    }

    /**
     * 创建指定类的实例。
     * 此方法将解析指定类的依赖关系，实例化它们，
     * 并且将它们注入到指定类的新实例中。
     * @param string $class 类名
     * @param array $params 构造函数的参数
     * @param array $config 引用于新实例的配置
     * @return object 新创建的指定类的实例
     * @throws NotInstantiableException 从 2.0.9 版本开始，如果解析为抽象类或接口抛出的异常
     */
    protected function build($class, $params, $config)
    {
        /* @var $reflection ReflectionClass */
        list($reflection, $dependencies) = $this->getDependencies($class);

        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }

        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (!$reflection->isInstantiable()) {
            throw new NotInstantiableException($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }

        $config = $this->resolveDependencies($config);

        if (!empty($dependencies) && $reflection->implementsInterface('yii\base\Configurable')) {
            // 将 $config 设置为最后一个参数（现有的参数将会被覆盖）
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        }

        $object = $reflection->newInstanceArgs($dependencies);
        foreach ($config as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * 将用户指定的构造函数的参数和通过 [[set()]] 注册的参数合并。
     * @param string $class 类名，接口名或别名
     * @param array $params 构造函数的参数
     * @return array 合并后的参数
     */
    protected function mergeParams($class, $params)
    {
        if (empty($this->_params[$class])) {
            return $params;
        } elseif (empty($params)) {
            return $this->_params[$class];
        }

        $ps = $this->_params[$class];
        foreach ($params as $index => $value) {
            $ps[$index] = $value;
        }

        return $ps;
    }

    /**
     * 返回指定类的依赖项。
     * @param string $class 类名，接口名或别名
     * @return array 指定类的依赖关系。
     * @throws InvalidConfigException 如果无法解决依赖关系或无法实现依赖关系抛出的异常。
     */
    protected function getDependencies($class)
    {
        if (isset($this->_reflections[$class])) {
            return [$this->_reflections[$class], $this->_dependencies[$class]];
        }

        $dependencies = [];
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new InvalidConfigException('Failed to instantiate component or class "' . $class . '".', 0, $e);
        }

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if (version_compare(PHP_VERSION, '5.6.0', '>=') && $param->isVariadic()) {
                    break;
                } elseif ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }

        $this->_reflections[$class] = $reflection;
        $this->_dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }

    /**
     * 通过将依赖项替换为实际对象实例来解析依赖关系。
     * @param array $dependencies 依赖关系
     * @param ReflectionClass $reflection 与依赖关联的类反射
     * @return array 已解决的依赖项
     * @throws InvalidConfigException 如果无法解决依赖关系或无法实现依赖关系抛出的异常。
     */
    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new InvalidConfigException("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }

        return $dependencies;
    }

    /**
     * 通过解析参数中的依赖项来调用回调。
     *
     * 此方法允许调用回调并将类型提示的参数名称解析为 Container 的对象。
     * 它还允许使用命名参数调用函数。
     *
     * 例如，可以使用 Container 调用以下回调来解析格式化程序依赖项：
     *
     * ```php
     * $formatString = function($string, \yii\i18n\Formatter $formatter) {
     *    // ...
     * }
     * Yii::$container->invoke($formatString, ['string' => 'Hello World!']);
     * ```
     *
     * 这将传递字符串 `'Hello World!'` 作为第一个参数，
     * 以及由 DI 创建的格式化程序实例作为回调的第二个参数。
     *
     * @param callable $callback 需要调用的回调。
     * @param array $params 函数的参数数组。
     * 这可以是参数列表，也可以是表示命名函数参数的关联数组。
     * @return mixed 回调返回的值。
     * @throws InvalidConfigException 如果无法解决依赖关系或无法实现依赖关系抛出的异常。
     * @throws NotInstantiableException 从 2.0.9 版本开始，如果解析为抽象类或接口抛出的异常
     * @since 2.0.7
     */
    public function invoke(callable $callback, $params = [])
    {
        return call_user_func_array($callback, $this->resolveCallableDependencies($callback, $params));
    }

    /**
     * 解决函数的依赖关系。
     *
     * 此方法可用于实现其他组件中 [[invoke()]]
     * 提供的类似功能。
     *
     * @param callable $callback 可调用的回调函数。
     * @param array $params 函数的参数数组可以是数字的，也可以是相联的。
     * @return array 已解决的依赖项。
     * @throws InvalidConfigException 如果不能解决依赖关系，或者不能满足依赖关系抛出的异常。
     * @throws NotInstantiableException 从 2.0.9 版本开始，如果解析为抽象类或接口抛出的异常
     * @since 2.0.7
     */
    public function resolveCallableDependencies(callable $callback, $params = [])
    {
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (is_object($callback) && !$callback instanceof \Closure) {
            $reflection = new \ReflectionMethod($callback, '__invoke');
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $args = [];

        $associative = ArrayHelper::isAssociative($params);

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (($class = $param->getClass()) !== null) {
                $className = $class->getName();
                if (version_compare(PHP_VERSION, '5.6.0', '>=') && $param->isVariadic()) {
                    $args = array_merge($args, array_values($params));
                    break;
                } elseif ($associative && isset($params[$name]) && $params[$name] instanceof $className) {
                    $args[] = $params[$name];
                    unset($params[$name]);
                } elseif (!$associative && isset($params[0]) && $params[0] instanceof $className) {
                    $args[] = array_shift($params);
                } elseif (isset(Yii::$app) && Yii::$app->has($name) && ($obj = Yii::$app->get($name)) instanceof $className) {
                    $args[] = $obj;
                } else {
                    // 如果参数是可选的，捕获不可实例化的异常
                    try {
                        $args[] = $this->get($className);
                    } catch (NotInstantiableException $e) {
                        if ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } else {
                            throw $e;
                        }
                    }
                }
            } elseif ($associative && isset($params[$name])) {
                $args[] = $params[$name];
                unset($params[$name]);
            } elseif (!$associative && count($params)) {
                $args[] = array_shift($params);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } elseif (!$param->isOptional()) {
                $funcName = $reflection->getName();
                throw new InvalidConfigException("Missing required parameter \"$name\" when calling \"$funcName\".");
            }
        }

        foreach ($params as $value) {
            $args[] = $value;
        }

        return $args;
    }

    /**
     * 在容器中注册类定义。
     *
     * @param array $definitions 定义数组。有两种允许的数组格式。
     * 第一种格式：
     *  - key：类名，接口名或别名。
     *    键将作为第一个参数 `$class` 来传递给 [[set()]] 方法。
     *  - value：与 `$class` 相关的定义。
     *    在 [[set()]] 文档中描述了关于 `$definition` 参数可能的值。
     *    将作为第二个参数 `$definition` 传递给 [[set()]]。
     *
     * 例如：
     * ```php
     * $container->setDefinitions([
     *     'yii\web\Request' => 'app\components\Request',
     *     'yii\web\Response' => [
     *         'class' => 'app\components\Response',
     *         'format' => 'json'
     *     ],
     *     'foo\Bar' => function () {
     *         $qux = new Qux;
     *         $foo = new Foo($qux);
     *         return new Bar($foo);
     *     }
     * ]);
     * ```
     *
     * 第二种格式：
     *  - key：类名，接口名或别名。
     *    键将作为第一个参数 `$class` 来传递给 [[set()]] 方法。
     *  - value：两个元素的数组。
     *    第一个元素将作为第二个参数 `$definition` 传递给 [[set()]] 方法，第二个元素 — 作为 `$params`。
     *
     * 例如：
     * ```php
     * $container->setDefinitions([
     *     'foo\Bar' => [
     *          ['class' => 'app\Bar'],
     *          [Instance::of('baz')]
     *      ]
     * ]);
     * ```
     *
     * @see set() 了解更多关于定义可能的值
     * @since 2.0.11
     */
    public function setDefinitions(array $definitions)
    {
        foreach ($definitions as $class => $definition) {
            if (is_array($definition) && count($definition) === 2 && array_values($definition) === $definition) {
                $this->set($class, $definition[0], $definition[1]);
                continue;
            }

            $this->set($class, $definition);
        }
    }

    /**
     * 通过调用 [[setSingleton()]] 将类定义注册为容器的单例
     *
     * @param array $singletons 定义单例的数组。
     * 有关允许的数组格式，请参阅 [[setDefinitions()]]。
     *
     * @see setDefinitions() 允许格式的 $singletons 参数
     * @see setSingleton() 了解更多关于定义可能的值
     * @since 2.0.11
     */
    public function setSingletons(array $singletons)
    {
        foreach ($singletons as $class => $definition) {
            if (is_array($definition) && count($definition) === 2 && array_values($definition) === $definition) {
                $this->setSingleton($class, $definition[0], $definition[1]);
                continue;
            }

            $this->setSingleton($class, $definition);
        }
    }
}
