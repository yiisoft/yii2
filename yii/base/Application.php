<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Application is the base class for all application classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends Module
{
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	const EVENT_AFTER_REQUEST = 'afterRequest';
	/**
	 * @var string the application name.
	 */
	public $name = 'My Application';
	/**
	 * @var string the version of this application.
	 */
	public $version = '1.0';
	/**
	 * @var string the charset currently used for the application.
	 */
	public $charset = 'UTF-8';
	/**
	 * @var string the language that is meant to be used for end users.
	 * @see sourceLanguage
	 */
	public $language = 'en_US';
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are written in.
	 * @see language
	 */
	public $sourceLanguage = 'en_US';
	/**
	 * @var array IDs of the components that need to be loaded when the application starts.
	 */
	public $preload = array();
	/**
	 * @var \yii\web\Controller|\yii\console\Controller the currently active controller instance
	 */
	public $controller;
	/**
	 * @var mixed the layout that should be applied for views in this application. Defaults to 'main'.
	 * If this is false, layout will be disabled.
	 */
	public $layout = 'main';

	private $_ended = false;

	/**
	 * @var string Used to reserve memory for fatal error handler.
	 */
	private $_memoryReserve;

	/**
	 * Constructor.
	 * @param array $config name-value pairs that will be used to initialize the object properties.
	 * Note that the configuration must contain both [[id]] and [[basePath]].
	 * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
	 */
	public function __construct($config = array())
	{
		Yii::$app = $this;

		if (!isset($config['id'])) {
			throw new InvalidConfigException('The "id" configuration is required.');
		}

		if (isset($config['basePath'])) {
			$this->setBasePath($config['basePath']);
			Yii::setAlias('@app', $this->getBasePath());
			unset($config['basePath']);
		} else {
			throw new InvalidConfigException('The "basePath" configuration is required.');
		}
		
		if (isset($config['timeZone'])) {
			$this->setTimeZone($config['timeZone']);
			unset($config['timeZone']);
		} elseif (!ini_get('date.timezone')) {
			$this->setTimeZone('UTC');
		} 

		if (!ini_get('date.timezone')) {
			$this->setTimeZone('UTC');
		}

		$this->registerErrorHandlers();
		$this->registerCoreComponents();

		Component::__construct($config);
	}

	/**
	 * Registers error handlers.
	 */
	public function registerErrorHandlers()
	{
		if (YII_ENABLE_ERROR_HANDLER) {
			ini_set('display_errors', 0);
			set_exception_handler(array($this, 'handleException'));
			set_error_handler(array($this, 'handleError'), error_reporting());
		}
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

		$this->handleFatalError();

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
		// Allocating twice more than required to display memory exhausted error
		// in case of trying to allocate last 1 byte while all memory is taken.
		$this->_memoryReserve = str_repeat('x', 1024 * 256);
		register_shutdown_function(array($this, 'end'), 0, false);
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

	private $_runtimePath;

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
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
		$path = Yii::getAlias($path);
		if (is_dir($path) && is_writable($path)) {
			$this->_runtimePath = $path;
		} else {
			throw new InvalidConfigException("Runtime path must be a directory writable by the Web server process: $path");
		}
	}

	private $_vendorPath;

	/**
	 * Returns the directory that stores vendor files.
	 * @return string the directory that stores vendor files. Defaults to 'protected/vendor'.
	 */
	public function getVendorPath()
	{
		if ($this->_vendorPath === null) {
			$this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
		}
		return $this->_vendorPath;
	}

	/**
	 * Sets the directory that stores vendor files.
	 * @param string $path the directory that stores vendor files.
	 */
	public function setVendorPath($path)
	{
		$this->_vendorPath = Yii::getAlias($path);
	}

	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * If time zone is not configured in php.ini or application config,
	 * it will be set to UTC by default.
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
	 * Returns the cache component.
	 * @return \yii\caching\Cache the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * Returns the request component.
	 * @return \yii\web\Request|\yii\console\Request the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the view object.
	 * @return View the view object that is used to render various view files.
	 */
	public function getView()
	{
		return $this->getComponent('view');
	}

	/**
	 * Returns the URL manager for this application.
	 * @return \yii\web\UrlManager the URL manager for this application.
	 */
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
	}

	/**
	 * Returns the internationalization (i18n) component
	 * @return \yii\i18n\I18N the internationalization component
	 */
	public function getI18N()
	{
		return $this->getComponent('i18n');
	}

	/**
	 * Returns the auth manager for this application.
	 * @return \yii\rbac\Manager the auth manager for this application.
	 */
	public function getAuthManager()
	{
		return $this->getComponent('authManager');
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
			'i18n' => array(
				'class' => 'yii\i18n\I18N',
			),
			'urlManager' => array(
				'class' => 'yii\web\UrlManager',
			),
			'view' => array(
				'class' => 'yii\base\View',
			),
		));
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

		} catch (\Exception $e) {
			// exception could be thrown in end() or ErrorHandler::handle()
			$msg = (string)$e;
			$msg .= "\nPrevious exception:\n";
			$msg .= (string)$exception;
			if (YII_DEBUG) {
				echo $msg;
			}
			$msg .= "\n\$_SERVER = " . var_export($_SERVER, true);
			error_log($msg);
			exit(1);
		}
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
	 *
	 * @throws ErrorException
	 */
	public function handleError($code, $message, $file, $line)
	{
		if (error_reporting() !== 0) {
			$exception = new ErrorException($message, $code, $code, $file, $line);

			// in case error appeared in __toString method we can't throw any exception
			$trace = debug_backtrace(false);
			array_shift($trace);
			foreach ($trace as $frame) {
				if ($frame['function'] == '__toString') {
					$this->handleException($exception);
				}
			}

			throw $exception;
		}
	}

	/**
	 * Handles fatal PHP errors
	 */
	public function handleFatalError()
	{
		if (YII_ENABLE_ERROR_HANDLER) {
			$error = error_get_last();

			if (ErrorException::isFatalError($error)) {
				unset($this->_memoryReserve);
				$exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
				// use error_log because it's too late to use Yii log
				error_log($exception);

				if (($handler = $this->getErrorHandler()) !== null) {
					$handler->handle($exception);
				} else {
					$this->renderException($exception);
				}

				exit(1);
			}
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
