<?php
/**
 * Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\util\FileHelper;

/**
 * Application is the base class for all application classes.
 *
 * An application serves as the global context that the user request
 * is being processed. It manages a set of application components that
 * provide specific functionalities to the whole application.
 *
 * The core application components provided by Application are the following:
 * <ul>
 * <li>{@link getErrorHandler errorHandler}: handles PHP errors and
 *   uncaught exceptions. This application component is dynamically loaded when needed.</li>
 * <li>{@link getSecurityManager securityManager}: provides security-related
 *   services, such as hashing, encryption. This application component is dynamically
 *   loaded when needed.</li>
 * <li>{@link getStatePersister statePersister}: provides global state
 *   persistence method. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCache cache}: provides caching feature. This application component is
 *   disabled by default.</li>
 * <li>{@link getMessages messages}: provides the message source for translating
 *   application messages. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCoreMessages coreMessages}: provides the message source for translating
 *   Yii framework messages. This application component is dynamically loaded when needed.</li>
 * </ul>
 *
 * Application will undergo the following life cycles when processing a user request:
 * <ol>
 * <li>load application configuration;</li>
 * <li>set up class autoloader and error handling;</li>
 * <li>load static application components;</li>
 * <li>{@link beforeRequest}: preprocess the user request; `beforeRequest` event raised.</li>
 * <li>{@link processRequest}: process the user request;</li>
 * <li>{@link afterRequest}: postprocess the user request; `afterRequest` event raised.</li>
 * </ol>
 *
 * Starting from lifecycle 3, if a PHP error or an uncaught exception occurs,
 * the application will switch to its error handling logic and jump to step 6 afterwards.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends Module
{
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	const EVENT_AFTER_REQUEST = 'afterRequest';
	/**
	 * @var string the application name. Defaults to 'My Application'.
	 */
	public $name = 'My Application';
	/**
	 * @var string the version of this application. Defaults to '1.0'.
	 */
	public $version = '1.0';
	/**
	 * @var string the charset currently used for the application. Defaults to 'UTF-8'.
	 */
	public $charset = 'UTF-8';
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are in. Defaults to 'en_us' (US English).
	 * @see language
	 */
	public $sourceLanguage = 'en_us';
	/**
	 * @var array IDs of the components that need to be loaded when the application starts.
	 */
	public $preload = array();
	/**
	 * @var Controller the currently active controller instance
	 */
	public $controller;
	/**
	 * @var mixed the layout that should be applied for views in this application. Defaults to 'main'.
	 * If this is false, layout will be disabled.
	 */
	public $layout = 'main';

	// todo
	public $localeDataPath = '@yii/i18n/data';

	private $_runtimePath;
	private $_ended = false;
	private $_language;

	/**
	 * Constructor.
	 * @param string $id the ID of this application. The ID should uniquely identify the application from others.
	 * @param string $basePath the base path of this application. This should point to
	 * the directory containing all application logic, template and data.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $basePath, $config = array())
	{
		Yii::$application = $this;
		$this->id = $id;
		$this->setBasePath($basePath);

		if (YII_ENABLE_ERROR_HANDLER) {
			set_exception_handler(array($this, 'handleException'));
			set_error_handler(array($this, 'handleError'), error_reporting());
		}

		$this->registerDefaultAliases();
		$this->registerCoreComponents();

		Component::__construct($config);
	}

	/**
	 * Initializes the application by loading components declared in [[preload]].
	 * If you override this method, make sure the parent implementation is invoked.
	 */
	public function init()
	{
		$this->preloadComponents();
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by calling [[afterRequest()]] before exiting.
	 * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param boolean $exit whether to exit the current request.
	 * It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
	 */
	public function end($status = 0, $exit = true)
	{
		if (!$this->_ended) {
			$this->_ended = true;
			$this->afterRequest();
		}
		if ($exit) {
			exit($status);
		}
	}

	/**
	 * Runs the application.
	 * This is the main entrance of an application.
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 */
	public function run()
	{
		$this->beforeRequest();
		$status = $this->processRequest();
		$this->afterRequest();
		return $status;
	}

	/**
	 * Raises the [[EVENT_BEFORE_REQUEST]] event right BEFORE the application processes the request.
	 */
	public function beforeRequest()
	{
		$this->trigger(self::EVENT_BEFORE_REQUEST);
	}

	/**
	 * Raises the [[EVENT_AFTER_REQUEST]] event right AFTER the application processes the request.
	 */
	public function afterRequest()
	{
		$this->trigger(self::EVENT_AFTER_REQUEST);
	}

	/**
	 * Processes the request.
	 * Child classes should override this method with actual request processing logic.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 */
	public function processRequest()
	{
		return 0;
	}

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath !== null) {
			$this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
		}
		return $this->_runtimePath;
	}

	/**
	 * Sets the directory that stores runtime files.
	 * @param string $path the directory that stores runtime files.
	 * @throws InvalidConfigException if the directory does not exist or is not writable
	 */
	public function setRuntimePath($path)
	{
		$p = FileHelper::ensureDirectory($path);
		if (is_writable($p)) {
			$this->_runtimePath = $p;
		} else {
			throw new InvalidConfigException("Runtime path must be writable by the Web server process: $path");
		}
	}

	/**
	 * Returns the language that the end user is using.
	 * @return string the language that the user is using (e.g. 'en_US', 'zh_CN').
	 * Defaults to the value of [[sourceLanguage]].
	 */
	public function getLanguage()
	{
		return $this->_language === null ? $this->sourceLanguage : $this->_language;
	}

	/**
	 * Specifies which language the end user is using.
	 * This is the language that the application should use to display to end users.
	 * By default, [[language]] and [[sourceLanguage]] are the same.
	 * Do not set this property unless your application needs to support multiple languages.
	 * @param string $language the user language (e.g. 'en_US', 'zh_CN').
	 * If it is null, the [[sourceLanguage]] will be used.
	 */
	public function setLanguage($language)
	{
		$this->_language = $language;
	}

	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Sets the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_set().
	 * @param string $value the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set($value);
	}

	//	/**
	//	 * Returns the security manager component.
	//	 * @return SecurityManager the security manager application component.
	//	 */
	//	public function getSecurityManager()
	//	{
	//		return $this->getComponent('securityManager');
	//	}
	//
	//	/**
	//	 * Returns the locale instance.
	//	 * @param string $localeID the locale ID (e.g. en_US). If null, the {@link getLanguage application language ID} will be used.
	//	 * @return CLocale the locale instance
	//	 */
	//	public function getLocale($localeID = null)
	//	{
	//		return CLocale::getInstance($localeID === null ? $this->getLanguage() : $localeID);
	//	}
	//
	//	/**
	//	 * @return CNumberFormatter the locale-dependent number formatter.
	//	 * The current {@link getLocale application locale} will be used.
	//	 */
	//	public function getNumberFormatter()
	//	{
	//		return $this->getLocale()->getNumberFormatter();
	//	}
	//
	//	/**
	//	 * Returns the locale-dependent date formatter.
	//	 * @return CDateFormatter the locale-dependent date formatter.
	//	 * The current {@link getLocale application locale} will be used.
	//	 */
	//	public function getDateFormatter()
	//	{
	//		return $this->getLocale()->getDateFormatter();
	//	}
	//
	//	/**
	//	 * Returns the core message translations component.
	//	 * @return \yii\i18n\MessageSource the core message translations
	//	 */
	//	public function getCoreMessages()
	//	{
	//		return $this->getComponent('coreMessages');
	//	}
	//
	//	/**
	//	 * Returns the application message translations component.
	//	 * @return \yii\i18n\MessageSource the application message translations
	//	 */
	//	public function getMessages()
	//	{
	//		return $this->getComponent('messages');
	//	}

	/**
	 * Returns the database connection component.
	 * @return \yii\db\Connection the database connection
	 */
	public function getDb()
	{
		return $this->getComponent('db');
	}

	/**
	 * Returns the error handler component.
	 * @return ErrorHandler the error handler application component.
	 */
	public function getErrorHandler()
	{
		return $this->getComponent('errorHandler');
	}

	/**
	 * Returns the application theme.
	 * @return Theme the theme that this application is currently using.
	 */
	public function getTheme()
	{
		return $this->getComponent('theme');
	}

	/**
	 * Returns the cache component.
	 * @return \yii\caching\Cache the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * Returns the request component.
	 * @return Request the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the view renderer.
	 * @return ViewRenderer the view renderer used by this application.
	 */
	public function getViewRenderer()
	{
		return $this->getComponent('viewRenderer');
	}

	/**
	 * Sets default path aliases.
	 */
	public function registerDefaultAliases()
	{
		Yii::$aliases['@application'] = $this->getBasePath();
		Yii::$aliases['@entry'] = dirname($_SERVER['SCRIPT_FILENAME']);
		Yii::$aliases['@www'] = '';
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	public function registerCoreComponents()
	{
		$this->setComponents(array(
			'errorHandler' => array(
				'class' => 'yii\base\ErrorHandler',
			),
			'coreMessages' => array(
				'class' => 'yii\i18n\PhpMessageSource',
				'language' => 'en_us',
				'basePath' => '@yii/messages',
			),
			'messages' => array(
				'class' => 'yii\i18n\PhpMessageSource',
			),
			'securityManager' => array(
				'class' => 'yii\base\SecurityManager',
			),
			'urlManager' => array(
				'class' => 'yii\web\UrlManager',
			),
		));
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is used as a PHP error handler. It will simply raise an `ErrorException`.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 * @throws \ErrorException the error exception
	 */
	public function handleError($code, $message, $file, $line)
	{
		if (error_reporting() !== 0) {
			throw new \ErrorException($message, 0, $code, $file, $line);
		}
	}

	/**
	 * Handles uncaught PHP exceptions.
	 *
	 * This method is implemented as a PHP exception handler. It requires
	 * that constant YII_ENABLE_ERROR_HANDLER be defined true.
	 *
	 * @param \Exception $exception exception that is not caught
	 */
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors while handling exceptions
		restore_error_handler();
		restore_exception_handler();

		try {
			$this->logException($exception);

			if (($handler = $this->getErrorHandler()) !== null) {
				$handler->handle($exception);
			} else {
				$this->renderException($exception);
			}

			$this->end(1);

		} catch(\Exception $e) {
			// exception could be thrown in end() or ErrorHandler::handle()
			$msg = (string)$e;
			$msg .= "\nPrevious exception:\n";
			$msg .= (string)$exception;
			$msg .= "\n\$_SERVER = " . var_export($_SERVER, true);
			error_log($msg);
			exit(1);
		}
	}

	/**
	 * Renders an exception without using rich format.
	 * @param \Exception $exception the exception to be rendered.
	 */
	public function renderException($exception)
	{
		if ($exception instanceof Exception && ($exception instanceof UserException || !YII_DEBUG)) {
			$message = $exception->getName() . ': ' . $exception->getMessage();
		} else {
			$message = YII_DEBUG ? (string)$exception : 'Error: ' . $exception->getMessage();
		}
		if (PHP_SAPI) {
			echo $message . "\n";
		} else {
			echo '<pre>' . htmlspecialchars($message, ENT_QUOTES, $this->charset) . '</pre>';
		}
	}

	// todo: to be polished
	protected function logException($exception)
	{
		$category = get_class($exception);
		if ($exception instanceof HttpException) {
			/** @var $exception HttpException */
			$category .= '\\' . $exception->statusCode;
		} elseif ($exception instanceof \ErrorException) {
			/** @var $exception \ErrorException */
			$category .= '\\' . $exception->getSeverity();
		}
		Yii::error((string)$exception, $category);
	}
}
