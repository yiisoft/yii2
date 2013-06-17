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
	public static $aliases;
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
				$name = trim(strtr($name, array('\\' => '/', '_' => '/')), '/');
				static::setAlias('@' . $name, rtrim($path, '/\\') . '/' . $name);
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
				if ($pos === false) {
					self::$aliases[$root] = $path;
				} else {
					self::$aliases[$root] = array($alias => $path);
				}
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
			$classFile = self::$classMap[$className];
			if ($classFile[0] === '@') {
				$classFile = static::getAlias($classFile);
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
	 * [[http://www.unicode.org/cldr/charts/supplemental/language_plural_rules.html]]
	 *
	 * @param string $category the message category.
	 * @param string $message the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en_US`, `en`). If this is null, the current
	 * [[\yii\base\Application::language|application language]] will be used.
	 * @return string the translated message.
	 */
	public static function t($category, $message, $params = array(), $language = null)
	{
		if (self::$app !== null) {
			return self::$app->getI18N()->translate($category, $message, $params, $language ?: self::$app->language);
		} else {
			return is_array($params) ? strtr($message, $params) : $message;
		}
	}

	/**
	 * Configures an object with the initial property values.
	 * @param object $object the object to be configured
	 * @param array $properties the property initial values given in terms of name-value pairs.
	 */
	public static function configure($object, $properties)
	{
		foreach ($properties as $name => $value) {
			$object->$name = $value;
		}
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

YiiBase::$aliases = array(
	'@yii' => array(
		'@yii/bootstrap' => __DIR__ . '/bootstrap',
		'@yii/jui' => __DIR__ . '/jui',
		'@yii' => __DIR__,
	),
);

YiiBase::$classMap = array(
'yii\YiiBase' => YII_PATH . '/YiiBase.php',
'yii\debug\Module' => YII_PATH . '/debug/Module.php',
'yii\debug\controllers\DefaultController' => YII_PATH . '/debug/controllers/DefaultController.php',
'yii\debug\Toolbar' => YII_PATH . '/debug/Toolbar.php',
'yii\web\PageCache' => YII_PATH . '/web/PageCache.php',
'yii\web\CaptchaAction' => YII_PATH . '/web/CaptchaAction.php',
'yii\web\HttpException' => YII_PATH . '/web/HttpException.php',
'yii\web\Application' => YII_PATH . '/web/Application.php',
'yii\web\CacheSession' => YII_PATH . '/web/CacheSession.php',
'yii\web\UserEvent' => YII_PATH . '/web/UserEvent.php',
'yii\web\VerbFilter' => YII_PATH . '/web/VerbFilter.php',
'yii\web\JsExpression' => YII_PATH . '/web/JsExpression.php',
'yii\web\CookieCollection' => YII_PATH . '/web/CookieCollection.php',
'yii\web\ResponseFormatter' => YII_PATH . '/web/ResponseFormatter.php',
'yii\web\AccessRule' => YII_PATH . '/web/AccessRule.php',
'yii\web\AssetManager' => YII_PATH . '/web/AssetManager.php',
'yii\web\Session' => YII_PATH . '/web/Session.php',
'yii\web\AccessControl' => YII_PATH . '/web/AccessControl.php',
'yii\web\DbSession' => YII_PATH . '/web/DbSession.php',
'yii\web\IAssetConverter' => YII_PATH . '/web/IAssetConverter.php',
'yii\web\Identity' => YII_PATH . '/web/Identity.php',
'yii\web\Controller' => YII_PATH . '/web/Controller.php',
'yii\web\User' => YII_PATH . '/web/User.php',
'yii\web\HttpCache' => YII_PATH . '/web/HttpCache.php',
'yii\web\UrlManager' => YII_PATH . '/web/UrlManager.php',
'yii\web\Request' => YII_PATH . '/web/Request.php',
'yii\web\Cookie' => YII_PATH . '/web/Cookie.php',
'yii\web\UploadedFile' => YII_PATH . '/web/UploadedFile.php',
'yii\web\ResponseEvent' => YII_PATH . '/web/ResponseEvent.php',
'yii\web\UrlRule' => YII_PATH . '/web/UrlRule.php',
'yii\web\XmlResponseFormatter' => YII_PATH . '/web/XmlResponseFormatter.php',
'yii\web\Response' => YII_PATH . '/web/Response.php',
'yii\web\SessionIterator' => YII_PATH . '/web/SessionIterator.php',
'yii\web\AssetBundle' => YII_PATH . '/web/AssetBundle.php',
'yii\web\AssetConverter' => YII_PATH . '/web/AssetConverter.php',
'yii\web\HeaderCollection' => YII_PATH . '/web/HeaderCollection.php',
'yii\logging\Target' => YII_PATH . '/logging/Target.php',
'yii\logging\DebugTarget' => YII_PATH . '/logging/DebugTarget.php',
'yii\logging\Router' => YII_PATH . '/logging/Router.php',
'yii\logging\Logger' => YII_PATH . '/logging/Logger.php',
'yii\logging\EmailTarget' => YII_PATH . '/logging/EmailTarget.php',
'yii\logging\DbTarget' => YII_PATH . '/logging/DbTarget.php',
'yii\logging\FileTarget' => YII_PATH . '/logging/FileTarget.php',
'yii\widgets\ActiveField' => YII_PATH . '/widgets/ActiveField.php',
'yii\widgets\Captcha' => YII_PATH . '/widgets/Captcha.php',
'yii\widgets\ListPager' => YII_PATH . '/widgets/ListPager.php',
'yii\widgets\LinkPager' => YII_PATH . '/widgets/LinkPager.php',
'yii\widgets\MaskedInput' => YII_PATH . '/widgets/MaskedInput.php',
'yii\widgets\InputWidget' => YII_PATH . '/widgets/InputWidget.php',
'yii\widgets\ActiveForm' => YII_PATH . '/widgets/ActiveForm.php',
'yii\widgets\ContentDecorator' => YII_PATH . '/widgets/ContentDecorator.php',
'yii\widgets\Breadcrumbs' => YII_PATH . '/widgets/Breadcrumbs.php',
'yii\widgets\Block' => YII_PATH . '/widgets/Block.php',
'yii\widgets\FragmentCache' => YII_PATH . '/widgets/FragmentCache.php',
'yii\widgets\Menu' => YII_PATH . '/widgets/Menu.php',
'yii\rbac\PhpManager' => YII_PATH . '/rbac/PhpManager.php',
'yii\rbac\Item' => YII_PATH . '/rbac/Item.php',
'yii\rbac\Manager' => YII_PATH . '/rbac/Manager.php',
'yii\rbac\Assignment' => YII_PATH . '/rbac/Assignment.php',
'yii\rbac\DbManager' => YII_PATH . '/rbac/DbManager.php',
'yii\behaviors\AutoTimestamp' => YII_PATH . '/behaviors/AutoTimestamp.php',
'yii\caching\Cache' => YII_PATH . '/caching/Cache.php',
'yii\caching\XCache' => YII_PATH . '/caching/XCache.php',
'yii\caching\DbDependency' => YII_PATH . '/caching/DbDependency.php',
'yii\caching\DbCache' => YII_PATH . '/caching/DbCache.php',
'yii\caching\Dependency' => YII_PATH . '/caching/Dependency.php',
'yii\caching\ApcCache' => YII_PATH . '/caching/ApcCache.php',
'yii\caching\MemCacheServer' => YII_PATH . '/caching/MemCacheServer.php',
'yii\caching\ZendDataCache' => YII_PATH . '/caching/ZendDataCache.php',
'yii\caching\MemCache' => YII_PATH . '/caching/MemCache.php',
'yii\caching\GroupDependency' => YII_PATH . '/caching/GroupDependency.php',
'yii\caching\ChainedDependency' => YII_PATH . '/caching/ChainedDependency.php',
'yii\caching\WinCache' => YII_PATH . '/caching/WinCache.php',
'yii\caching\FileCache' => YII_PATH . '/caching/FileCache.php',
'yii\caching\FileDependency' => YII_PATH . '/caching/FileDependency.php',
'yii\caching\ExpressionDependency' => YII_PATH . '/caching/ExpressionDependency.php',
'yii\caching\DummyCache' => YII_PATH . '/caching/DummyCache.php',
'yii\i18n\Formatter' => YII_PATH . '/i18n/Formatter.php',
'yii\i18n\GettextFile' => YII_PATH . '/i18n/GettextFile.php',
'yii\i18n\I18N' => YII_PATH . '/i18n/I18N.php',
'yii\i18n\PhpMessageSource' => YII_PATH . '/i18n/PhpMessageSource.php',
'yii\i18n\GettextPoFile' => YII_PATH . '/i18n/GettextPoFile.php',
'yii\i18n\data\plurals' => YII_PATH . '/i18n/data/plurals.php',
'yii\i18n\DbMessageSource' => YII_PATH . '/i18n/DbMessageSource.php',
'yii\i18n\GettextMessageSource' => YII_PATH . '/i18n/GettextMessageSource.php',
'yii\i18n\MessageSource' => YII_PATH . '/i18n/MessageSource.php',
'yii\i18n\MissingTranslationEvent' => YII_PATH . '/i18n/MissingTranslationEvent.php',
'yii\i18n\GettextMoFile' => YII_PATH . '/i18n/GettextMoFile.php',
'yii\data\Pagination' => YII_PATH . '/data/Pagination.php',
'yii\data\Sort' => YII_PATH . '/data/Sort.php',
'yii\validators\RequiredValidator' => YII_PATH . '/validators/RequiredValidator.php',
'yii\validators\NumberValidator' => YII_PATH . '/validators/NumberValidator.php',
'yii\validators\BooleanValidator' => YII_PATH . '/validators/BooleanValidator.php',
'yii\validators\UniqueValidator' => YII_PATH . '/validators/UniqueValidator.php',
'yii\validators\StringValidator' => YII_PATH . '/validators/StringValidator.php',
'yii\validators\UrlValidator' => YII_PATH . '/validators/UrlValidator.php',
'yii\validators\EmailValidator' => YII_PATH . '/validators/EmailValidator.php',
'yii\validators\CaptchaValidator' => YII_PATH . '/validators/CaptchaValidator.php',
'yii\validators\DefaultValueValidator' => YII_PATH . '/validators/DefaultValueValidator.php',
'yii\validators\CompareValidator' => YII_PATH . '/validators/CompareValidator.php',
'yii\validators\RangeValidator' => YII_PATH . '/validators/RangeValidator.php',
'yii\validators\Validator' => YII_PATH . '/validators/Validator.php',
'yii\validators\FilterValidator' => YII_PATH . '/validators/FilterValidator.php',
'yii\validators\FileValidator' => YII_PATH . '/validators/FileValidator.php',
'yii\validators\RegularExpressionValidator' => YII_PATH . '/validators/RegularExpressionValidator.php',
'yii\validators\InlineValidator' => YII_PATH . '/validators/InlineValidator.php',
'yii\validators\DateValidator' => YII_PATH . '/validators/DateValidator.php',
'yii\validators\ExistValidator' => YII_PATH . '/validators/ExistValidator.php',
'yii\base\Formatter' => YII_PATH . '/base/Formatter.php',
'yii\base\UnknownMethodException' => YII_PATH . '/base/UnknownMethodException.php',
'yii\base\Application' => YII_PATH . '/base/Application.php',
'yii\base\ErrorException' => YII_PATH . '/base/ErrorException.php',
'yii\base\ActionEvent' => YII_PATH . '/base/ActionEvent.php',
'yii\base\UserException' => YII_PATH . '/base/UserException.php',
'yii\base\Module' => YII_PATH . '/base/Module.php',
'yii\base\ViewEvent' => YII_PATH . '/base/ViewEvent.php',
'yii\base\Action' => YII_PATH . '/base/Action.php',
'yii\base\ViewRenderer' => YII_PATH . '/base/ViewRenderer.php',
'yii\base\ActionFilter' => YII_PATH . '/base/ActionFilter.php',
'yii\base\Theme' => YII_PATH . '/base/Theme.php',
'yii\base\Controller' => YII_PATH . '/base/Controller.php',
'yii\base\View' => YII_PATH . '/base/View.php',
'yii\base\UnknownClassException' => YII_PATH . '/base/UnknownClassException.php',
'yii\base\ErrorHandler' => YII_PATH . '/base/ErrorHandler.php',
'yii\base\Request' => YII_PATH . '/base/Request.php',
'yii\base\Object' => YII_PATH . '/base/Object.php',
'yii\base\Behavior' => YII_PATH . '/base/Behavior.php',
'yii\base\Exception' => YII_PATH . '/base/Exception.php',
'yii\base\Event' => YII_PATH . '/base/Event.php',
'yii\base\ModelEvent' => YII_PATH . '/base/ModelEvent.php',
'yii\base\InvalidConfigException' => YII_PATH . '/base/InvalidConfigException.php',
'yii\base\Component' => YII_PATH . '/base/Component.php',
'yii\base\Response' => YII_PATH . '/base/Response.php',
'yii\base\InvalidParamException' => YII_PATH . '/base/InvalidParamException.php',
'yii\base\Model' => YII_PATH . '/base/Model.php',
'yii\base\UnknownPropertyException' => YII_PATH . '/base/UnknownPropertyException.php',
'yii\base\Arrayable' => YII_PATH . '/base/Arrayable.php',
'yii\base\InvalidRouteException' => YII_PATH . '/base/InvalidRouteException.php',
'yii\base\InlineAction' => YII_PATH . '/base/InlineAction.php',
'yii\base\NotSupportedException' => YII_PATH . '/base/NotSupportedException.php',
'yii\base\Widget' => YII_PATH . '/base/Widget.php',
'yii\base\InvalidCallException' => YII_PATH . '/base/InvalidCallException.php',
'yii\helpers\VarDumper' => YII_PATH . '/helpers/VarDumper.php',
'yii\helpers\FileHelper' => YII_PATH . '/helpers/FileHelper.php',
'yii\helpers\Console' => YII_PATH . '/helpers/Console.php',
'yii\helpers\HtmlPurifier' => YII_PATH . '/helpers/HtmlPurifier.php',
'yii\helpers\SecurityHelper' => YII_PATH . '/helpers/SecurityHelper.php',
'yii\helpers\Inflector' => YII_PATH . '/helpers/Inflector.php',
'yii\helpers\Markdown' => YII_PATH . '/helpers/Markdown.php',
'yii\helpers\Html' => YII_PATH . '/helpers/Html.php',
'yii\helpers\base\VarDumper' => YII_PATH . '/helpers/base/VarDumper.php',
'yii\helpers\base\FileHelper' => YII_PATH . '/helpers/base/FileHelper.php',
'yii\helpers\base\Console' => YII_PATH . '/helpers/base/Console.php',
'yii\helpers\base\HtmlPurifier' => YII_PATH . '/helpers/base/HtmlPurifier.php',
'yii\helpers\base\SecurityHelper' => YII_PATH . '/helpers/base/SecurityHelper.php',
'yii\helpers\base\Inflector' => YII_PATH . '/helpers/base/Inflector.php',
'yii\helpers\base\Markdown' => YII_PATH . '/helpers/base/Markdown.php',
'yii\helpers\base\Html' => YII_PATH . '/helpers/base/Html.php',
'yii\helpers\base\StringHelper' => YII_PATH . '/helpers/base/StringHelper.php',
'yii\helpers\base\Json' => YII_PATH . '/helpers/base/Json.php',
'yii\helpers\base\ArrayHelper' => YII_PATH . '/helpers/base/ArrayHelper.php',
'yii\helpers\StringHelper' => YII_PATH . '/helpers/StringHelper.php',
'yii\helpers\Json' => YII_PATH . '/helpers/Json.php',
'yii\helpers\ArrayHelper' => YII_PATH . '/helpers/ArrayHelper.php',
'yii\bootstrap\Collapse' => YII_PATH . '/bootstrap/Collapse.php',
'yii\bootstrap\Progress' => YII_PATH . '/bootstrap/Progress.php',
'yii\bootstrap\Dropdown' => YII_PATH . '/bootstrap/Dropdown.php',
'yii\bootstrap\Alert' => YII_PATH . '/bootstrap/Alert.php',
'yii\bootstrap\Carousel' => YII_PATH . '/bootstrap/Carousel.php',
'yii\bootstrap\NavBar' => YII_PATH . '/bootstrap/NavBar.php',
'yii\bootstrap\Button' => YII_PATH . '/bootstrap/Button.php',
'yii\bootstrap\Modal' => YII_PATH . '/bootstrap/Modal.php',
'yii\bootstrap\TypeAhead' => YII_PATH . '/bootstrap/TypeAhead.php',
'yii\bootstrap\ButtonDropdown' => YII_PATH . '/bootstrap/ButtonDropdown.php',
'yii\bootstrap\ButtonGroup' => YII_PATH . '/bootstrap/ButtonGroup.php',
'yii\bootstrap\Nav' => YII_PATH . '/bootstrap/Nav.php',
'yii\bootstrap\Widget' => YII_PATH . '/bootstrap/Widget.php',
'yii\bootstrap\Tabs' => YII_PATH . '/bootstrap/Tabs.php',
'yii\jui\DatePicker' => YII_PATH . '/jui/DatePicker.php',
'yii\jui\Accordion' => YII_PATH . '/jui/Accordion.php',
'yii\jui\Spinner' => YII_PATH . '/jui/Spinner.php',
'yii\jui\InputWidget' => YII_PATH . '/jui/InputWidget.php',
'yii\jui\Droppable' => YII_PATH . '/jui/Droppable.php',
'yii\jui\ProgressBar' => YII_PATH . '/jui/ProgressBar.php',
'yii\jui\Draggable' => YII_PATH . '/jui/Draggable.php',
'yii\jui\AutoComplete' => YII_PATH . '/jui/AutoComplete.php',
'yii\jui\Dialog' => YII_PATH . '/jui/Dialog.php',
'yii\jui\Selectable' => YII_PATH . '/jui/Selectable.php',
'yii\jui\assets' => YII_PATH . '/jui/assets.php',
'yii\jui\Widget' => YII_PATH . '/jui/Widget.php',
'yii\jui\Sortable' => YII_PATH . '/jui/Sortable.php',
'yii\jui\Menu' => YII_PATH . '/jui/Menu.php',
'yii\jui\Resizable' => YII_PATH . '/jui/Resizable.php',
'yii\jui\Tabs' => YII_PATH . '/jui/Tabs.php',
'yii\console\Application' => YII_PATH . '/console/Application.php',
'yii\console\Controller' => YII_PATH . '/console/Controller.php',
'yii\console\Request' => YII_PATH . '/console/Request.php',
'yii\console\Exception' => YII_PATH . '/console/Exception.php',
'yii\console\controllers\AssetController' => YII_PATH . '/console/controllers/AssetController.php',
'yii\console\controllers\MessageController' => YII_PATH . '/console/controllers/MessageController.php',
'yii\console\controllers\MigrateController' => YII_PATH . '/console/controllers/MigrateController.php',
'yii\console\controllers\HelpController' => YII_PATH . '/console/controllers/HelpController.php',
'yii\console\controllers\CacheController' => YII_PATH . '/console/controllers/CacheController.php',
'yii\console\Response' => YII_PATH . '/console/Response.php',
'yii\db\StaleObjectException' => YII_PATH . '/db/StaleObjectException.php',
'yii\db\Connection' => YII_PATH . '/db/Connection.php',
'yii\db\Expression' => YII_PATH . '/db/Expression.php',
'yii\db\TableSchema' => YII_PATH . '/db/TableSchema.php',
'yii\db\Transaction' => YII_PATH . '/db/Transaction.php',
'yii\db\QueryBuilder' => YII_PATH . '/db/QueryBuilder.php',
'yii\db\Command' => YII_PATH . '/db/Command.php',
'yii\db\Schema' => YII_PATH . '/db/Schema.php',
'yii\db\ActiveRelation' => YII_PATH . '/db/ActiveRelation.php',
'yii\db\pgsql\QueryBuilder' => YII_PATH . '/db/pgsql/QueryBuilder.php',
'yii\db\pgsql\Schema' => YII_PATH . '/db/pgsql/Schema.php',
'yii\db\Exception' => YII_PATH . '/db/Exception.php',
'yii\db\sqlite\QueryBuilder' => YII_PATH . '/db/sqlite/QueryBuilder.php',
'yii\db\sqlite\Schema' => YII_PATH . '/db/sqlite/Schema.php',
'yii\db\ColumnSchema' => YII_PATH . '/db/ColumnSchema.php',
'yii\db\mysql\QueryBuilder' => YII_PATH . '/db/mysql/QueryBuilder.php',
'yii\db\mysql\Schema' => YII_PATH . '/db/mysql/Schema.php',
'yii\db\DataReader' => YII_PATH . '/db/DataReader.php',
'yii\db\ActiveQuery' => YII_PATH . '/db/ActiveQuery.php',
'yii\db\mssql\PDO' => YII_PATH . '/db/mssql/PDO.php',
'yii\db\mssql\QueryBuilder' => YII_PATH . '/db/mssql/QueryBuilder.php',
'yii\db\mssql\Schema' => YII_PATH . '/db/mssql/Schema.php',
'yii\db\mssql\SqlsrvPDO' => YII_PATH . '/db/mssql/SqlsrvPDO.php',
'yii\db\Migration' => YII_PATH . '/db/Migration.php',
'yii\db\Query' => YII_PATH . '/db/Query.php',
'yii\db\ActiveRecord' => YII_PATH . '/db/ActiveRecord.php',
);
