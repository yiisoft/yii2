<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\UnknownClassException;
use yii\logging\Logger;

/**
 * Gets the application start timestamp.
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') or define('YII_DEBUG', false);
/**
 * This constant defines how much call stack information (file name and line number) should be logged by Yii::trace().
 * Defaults to 0, meaning no backtrace information. If it is greater than 0,
 * at most that number of call stacks will be logged. Note, only user application call stacks are considered.
 */
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 0);
/**
 * This constant defines the framework installation directory.
 */
defined('YII_PATH') or define('YII_PATH', __DIR__);
/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);


/**
 * YiiBase is the core helper class for the Yii framework.
 *
 * Do not use YiiBase directly. Instead, use its child class [[Yii]] where
 * you can customize methods of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class YiiBase
{
	/**
	 * @var array class map used by the Yii autoloading mechanism.
	 * The array keys are the class names (without leading backslashes), and the array values
	 * are the corresponding class file paths (or path aliases). This property mainly affects
	 * how [[autoload()]] works.
	 * @see import
	 * @see autoload
	 */
	public static $classMap = array();
	/**
	 * @var boolean whether to search PHP include_path when autoloading unknown classes.
	 * You may want to turn this off if you are also using autoloaders from other libraries.
	 */
	public static $enableIncludePath = true;
	/**
	 * @var \yii\console\Application|\yii\web\Application the application instance
	 */
	public static $app;
	/**
	 * @var array registered path aliases
	 * @see getAlias
	 * @see setAlias
	 */
	public static $aliases = array(
		'@yii' => __DIR__,
	);
	/**
	 * @var array initial property values that will be applied to objects newly created via [[createObject]].
	 * The array keys are class names without leading backslashes "\", and the array values are the corresponding
	 * name-value pairs for initializing the created class instances. For example,
	 *
	 * ~~~
	 * array(
	 *     'Bar' => array(
	 *         'prop1' => 'value1',
	 *         'prop2' => 'value2',
	 *     ),
	 *     'mycompany\foo\Car' => array(
	 *         'prop1' => 'value1',
	 *         'prop2' => 'value2',
	 *     ),
	 * )
	 * ~~~
	 *
	 * @see createObject
	 */
	public static $objectConfig = array();

	private static $_imported = array(); // alias => class name or directory
	private static $_logger;

	/**
	 * @return string the version of Yii framework
	 */
	public static function getVersion()
	{
		return '2.0-dev';
	}

	/**
	 * Imports a class by its alias.
	 *
	 * This method is provided to support autoloading of non-namespaced classes.
	 * Such a class can be specified in terms of an alias. For example, the alias `@old/code/Sample`
	 * may represent the `Sample` class under the directory `@old/code` (a path alias).
	 *
	 * By importing a class, the class is put in an internal storage such that when
	 * the class is used for the first time, the class autoloader will be able to
	 * find the corresponding class file and include it. For this reason, this method
	 * is much lighter than `include()`.
	 *
	 * You may import the same class multiple times. Only the first importing will count.
	 *
	 * @param string $alias the class to be imported. This may be either a class alias or a fully-qualified class name.
	 * If the latter, it will be returned back without change.
	 * @return string the actual class name that `$alias` refers to
	 * @throws Exception if the alias is invalid
	 */
	public static function import($alias)
	{
		if (strncmp($alias, '@', 1)) {
			return $alias;
		} else {
			$alias = static::getAlias($alias);
			if (!isset(self::$_imported[$alias])) {
				$className = basename($alias);
				self::$_imported[$alias] = $className;
				self::$classMap[$className] = $alias . '.php';
			}
			return self::$_imported[$alias];
		}
	}

	/**
	 * Imports a set of namespaces.
	 *
	 * By importing a namespace, the method will create an alias for the directory corresponding
	 * to the namespace. For example, if "foo\bar" is a namespace associated with the directory
	 * "path/to/foo/bar", then an alias "@foo/bar" will be created for this directory.
	 *
	 * This method is typically invoked in the bootstrap file to import the namespaces of
	 * the installed extensions. By default, Composer, when installing new extensions, will
	 * generate such a mapping file which can be loaded and passed to this method.
	 *
	 * @param array $namespaces the namespaces to be imported. The keys are the namespaces,
	 * and the values are the corresponding directories.
	 */
	public static function importNamespaces($namespaces)
	{
		foreach ($namespaces as $name => $path) {
			if ($name !== '') {
				$name = '@' . str_replace('\\', '/', $name);
				static::setAlias($name, $path);
			}
		}
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
	 * @see setAlias
	 */
	public static function getAlias($alias, $throwException = true)
	{
		if (strncmp($alias, '@', 1)) {
			// not an alias
			return $alias;
		}

		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);

		if (isset(self::$aliases[$root])) {
			if (is_string(self::$aliases[$root])) {
				return $pos === false ? self::$aliases[$root] : self::$aliases[$root] . substr($alias, $pos);
			} else {
				foreach (self::$aliases[$root] as $name => $path) {
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

		if (isset(self::$aliases[$root])) {
			if (is_string(self::$aliases[$root])) {
				return $root;
			} else {
				foreach (self::$aliases[$root] as $name => $path) {
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
	 * @see getAlias
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
			if (!isset(self::$aliases[$root])) {
				self::$aliases[$root] = $path;
			} elseif (is_string(self::$aliases[$root])) {
				if ($pos === false) {
					self::$aliases[$root] = $path;
				} else {
					self::$aliases[$root] = array(
						$alias => $path,
						$root => self::$aliases[$root],
					);
				}
			} else {
				self::$aliases[$root][$alias] = $path;
				krsort(self::$aliases[$root]);
			}
		} elseif (isset(self::$aliases[$root])) {
			if (is_array(self::$aliases[$root])) {
				unset(self::$aliases[$root][$alias]);
			} elseif ($pos === false) {
				unset(self::$aliases[$root]);
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
	 * 3. If the class is named in PEAR style (e.g. `PHPUnit_Framework_TestCase`),
	 *    it will attempt to include the file associated with the corresponding path alias
	 *    (e.g. `@PHPUnit/Framework/TestCase.php`);
	 * 4. Search PHP include_path for the actual class file if [[enableIncludePath]] is true;
	 * 5. Return false so that other autoloaders have chance to include the class file.
	 *
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 * @throws InvalidConfigException if the class file does not exist
	 * @throws UnknownClassException if the class does not exist in the class file
	 */
	public static function autoload($className)
	{
		$className = ltrim($className, '\\');

		if (isset(self::$classMap[$className])) {
			$classFile = static::getAlias(self::$classMap[$className]);
			if (!is_file($classFile)) {
				throw new InvalidConfigException("Class file does not exist: $classFile");
			}
		} else {
			// follow PSR-0 to determine the class file
			if (($pos = strrpos($className, '\\')) !== false) {
				// namespaced class, e.g. yii\base\Component
				$path = str_replace('\\', '/', substr($className, 0, $pos + 1))
					. str_replace('_', '/', substr($className, $pos + 1)) . '.php';
			} else {
				$path = str_replace('_', '/', $className) . '.php';
			}

			// try via path alias first
			if (strpos($path, '/') !== false) {
				$fullPath = static::getAlias('@' . $path, false);
				if ($fullPath !== false && is_file($fullPath)) {
					$classFile = $fullPath;
				}
			}

			// search include_path
			if (!isset($classFile) && self::$enableIncludePath && ($fullPath = stream_resolve_include_path($path)) !== false) {
				$classFile = $fullPath;
			}

			if (!isset($classFile)) {
				// return false to let other autoloaders to try loading the class
				return false;
			}
		}

		include($classFile);

		if (class_exists($className, false) || interface_exists($className, false) ||
			function_exists('trait_exists') && trait_exists($className, false)) {
			return true;
		} else {
			throw new UnknownClassException("Unable to find '$className' in file: $classFile");
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
	 * The object type can be either a class name or the [[getAlias()|alias]] of
	 * the class. For example,
	 *
	 * - `app\components\GoogleMap`: fully-qualified namespaced class.
	 * - `@app/components/GoogleMap`: an alias, used for non-namespaced class.
	 *
	 * Below are some usage examples:
	 *
	 * ~~~
	 * $object = \Yii::createObject('@app/components/GoogleMap');
	 * $object = \Yii::createObject(array(
	 *     'class' => '\app\components\GoogleMap',
	 *     'apiKey' => 'xyz',
	 * ));
	 * ~~~
	 *
	 * This method can be used to create any object as long as the object's constructor is
	 * defined like the following:
	 *
	 * ~~~
	 * public function __construct(..., $config = array()) {
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
		static $reflections = array();

		if (is_string($config)) {
			$class = $config;
			$config = array();
		} elseif (isset($config['class'])) {
			$class = $config['class'];
			unset($config['class']);
		} else {
			throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
		}

		if (!class_exists($class, false)) {
			$class = static::import($class);
		}

		$class = ltrim($class, '\\');

		if (isset(self::$objectConfig[$class])) {
			$config = array_merge(self::$objectConfig[$class], $config);
		}

		if (($n = func_num_args()) > 1) {
			/** @var $reflection \ReflectionClass */
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
			self::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
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
		self::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
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
		self::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
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
		self::getLogger()->log($message, Logger::LEVEL_INFO, $category);
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
	 * @see endProfile
	 */
	public static function beginProfile($token, $category = 'application')
	{
		self::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile
	 */
	public static function endProfile($token, $category = 'application')
	{
		self::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
	}

	/**
	 * Returns the message logger object.
	 * @return \yii\logging\Logger message logger
	 */
	public static function getLogger()
	{
		if (self::$_logger !== null) {
			return self::$_logger;
		} else {
			return self::$_logger = new Logger;
		}
	}

	/**
	 * Sets the logger object.
	 * @param Logger $logger the logger object.
	 */
	public static function setLogger($logger)
	{
		self::$_logger = $logger;
	}

	/**
	 * Returns an HTML hyperlink that can be displayed on your Web page showing Powered by Yii" information.
	 * @return string an HTML hyperlink that can be displayed on your Web page showing Powered by Yii" information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.yiiframework.com/" rel="external">Yii Framework</a>.';
	}

	/**
	 * Translates a message to the specified language.
	 *
	 * The translation will be conducted according to the message category and the target language.
	 * To specify the category of the message, prefix the message with the category name and separate it
	 * with "|". For example, "app|hello world". If the category is not specified, the default category "app"
	 * will be used. The actual message translation is done by a [[\yii\i18n\MessageSource|message source]].
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
	 * [[http://www.unicode.org/cldr/charts/supplemental/language_plural_rules.html]]
	 *
	 * @param string $message the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en_US`, `en`). If this is null, the current
	 * [[\yii\base\Application::language|application language]] will be used.
	 * @return string the translated message.
	 */
	public static function t($message, $params = array(), $language = null)
	{
		if (self::$app !== null) {
			return self::$app->getI18N()->translate($message, $params, $language);
		} else {
			if (strpos($message, '|') !== false && preg_match('/^([\w\-\\/\.\\\\]+)\|(.*)/', $message, $matches)) {
				$message = $matches[2];
			}
			return is_array($params) ? strtr($message, $params) : $message;
		}
	}
}
