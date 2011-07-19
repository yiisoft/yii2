<?php
/**
 * YiiBase class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
 * This constant defines whether exception handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', true);
/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);
/**
 * Defines the Yii framework installation path.
 */
defined('YII_PATH') or define('YII_PATH', __DIR__);


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
	 * The array keys are the class names, and the array values are the corresponding class file paths.
	 * This property mainly affects how [[autoload]] works.
	 */
	public static $classMap = array();
	/**
	 * @var array list of directories where Yii will search for new classes to be included.
	 * The first directory in the array will be searched first, and so on.
	 * This property mainly affects how [[autoload]] works.
	 */
	public static $classPath = array();
	/**
	 * @var yii\base\Application the application instance
	 */
	public static $app;

	private static $_aliases = array(
		'@yii' => YII_PATH,
	);
	private static $_imports = array();				 	// alias => class name or directory
	private static $_app;
	private static $_logger;



	/**
	 * @return string the version of Yii framework
	 */
	public static function getVersion()
	{
		return '2.0-dev';
	}

	/**
	 * Creates a Web application instance.
	 * @param mixed $config application configuration. This can be either an array representing
	 * the configuration to be applied to the newly created application instance, or a string
	 * referring to a PHP file returning the configuration array.
	 * @return yii\web\Application the newly created application instance.
	 */
	public static function createWebApplication($config = null)
	{
		return new yii\web\Application($config);
	}

	/**
	 * Creates a console application instance.
	 * @param mixed $config application configuration. This can be either an array representing
	 * the configuration to be applied to the newly created application instance, or a string
	 * referring to a PHP file returning the configuration array.
	 * @return yii\console\Application the newly created application instance.
	 */
	public static function createConsoleApplication($config = null)
	{
		return new yii\console\Application($config);
	}

	/**
	 * Returns the installation directory of the Yii framework.
	 * @return string the path of the framework
	 */
	public static function getFrameworkPath()
	{
		return YII_PATH;
	}

	/**
	 * Creates an object and initializes it based on the given configuration.
	 *
	 * The specified configuration can be either a string or an array.
	 * If the former, the string is treated as the object type which can
	 * be either the class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * If the latter, the 'class' element is treated as the object type,
	 * and the rest name-value pairs in the array are used to initialize
	 * the corresponding object properties.
	 *
	 * Any additional parameters passed to this method will be
	 * passed to the constructor of the object being created.
	 *
	 * NOTE: the array-typed configuration has been supported since version 1.0.1.
	 *
	 * @param mixed $config the configuration. It can be either a string or an array.
	 * @return mixed the created object
	 * @throws CException if the configuration does not have a 'class' element.
	 */
	public static function createComponent($config)
	{
		if (is_string($config))
		{
			$type = $config;
			$config = array();
		}
		elseif (isset($config['class']))
		{
			$type = $config['class'];
			unset($config['class']);
		}
		else
			throw new CException(Yii::t('yii', 'Object configuration must be an array containing a "class" element.'));

		if (!class_exists($type, false))
			$type = Yii::import($type, true);

		if (($n = func_num_args()) > 1)
		{
			$args = func_get_args();
			if ($n === 2)
				$object = new $type($args[1]);
			elseif ($n === 3)
				$object = new $type($args[1], $args[2]);
			elseif ($n === 4)
				$object = new $type($args[1], $args[2], $args[3]);
			else
			{
				unset($args[0]);
				$class = new ReflectionClass($type);
				// Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
				// $object=$class->newInstanceArgs($args);
				$object = call_user_func_array(array($class, 'newInstance'), $args);
			}
		}
		else
			$object = new $type;

		foreach ($config as $key => $value)
			$object->$key = $value;

		return $object;
	}

	/**
	 * Imports a class or a directory.
	 *
	 * Importing a class is like including the corresponding class file.
	 * The main difference is that importing a class is much lighter because it only
	 * includes the class file when the class is referenced the first time.
	 *
	 * Importing a directory is equivalent to adding a directory into the PHP include path.
	 * If multiple directories are imported, the directories imported later will take
	 * precedence in class file searching (i.e., they are added to the front of the PHP include path).
	 *
	 * Path aliases are used to import a class or directory. For example,
	 * <ul>
	 *   <li><code>application.components.GoogleMap</code>: import the <code>GoogleMap</code> class.</li>
	 *   <li><code>application.components.*</code>: import the <code>components</code> directory.</li>
	 * </ul>
	 *
	 * The same path alias can be imported multiple times, but only the first time is effective.
	 * Importing a directory does not import any of its subdirectories.
	 *
	 * Starting from version 1.1.5, this method can also be used to import a class in namespace format
	 * (available for PHP 5.3 or above only). It is similar to importing a class in path alias format,
	 * except that the dot separator is replaced by the backslash separator. For example, importing
	 * <code>application\components\GoogleMap</code> is similar to importing <code>application.components.GoogleMap</code>.
	 * The difference is that the former class is using qualified name, while the latter unqualified.
	 *
	 * Note, importing a class in namespace format requires that the namespace is corresponding to
	 * a valid path alias if we replace the backslash characters with dot characters.
	 * For example, the namespace <code>application\components</code> must correspond to a valid
	 * path alias <code>application.components</code>.
	 *
	 * @param string $alias path alias to be imported
	 * @param boolean $forceInclude whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used. This parameter is used only when
	 * the path alias refers to a class.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	public static function import($alias, $forceInclude = false)
	{
		if (isset(self::$_imports[$alias]))  // previously imported
			return self::$_imports[$alias];

		if (class_exists($alias, false) || interface_exists($alias, false))
			return self::$_imports[$alias] = $alias;

		if (($pos = strrpos($alias, '\\')) !== false) // a class name in PHP 5.3 namespace format
		{
			$namespace = str_replace('\\', '.', ltrim(substr($alias, 0, $pos), '\\'));
			if (($path = self::getPathOfAlias($namespace)) !== false)
			{
				$classFile = $path . DIRECTORY_SEPARATOR . substr($alias, $pos + 1) . '.php';
				if ($forceInclude)
				{
					if (is_file($classFile))
						require($classFile);
					else
						throw new CException(Yii::t('yii', 'Alias "{alias}" is invalid. Make sure it points to an existing PHP file.', array('{alias}' => $alias)));
					self::$_imports[$alias] = $alias;
				}
				else
					self::$classMap[$alias] = $classFile;
				return $alias;
			}
			else
				throw new CException(Yii::t('yii', 'Alias "{alias}" is invalid. Make sure it points to an existing directory.',
					array('{alias}' => $namespace)));
		}

		if (($pos = strrpos($alias, '.')) === false)  // a simple class name
		{
			if ($forceInclude && self::autoload($alias))
				self::$_imports[$alias] = $alias;
			return $alias;
		}

		$className = (string)substr($alias, $pos + 1);
		$isClass = $className !== '*';

		if ($isClass && (class_exists($className, false) || interface_exists($className, false)))
			return self::$_imports[$alias] = $className;

		if (($path = self::getPathOfAlias($alias)) !== false)
		{
			if ($isClass)
			{
				if ($forceInclude)
				{
					if (is_file($path . '.php'))
						require($path . '.php');
					else
						throw new CException(Yii::t('yii', 'Alias "{alias}" is invalid. Make sure it points to an existing PHP file.', array('{alias}' => $alias)));
					self::$_imports[$alias] = $className;
				}
				else
					self::$classMap[$className] = $path . '.php';
				return $className;
			}
			else  // a directory
			{
				if (self::$classPath === null)
				{
					self::$classPath = array_unique(explode(PATH_SEPARATOR, get_include_path()));
					if (($pos = array_search('.', self::$classPath, true)) !== false)
						unset(self::$classPath[$pos]);
				}

				array_unshift(self::$classPath, $path);

				if (self::$useIncludePath && set_include_path('.' . PATH_SEPARATOR . implode(PATH_SEPARATOR, self::$classPath)) === false)
					self::$useIncludePath = false;

				return self::$_imports[$alias] = $path;
			}
		}
		else
			throw new CException(Yii::t('yii', 'Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
				array('{alias}' => $alias)));
	}

	/**
	 * Translates a path alias into an actual path.
	 * The path alias can be either a root alias registered via [[setPathOfAlias]] or an
	 * alias starting with a root alias (e.g. `@yii/base/Component.php`).
	 * In the latter case, the root alias will be replaced by the corresponding registered path
	 * and the remaining part will be appended to it.
	 * Note, this method does not ensure the existence of the resulting path.
	 * @param string $alias alias
	 * @return mixed path corresponding to the alias, false if the root alias is not previously registered.
	 * @see setPathOfAlias
	 */
	public static function getPathOfAlias($alias)
	{
		if (isset(self::$_aliases[$alias])) {
			return self::$_aliases[$alias];
		}
		elseif (($pos = strpos($alias, '/')) !== false) {
			$rootAlias = substr($alias, 0, $pos);
			if (isset(self::$_aliases[$rootAlias])) {
				return self::$_aliases[$alias] = self::$_aliases[$rootAlias] . substr($alias, $pos);
			}
		}
		return false;
	}

	/**
	 * Registers a path alias.
	 * A path alias is a short name representing a path (a file path, a URL, etc.)
	 * A path alias must start with '@' (e.g. '@yii').
	 * Note that this method neither checks the existence of the path nor normalizes the path.
	 * @param string $alias alias to the path. The alias must start with '@'.
	 * @param string $path the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed. The path can be a file path (e.g. `/tmp`) or a URL (e.g. `http://www.yiiframework.com`).
	 * @see getPathOfAlias
	 */
	public static function setPathOfAlias($alias, $path)
	{
		if ($path === null) {
			unset(self::$_aliases[$alias]);
		}
		else {
			self::$_aliases[$alias] = rtrim($path, '\\/');
		}
	}

	/**
	 * Class autoload loader.
	 * This method is invoked automatically when the execution encounters an unknown class.
	 * The method will attempt to include the class file as follows:
	 *
	 * 1. Search in [[classMap]];
	 * 2. If the class is namespaced (e.g. `yii\base\Component`), it will attempt
	 *    to include the file associated with the corresponding path alias
	 *    (e.g. `@yii/base/Component.php`);
	 * 3. If the class is named in PEAR style (e.g. `PHPUnit_Framework_TestCase`),
	 *    it will attempt to include the file associated with the corresponding path alias
	 *    (e.g. `@PHPUnit/Framework/TestCase.php`);
	 * 4. Search in [[classPath]];
	 * 5. Return false so that other autoloaders have chance to include the class file.
	 *
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function autoload($className)
	{
		if (isset(self::$classMap[$className])) {
			include(self::$classMap[$className]);
			return true;
		}

		// namespaced class, e.g. yii\base\Component
		if (strpos($className, '\\') !== false) {
			// convert namespace to path alias, e.g. yii\base\Component to @yii/base/Component
			$alias = '@' . str_replace('\\', '/', $className);
			if (($path = self::getPathOfAlias($alias)) !== false) {
				include($path . '.php');
				return true;
			}
			return false;
		}

		// PEAR-styled class, e.g. PHPUnit_Framework_TestCase
		if (($pos = strpos($className, '_')) !== false) {
			// convert class name to path alias, e.g. PHPUnit_Framework_TestCase to @PHPUnit/Framework/TestCase
			$alias = '@' . str_replace('_', '/', $className);
			if (($path = self::getPathOfAlias($alias)) !== false) {
				include($path . '.php');
				return true;
			}
		}

		// search in include paths
		foreach (self::$classPath as $path) {
			$classFile = $path . DIRECTORY_SEPARATOR . $className . '.php';
			if (is_file($classFile)) {
				include($classFile);
				return true;
			}
		}

		return false;
	}

	/**
	 * Writes a trace message.
	 * This method will only log a message when the application is in debug mode.
	 * @param string $msg message to be logged
	 * @param string $category category of the message
	 * @see log
	 */
	public static function trace($msg, $category = 'application')
	{
		if (YII_DEBUG) {
			self::log($msg, CLogger::LEVEL_TRACE, $category);
		}
	}

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
	 * and may be recorded in different media, such as file, email, database, using
	 * {@link CLogRouter}.
	 * @param string $msg message to be logged
	 * @param string $level level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public static function log($msg, $level = CLogger::LEVEL_INFO, $category = 'application')
	{
		if (self::$_logger === null) {
			self::$_logger = new CLogger;
		}
		if (YII_DEBUG && YII_TRACE_LEVEL > 0 && $level !== CLogger::LEVEL_PROFILE)
		{
			$traces = debug_backtrace();
			$count = 0;
			foreach ($traces as $trace)
			{
				if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII_PATH) !== 0)
				{
					$msg .= "\nin " . $trace['file'] . ' (' . $trace['line'] . ')';
					if (++$count >= YII_TRACE_LEVEL)
						break;
				}
			}
		}
		self::$_logger->log($msg, $level, $category);
	}

	/**
	 * Marks the begin of a code block for profiling.
	 * This has to be matched with a call to {@link endProfile()} with the same token.
	 * The begin- and end- calls must also be properly nested, e.g.,
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block2');
	 * Yii::endProfile('block1');
	 * </pre>
	 * The following sequence is not valid:
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block1');
	 * Yii::endProfile('block2');
	 * </pre>
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile
	 */
	public static function beginProfile($token, $category = 'application')
	{
		self::log('begin:' . $token, CLogger::LEVEL_PROFILE, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to {@link beginProfile()} with the same token.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile
	 */
	public static function endProfile($token, $category = 'application')
	{
		self::log('end:' . $token, CLogger::LEVEL_PROFILE, $category);
	}

	/**
	 * @return CLogger message logger
	 */
	public static function getLogger()
	{
		if (self::$_logger !== null)
			return self::$_logger;
		else
			return self::$_logger = new CLogger;
	}

	/**
	 * Sets the logger object.
	 * @param CLogger $logger the logger object.
	 * @since 1.1.8
	 */
	public static function setLogger($logger)
	{
		self::$_logger = $logger;
	}

	/**
	 * Returns a string that can be displayed on your Web page showing Powered-by-Yii information
	 * @return string a string that can be displayed on your Web page showing Powered-by-Yii information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.yiiframework.com/" rel="external">Yii Framework</a>.';
	}

	/**
	 * Translates a message to the specified language.
	 * Starting from version 1.0.2, this method supports choice format (see {@link CChoiceFormat}),
	 * i.e., the message returned will be chosen from a few candidates according to the given
	 * number value. This feature is mainly used to solve plural format issue in case
	 * a message has different plural forms in some languages.
	 * @param string $category message category. Please use only word letters. Note, category 'yii' is
	 * reserved for Yii framework core code use. See {@link CPhpMessageSource} for
	 * more interpretation about message category.
	 * @param string $message the original message
	 * @param array $params parameters to be applied to the message using <code>strtr</code>.
	 * Starting from version 1.0.2, the first parameter can be a number without key.
	 * And in this case, the method will call {@link CChoiceFormat::format} to choose
	 * an appropriate message translation.
	 * Starting from version 1.1.6 you can pass parameter for {@link CChoiceFormat::format}
	 * or plural forms format without wrapping it with array.
	 * @param string $source which message source application component to use.
	 * Defaults to null, meaning using 'coreMessages' for messages belonging to
	 * the 'yii' category and using 'messages' for the rest messages.
	 * @param string $language the target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 * This parameter has been available since version 1.0.3.
	 * @return string the translated message
	 * @see CMessageSource
	 */
	public static function t($category, $message, $params = array(), $source = null, $language = null)
	{
		if (self::$_app !== null)
		{
			if ($source === null)
				$source = ($category === 'yii' || $category === 'zii') ? 'coreMessages' : 'messages';
			if (($source = self::$_app->getComponent($source)) !== null)
				$message = $source->translate($category, $message, $language);
		}
		if ($params === array())
			return $message;
		if (!is_array($params))
			$params = array($params);
		if (isset($params[0])) // number choice
		{
			if (strpos($message, '|') !== false)
			{
				if (strpos($message, '#') === false)
				{
					$chunks = explode('|', $message);
					$expressions = self::$_app->getLocale($language)->getPluralRules();
					if ($n = min(count($chunks), count($expressions)))
					{
						for ($i = 0;$i < $n;$i++)
							$chunks[$i] = $expressions[$i] . '#' . $chunks[$i];

						$message = implode('|', $chunks);
					}
				}
				$message = CChoiceFormat::format($message, $params[0]);
			}
			if (!isset($params['{n}']))
				$params['{n}'] = $params[0];
			unset($params[0]);
		}
		return $params !== array() ? strtr($message, $params) : $message;
	}
}
