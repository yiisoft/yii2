<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii;

use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\di\Container;
use yii\log\Logger;

/**
 * 获取应用程序开始的时间戳。
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
/**
 * 此常量定义框架安装目录。
 */
defined('YII2_PATH') or define('YII2_PATH', __DIR__);
/**
 * 此常量定义应用程序是否应处于调试模式。默认为 false。
 */
defined('YII_DEBUG') or define('YII_DEBUG', false);
/**
 * 此常量定义应用程序在哪个环境中运行。默认为 'prod'，表示生产环境。
 * 您可以在引导脚本中定义此常量。值可以是 'prod'（生产），'dev'（开发），'test'，'staging' 等。
 */
defined('YII_ENV') or define('YII_ENV', 'prod');
/**
 * 应用程序是否在生产环境中运行。
 */
defined('YII_ENV_PROD') or define('YII_ENV_PROD', YII_ENV === 'prod');
/**
 * 应用程序是否在开发环境中运行。
 */
defined('YII_ENV_DEV') or define('YII_ENV_DEV', YII_ENV === 'dev');
/**
 * 应用程序是否在测试环境中运行。
 */
defined('YII_ENV_TEST') or define('YII_ENV_TEST', YII_ENV === 'test');

/**
 * 此常量定义是否应启用错误处理。默认为 true。
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);

/**
 * BaseYii 是 Yii 框架的核心助手类。
 *
 * 不要直接使用 BaseYii。
 * 相反，使用它的子类 [[\Yii]] 来自定义 BaseYii 的方法。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseYii
{
    /**
     * @var array Yii 自动加载机制使用的类映射。
     * 数组键是类名（没有前导反斜杠），
     * 数组值是相应的类文件路径（或 [路径别名](guide:concept-aliases))。
     * 此属性主要影响 [[autoload()]] 的工作方式。
     * @see autoload()
     */
    public static $classMap = [];
    /**
     * @var \yii\console\Application|\yii\web\Application 应用程序实例
     */
    public static $app;
    /**
     * @var array 注册路径别名
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = ['@yii' => __DIR__];
    /**
     * @var Container [[createObject()]] 使用的依赖注入（DI）容器。
     * 您可以使用 [[Container::set()]]
     * 来设置类及其初始属性值所需的依赖项。
     * @see createObject()
     * @see Container
     */
    public static $container;


    /**
     * 返回表示 Yii 框架当前版本的字符串。
     * @return string Yii 框架的版本
     */
    public static function getVersion()
    {
        return '2.0.16-dev';
    }

    /**
     * 将路径别名转换为实际路径。
     *
     * 翻译按照以下步骤完成：
     *
     * 1. 如果给定的别名不以 '@' 开头，则返回时不做更改；
     * 2. 否则，查找与给定别名的开头部分匹配的最长注册别名。
     *    如果存在，
     *    请将给定别名的匹配部分替换为相应的注册路径。
     * 3. 抛出异常或返回 false，具体取决于 `$throwException` 参数。
     *
     * 例如，默认情况下，'@yii' 被注册为 Yii 框架目录的别名，例如 '/path/to/yii'。
     * 别名 '@yii/web' 将被翻译成 '/path/to/yii/web'。
     *
     * 如果你已经注册了两个别名 '@foo' 和 '@foo/bar'。然后翻译 '@foo/bar/config'
     * 会将部分 '@foo/bar'（而不是 '@foo'）替换为相应的注册路径。
     * 这是因为最长的别名优先。
     *
     * 但是，如果要翻译的别名是 '@foo/barbar/config'，那么 '@foo' 将被替换而不是 '@foo/bar'，
     * 因为 '/' 用作边界字符。
     *
     * 注意，此方法不检查返回的路径是否存在。
     *
     * 有关详细信息，请参阅[别名的指南文章](guide:concept-aliases)。
     *
     * @param string $alias 要翻译的别名。
     * @param bool $throwException 如果给定的别名无效，是否抛出异常。
     * 如果为 false 并且给出了无效的别名，则此方法将返回 false。
     * @return string|bool 与别名对应的路径，如果先前未注册根别名，则为 false。
     * @throws InvalidArgumentException 如果 $throwException 为 true 时别名无效。
     * @see setAlias()
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $path . substr($alias, strlen($name));
                }
            }
        }

        if ($throwException) {
            throw new InvalidArgumentException("Invalid path alias: $alias");
        }

        return false;
    }

    /**
     * 返回给定别名的根别名部分。
     * 根别名是先前通过 [[setAlias()]] 注册的别名。
     * 如果给定的别名与多个根别名匹配，则将返回最长的别名。
     * @param string $alias 别名
     * @return string|bool 根别名，如果没有找到根别名则为 false
     */
    public static function getRootAlias($alias)
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $root;
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $name;
                }
            }
        }

        return false;
    }

    /**
     * 注册路径别名。
     *
     * 路径别名是表示长路径的短名称（文件路径，URL 等）
     * 例如，我们使用 '@yii' 作为 Yii 框架目录路径的别名。
     *
     * 路径别名必须以字符“@”开头，
     * 以便可以轻松区分非别名路径。
     *
     * 请注意，此方法不检查给定路径是否存在。
     * 它所做的只是将别名与路径相关联。
     *
     * 给定路径中的任何结尾的 '/' 和 '\' 字符都将被截取。
     *
     * 有关详细信息，请参阅[别名的指南文章](guide:concept-aliases)。
     *
     * @param string $alias 别名（例如“@yii”）。它必须以 '@' 字符开头。
     * 它可能包含正斜杠 '/'，
     * 它在 [[getAlias()]] 执行别名转换时用作边界字符。
     * @param string $path 别名对应的路径。如果为 null，则将删除别名。
     * 结尾的 '/' 和 '\' 字符将被截取。可以是
     *
     * - 目录或文件路径（例如 `/tmp`，`/tmp/main.txt`）
     * - 一个URL（例如 `http://www.yiiframework.com`）
     * - 路径别名（例如 `@yii/base`）。 
     *   在这种情况下，路径别名将首先通过调用 [[getAlias()]] 转换为实际路径。
     *
     * @throws InvalidArgumentException 如果 $path 是无效的别名。
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * 类自动加载器。
     *
     * 当 PHP 发现一个未知类时，会自动调用此方法。
     * 该方法将尝试根据以下过程包含类文件：
     *
     * 1. 在 [[classMap]] 中搜索；
     * 2. 如果是带命名空间的类（例如 `yii\base\Component`），
     *    它将尝试包含与相应路径别名相关联的文件
     *    （例如 `@yii/base/Component.php`）；
     *
     * 此自动加载器允许加载遵循 [PSR-4 标准](http://www.php-fig.org/psr/psr-4/)
     * 的类，并将其顶级命名空间或子命名空间定义为路径别名。
     *
     * 示例：当定义别名 `@yii` 和 `@yii/bootstrap` 时，将使用 `yii\bootstrap`
     * 别名加载 `@yii/bootstrap` 命名空间中的类，
     * 该别名指向安装引导程序扩展文件的目录 并且将从 yii 框架目录加载来自其他 `yii` 命名空间的所有类。

     *
     * 还有[自动加载指南](guide:concept-autoloading)。
     *
     * @param string $className 没有前导反斜杠“\”的完全限定类名
     * @throws UnknownClassException 如果类文件中不存在该类
     */
    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include $classFile;

        if (YII_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    /**
     * 使用给定配置创建新对象。
     *
     * 您可以将此方法视为 `new` 运算符的增强版本。
     * 该方法支持基于类名，
     * 配置数组或匿名函数创建对象。
     *
     * 以下是一些使用示例：
     *
     * ```php
     * // 使用类名创建对象
     * $object = Yii::createObject('yii\db\Connection');
     *
     * // 使用配置数组创建对象
     * $object = Yii::createObject([
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // 使用两个构造函数参数创建一个对象
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * 使用 [[\yii\di\Container|依赖注入容器]]，此方法还可以识别依赖对象，
     * 实例化它们并将它们注入新创建的对象。
     *
     * @param string|array|callable $type 对象类型。可以使用以下形式之一指定：
     *
     * - 一个字符串：表示要创建的对象的类名
     * - 配置数组：数组必须包含一个被视为对象类的 `class` 元素，
     *   其余的键值对将用于初始化相应的对象属性
     * - PHP可调用：要么是匿名函数，要么是表示类方法的数组（`[$class 或 $object, $method]`）。
     *   callable 应返回正在创建的对象的新实例。
     *
     * @param array $params 构造函数参数
     * @return object 创建的对象
     * @throws InvalidConfigException 如果配置无效。
     * @see \yii\di\Container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
    }

    private static $_logger;

    /**
     * @return Logger 消息记录器
     */
    public static function getLogger()
    {
        if (self::$_logger !== null) {
            return self::$_logger;
        }

        return self::$_logger = static::createObject('yii\log\Logger');
    }

    /**
     * 设置记录器对象。
     * @param Logger $logger 记录器对象。
     */
    public static function setLogger($logger)
    {
        self::$_logger = $logger;
    }

    /**
     * 记录调试消息。
     * 跟踪消息主要用于开发目的，
     * 以查看某些代码的执行工作流程。
     * 此方法仅在应用程序处于调试模式时记录消息。
     * @param string|array $message 要记录的消息。
     * 这可以是简单的字符串或更复杂的数据结构，例如数组。
     * @param string $category 消息的类别。
     * @since 2.0.14
     */
    public static function debug($message, $category = 'application')
    {
        if (YII_DEBUG) {
            static::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
        }
    }

    /**
     * [[debug()]] 的别名。
     * @param string|array $message 要记录的消息。
     * 这可以是简单的字符串或更复杂的数据结构，例如数组。
     * @param string $category 消息的类别。
     * @deprecated 从 2.0.14 开始。请改用 [[debug()]]。
     */
    public static function trace($message, $category = 'application')
    {
        static::debug($message, $category);
    }

    /**
     * 记录错误消息。
     * 在执行应用程序期间发生不可恢复的错误时，
     * 通常会记录错误消息。
     * @param string|array $message 要记录的消息。
     * 这可以是简单的字符串或更复杂的数据结构，例如数组。
     * @param string $category 消息的类别。
     */
    public static function error($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
    }

    /**
     * 记录警告消息。
     * 当执行仍然可以继续时发生错误时，
     * 通常会记录警告消息。
     * @param string|array $message 要记录的消息。
     * 这可以是简单的字符串或更复杂的数据结构，例如数组。
     * @param string $category 消息的类别。
     */
    public static function warning($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
    }

    /**
     * 记录信息性消息。
     * 通常由应用程序记录信息性消息以保持重要事件的记录
     * （例如，管理员登录）。
     * @param string|array $message 要记录的消息。
     * 这可以是简单的字符串或更复杂的数据结构，例如数组。
     * @param string $category 消息的类别。
     */
    public static function info($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_INFO, $category);
    }

    /**
     * 标记代码块的开头以进行性能分析。
     *
     * 这必须与具有相同类别名称的 [[endProfile]] 调用相匹配。
     * 开始和结束调用也必须正确嵌套。例如，
     *
     * ```php
     * \Yii::beginProfile('block1');
     * // 一些要分析的代码
     *     \Yii::beginProfile('block2');
     *     // 一些其他要分析的代码
     *     \Yii::endProfile('block2');
     * \Yii::endProfile('block1');
     * ```
     * @param string $token 代码块的标记
     * @param string $category 此日志消息的类别
     * @see endProfile()
     */
    public static function beginProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
    }

    /**
     * 标记代码块的结尾以进行性能分析。
     * 这必须与先前使用相同类别名称的 [[beginProfile]] 调用相匹配。
     * @param string $token 代码块的标记
     * @param string $category 此日志消息的类别
     * @see beginProfile()
     */
    public static function endProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
    }

    /**
     * 返回可显示在网页上的 HTML 超链接，其中显示“Powered by Yii Framework”的信息。
     * @return string 可以在网页上显示“Powered by Yii Framework”信息的 HTML 超链接
     * @deprecated 从 2.0.14 开始，此方法将在 2.1.0 中删除。
     */
    public static function powered()
    {
        return \Yii::t('yii', 'Powered by {yii}', [
            'yii' => '<a href="http://www.yiiframework.com/" rel="external">' . \Yii::t('yii',
                    'Yii Framework') . '</a>',
        ]);
    }

    /**
     * 将信息转换为指定的语言。
     *
     * 这是 [[\yii\i18n\I18N::translate()]] 的快捷方法。
     *
     * 翻译将根据消息类别进行，并将使用目标语言。
     *
     * 您可以将参数添加到翻译消息中，该翻译消息将在翻译后替换为相应的值。
     * 这种格式是在参数名称前后使用大括号，如下例所示：
     *
     * ```php
     * $username = 'Alexander';
     * echo \Yii::t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * 使用 [PHP intl 扩展](http://www.php.net/manual/en/intro.intl.php) 消息格式化程序支持进一步格式化消息参数。
     * 有关详细信息，请参见 [[\yii\i18n\I18N::translate()]]。
     *
     * @param string $category 消息类别。
     * @param string $message 要翻译的信息。
     * @param array $params 将用于替换消息中相应占位符的参数。
     * @param string $language 语言代码（例如 `en-US`，`en`）。
     * 如果为 null，则将使用当前 [[\yii\base\Application::language|application language]]。
     * @return string 翻译的消息。
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$app !== null) {
            return static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

    /**
     * 使用初始属性值配置对象。
     * @param object $object 要配置的对象
     * @param array $properties 以键值对的形式给出属性的初始值。
     * @return object 对象本身
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * 返回对象的公共成员变量。
     * 提供此方法使得我们可以获取对象的公共成员变量。
     * 它与“get_object_vars()”
     * 不同，因为如果在对象本身内调用它，后者将返回 private 和 protected 变量。
     * @param object $object 要处理的对象
     * @return array 对象的公共成员变量
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }
}
