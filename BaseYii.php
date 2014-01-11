<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\UnknownClassException;
use yii\log\Logger;

/**
 * Gets the application start timestamp.
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
/**
 * This constant defines the framework installation directory.
 */
defined('YII_PATH') or define('YII_PATH', __DIR__);
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') or define('YII_DEBUG', false);
/**
 * This constant defines in which environment the application is running. Defaults to 'prod', meaning production environment.
 * You may define this constant in the bootstrap script. The value could be 'prod' (production), 'dev' (development), 'test', 'staging', etc.
 */
defined('YII_ENV') or define('YII_ENV', 'prod');
/**
 * Whether the the application is running in production environment
 */
defined('YII_ENV_PROD') or define('YII_ENV_PROD', YII_ENV === 'prod');
/**
 * Whether the the application is running in development environment
 */
defined('YII_ENV_DEV') or define('YII_ENV_DEV', YII_ENV === 'dev');
/**
 * Whether the the application is running in testing environment
 */
defined('YII_ENV_TEST') or define('YII_ENV_TEST', YII_ENV === 'test');

/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);


/**
 * BaseYii is the core helper class for the Yii framework.
 *
 * Do not use BaseYii directly. Instead, use its child class [[\Yii]] where
 * you can customize methods of BaseYii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseYii
{
	/**
	 * @var array class map used by the Yii autoloading mechanism.
	 * The array keys are the class names (without leading backslashes), and the array values
	 * are the corresponding class file paths (or path aliases). This property mainly affects
	 * how [[autoload()]] works.
	 * @see autoload()
	 */
	public static $classMap = [];
	/**
	 * @var \yii\console\Application|\yii\web\Application the application instance
	 */
	public static $app;
	/**
	 * @var array registered path aliases
	 * @see getAlias()
	 * @see setAlias()
	 */
	public static $aliases = ['@yii' => __DIR__];
	/**
	 * @var array initial property values that will be applied to objects newly created via [[createObject]].
	 * The array keys are class names without leading backslashes "\", and the array values are the corresponding
	 * name-value pairs for initializing the created class instances. For example,
	 *
	 * ~~~
	 * [
	 *     'Bar' => [
	 *         'prop1' => 'value1',
	 *         'prop2' => 'value2',
	 *     ],
	 *     'mycompany\foo\Car' => [
	 *         'prop1' => 'value1',
	 *         'prop2' => 'value2',
	 *     ],
	 * ]
	 * ~~~
	 *
	 * @see createObject()
	 */
	public static $objectConfig = [];


	/**
	 * @return string the version of Yii framework
	 */
	public static function getVersion()
	{
		return '2.0.0-dev';
	}

	/**
	 * Translates a path alias into an actual path.
	 *
	 * The translation is done according to the following procedure:
	 *
	 * 1. If the given alias does not start with '@', it is returned back without change;
	 * 2. Otherwise, look for the longest registered alias that matches the beginning part
	 *    of the given alias. If it exists, replace the matching part of the given alias with
	 *    the corresponding registered path.
	 * 3. Throw an exception or return false, depending on the `$throwException` parameter.
	 *
	 * For example, by default '@yii' is registered as the alias to the Yii framework directory,
	 * say '/path/to/yii'. The alias '@yii/web' would then be translated into '/path/to/yii/web'.
	 *
	 * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
	 * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
	 * This is because the longest alias takes precedence.
	 *
	 * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
	 * instead of '@foo/bar', because '/' serves as the boundary character.
	 *
	 * Note, this method does not check if the returned path exists or not.
	 *
	 * @param string $alias the alias to be translated.
	 * @param boolean $throwException whether to throw an exception if the given alias is invalid.
	 * If this is false and an invalid alias is given, false will be returned by this method.
	 * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
	 * @throws InvalidParamException if the alias is invalid while $throwException is true.
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
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $path . substr($alias, strlen($name));
					}
				}
			}
		}

		if ($throwException) {
			throw new InvalidParamException("Invalid path alias: $alias");
		} else {
			return false;
		}
	}

	/**
	 * Returns the root alias part of a given alias.
	 * A root alias is an alias that has been registered via [[setAlias()]] previously.
	 * If a given alias matches multiple root aliases, the longest one will be returned.
	 * @param string $alias the alias
	 * @return string|boolean the root alias, or false if no root alias is found
	 */
	public static function getRootAlias($alias)
	{
		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);

		if (isset(static::$aliases[$root])) {
			if (is_string(static::$aliases[$root])) {
				return $root;
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $name;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Registers a path alias.
	 *
	 * A path alias is a short name representing a long path (a file path, a URL, etc.)
	 * For example, we use '@yii' as the alias of the path to the Yii framework directory.
	 *
	 * A path alias must start with the character '@' so that it can be easily differentiated
	 * from non-alias paths.
	 *
	 * Note that this method does not check if the given path exists or not. All it does is
	 * to associate the alias with the path.
	 *
	 * Any trailing '/' and '\' characters in the given path will be trimmed.
	 *
	 * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
	 * It may contain the forward slash '/' which serves as boundary character when performing
	 * alias translation by [[getAlias()]].
	 * @param string $path the path corresponding to the alias. Trailing '/' and '\' characters
	 * will be trimmed. This can be
	 *
	 * - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
	 * - a URL (e.g. `http://www.yiiframework.com`)
	 * - a path alias (e.g. `@yii/base`). In this case, the path alias will be converted into the
	 *   actual path first by calling [[getAlias()]].
	 *
	 * @throws InvalidParamException if $path is an invalid alias.
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
	 * Class autoload loader.
	 * This method is invoked automatically when PHP sees an unknown class.
	 * The method will attempt to include the class file according to the following procedure:
	 *
	 * 1. Search in [[classMap]];
	 * 2. If the class is namespaced (e.g. `yii\base\Component`), it will attempt
	 *    to include the file associated with the corresponding path alias
	 *    (e.g. `@yii/base/Component.php`);
	 *
	 * This autoloader allows loading classes that follow the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/)
	 * and have its top-level namespace or sub-namespaces defined as path aliases.
	 *
	 * Example: When aliases `@yii` and `@yii/bootstrap` are defined, classes in the `yii\bootstrap` namespace
	 * will be loaded using the `@yii/bootstrap` alias which points to the directory where bootstrap extension
	 * files are installed and all classes from other `yii` namespaces will be loaded from the yii framework directory.
	 *
	 * @param string $className the fully qualified class name without a leading backslash "\"
	 * @throws UnknownClassException if the class does not exist in the class file
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

		include($classFile);

		if (YII_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
			throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}

	/**
	 * Creates a new object using the given configuration.
	 *
	 * The configuration can be either a string or an array.
	 * If a string, it is treated as the *object class*; if an array,
	 * it must contain a `class` element specifying the *object class*, and
	 * the rest of the name-value pairs in the array will be used to initialize
	 * the corresponding object properties.
	 *
	 * Below are some usage examples:
	 *
	 * ~~~
	 * $object = \Yii::createObject('app\components\GoogleMap');
	 * $object = \Yii::createObject([
	 *     'class' => 'app\components\GoogleMap',
	 *     'apiKey' => 'xyz',
	 * ]);
	 * ~~~
	 *
	 * This method can be used to create any object as long as the object's constructor is
	 * defined like the following:
	 *
	 * ~~~
	 * public function __construct(..., $config = []) {
	 * }
	 * ~~~
	 *
	 * The method will pass the given configuration as the last parameter of the constructor,
	 * and any additional parameters to this method will be passed as the rest of the constructor parameters.
	 *
	 * @param string|array $config the configuration. It can be either a string representing the class name
	 * or an array representing the object configuration.
	 * @return mixed the created object
	 * @throws InvalidConfigException if the configuration is invalid.
	 */
	public static function createObject($config)
	{
		static $reflections = [];

		if (is_string($config)) {
			$class = $config;
			$config = [];
		} elseif (isset($config['class'])) {
			$class = $config['class'];
			unset($config['class']);
		} else {
			throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
		}

		$class = ltrim($class, '\\');

		if (isset(static::$objectConfig[$class])) {
			$config = array_merge(static::$objectConfig[$class], $config);
		}

		if (($n = func_num_args()) > 1) {
			/** @var \ReflectionClass $reflection */
			if (isset($reflections[$class])) {
				$reflection = $reflections[$class];
			} else {
				$reflection = $reflections[$class] = new \ReflectionClass($class);
			}
			$args = func_get_args();
			array_shift($args); // remove $config
			if (!empty($config)) {
				$args[] = $config;
			}
			return $reflection->newInstanceArgs($args);
		} else {
			return empty($config) ? new $class : new $class($config);
		}
	}

	/**
	 * Logs a trace message.
	 * Trace messages are logged mainly for development purpose to see
	 * the execution work flow of some code.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function trace($message, $category = 'application')
	{
		if (YII_DEBUG) {
			static::$app->getLog()->log($message, Logger::LEVEL_TRACE, $category);
		}
	}

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function error($message, $category = 'application')
	{
		static::$app->getLog()->log($message, Logger::LEVEL_ERROR, $category);
	}

	/**
	 * Logs a warning message.
	 * A warning message is typically logged when an error occurs while the execution
	 * can still continue.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function warning($message, $category = 'application')
	{
		static::$app->getLog()->log($message, Logger::LEVEL_WARNING, $category);
	}

	/**
	 * Logs an informative message.
	 * An informative message is typically logged by an application to keep record of
	 * something important (e.g. an administrator logs in).
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function info($message, $category = 'application')
	{
		static::$app->getLog()->log($message, Logger::LEVEL_INFO, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,
	 *
	 * ~~~
	 * \Yii::beginProfile('block1');
	 * // some code to be profiled
	 *     \Yii::beginProfile('block2');
	 *     // some other code to be profiled
	 *     \Yii::endProfile('block2');
	 * \Yii::endProfile('block1');
	 * ~~~
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile()
	 */
	public static function beginProfile($token, $category = 'application')
	{
		static::$app->getLog()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile()
	 */
	public static function endProfile($token, $category = 'application')
	{
		static::$app->getLog()->log($token, Logger::LEVEL_PROFILE_END, $category);
	}

	/**
	 * Returns an HTML hyperlink that can be displayed on your Web page showing Powered by Yii" information.
	 * @return string an HTML hyperlink that can be displayed on your Web page showing Powered by Yii" information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.yiiframework.com/" rel="external">Yii Framework</a>';
	}

	/**
	 * Translates a message to the specified language.
	 *
	 * This is a shortcut method of [[\yii\i18n\I18N::translate()]].
	 *
	 * The translation will be conducted according to the message category and the target language will be used.
	 *
	 * In case when a translated message has different plural forms (separated by "|"), this method
	 * will also attempt to choose an appropriate one according to a given numeric value which is
	 * specified as the first parameter (indexed by 0) in `$params`.
	 *
	 * For example, if a translated message is "I have an apple.|I have {n} apples.", and the first
	 * parameter is 2, the message returned will be "I have 2 apples.". Note that the placeholder "{n}"
	 * will be replaced with the given number.
	 *
	 * For more details on how plural rules are applied, please refer to:
	 * <http://www.unicode.org/cldr/charts/supplemental/language_plural_rules.html>
	 *
	 * @param string $category the message category.
	 * @param string $message the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
	 * [[\yii\base\Application::language|application language]] will be used.
	 * @return string the translated message.
	 */
	public static function t($category, $message, $params = [], $language = null)
	{
		if (static::$app !== null) {
			return static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->language);
		} else {
			$p = [];
			foreach ((array) $params as $name => $value) {
				$p['{' . $name . '}'] = $value;
			}
			return ($p === []) ? $message : strtr($message, $p);
		}
	}

	/**
	 * Configures an object with the initial property values.
	 * @param object $object the object to be configured
	 * @param array $properties the property initial values given in terms of name-value pairs.
	 * @return object the object itself
	 */
	public static function configure($object, $properties)
	{
		foreach ($properties as $name => $value) {
			$object->$name = $value;
		}
		return $object;
	}

	/**
	 * Returns the public member variables of an object.
	 * This method is provided such that we can get the public member variables of an object.
	 * It is different from "get_object_vars()" because the latter will return private
	 * and protected variables if it is called within the object itself.
	 * @param object $object the object to be handled
	 * @return array the public member variables of the object
	 */
	public static function getObjectVars($object)
	{
		return get_object_vars($object);
	}
}
