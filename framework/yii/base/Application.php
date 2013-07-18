<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\Console;
use yii\web\HttpException;

/**
 * Application is the base class for all application classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Application extends Module
{
	/**
	 * @event Event an event raised before the application starts to handle a request.
	 */
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	/**
	 * @event Event an event raised after the application successfully handles a request (before the response is sent out).
	 */
	const EVENT_AFTER_REQUEST = 'afterRequest';
	/**
	 * @event ActionEvent an event raised before executing a controller action.
	 * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
	 */
	const EVENT_BEFORE_ACTION = 'beforeAction';
	/**
	 * @event ActionEvent an event raised after executing a controller action.
	 */
	const EVENT_AFTER_ACTION = 'afterAction';
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
	 * @var Controller the currently active controller instance
	 */
	public $controller;
	/**
	 * @var mixed the layout that should be applied for views in this application. Defaults to 'main'.
	 * If this is false, layout will be disabled.
	 */
	public $layout = 'main';
	/**
	 * @var integer the size of the reserved memory. A portion of memory is pre-allocated so that
	 * when an out-of-memory issue occurs, the error handler is able to handle the error with
	 * the help of this reserved memory. If you set this value to be 0, no memory will be reserved.
	 * Defaults to 256KB.
	 */
	public $memoryReserveSize = 262144;
	/**
	 * @var string the requested route
	 */
	public $requestedRoute;
	/**
	 * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
	 */
	public $requestedAction;
	/**
	 * @var array the parameters supplied to the requested action.
	 */
	public $requestedParams;

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
			unset($config['basePath']);
		} else {
			throw new InvalidConfigException('The "basePath" configuration is required.');
		}

		$this->preInit($config);

		$this->registerErrorHandlers();
		$this->registerCoreComponents();

		Component::__construct($config);
	}

	/**
	 * Pre-initializes the application.
	 * This method is called at the beginning of the application constructor.
	 * @param array $config the application configuration
	 */
	public function preInit(&$config)
	{
		if (isset($config['vendorPath'])) {
			$this->setVendorPath($config['vendorPath']);
			unset($config['vendorPath']);
		} else {
			// set "@vendor"
			$this->getVendorPath();
		}
		if (isset($config['runtimePath'])) {
			$this->setRuntimePath($config['runtimePath']);
			unset($config['runtimePath']);
		} else {
			// set "@runtime"
			$this->getRuntimePath();
		}
		if (isset($config['timeZone'])) {
			$this->setTimeZone($config['timeZone']);
			unset($config['timeZone']);
		} elseif (!ini_get('date.timezone')) {
			$this->setTimeZone('UTC');
		}
	}

	/**
	 * Loads components that are declared in [[preload]].
	 * @throws InvalidConfigException if a component or module to be preloaded is unknown
	 */
	public function preloadComponents()
	{
		$this->getComponent('log');
		parent::preloadComponents();
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
			if ($this->memoryReserveSize > 0) {
				$this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
			}
			register_shutdown_function(array($this, 'handleFatalError'));
		}
	}

	/**
	 * Runs the application.
	 * This is the main entrance of an application.
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 */
	public function run()
	{
		$this->trigger(self::EVENT_BEFORE_REQUEST);
		$response = $this->handleRequest($this->getRequest());
		$this->trigger(self::EVENT_AFTER_REQUEST);
		$response->send();
		return $response->exitStatus;
	}

	/**
	 * Handles the specified request.
	 *
	 * This method should return an instance of [[Response]] or its child class
	 * which represents the handling result of the request.
	 *
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 */
	abstract public function handleRequest($request);


	private $_runtimePath;

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files.
	 * Defaults to the "runtime" subdirectory under [[basePath]].
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
	 */
	public function setRuntimePath($path)
	{
		$this->_runtimePath = Yii::getAlias($path);
		Yii::setAlias('@runtime', $this->_runtimePath);
	}

	private $_vendorPath;

	/**
	 * Returns the directory that stores vendor files.
	 * @return string the directory that stores vendor files.
	 * Defaults to "vendor" directory under [[basePath]].
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
		Yii::setAlias('@vendor', $this->_vendorPath);
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
	 * Returns the log component.
	 * @return \yii\log\Logger the log component
	 */
	public function getLog()
	{
		return $this->getComponent('log');
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
	 * Returns the formatter component.
	 * @return \yii\base\Formatter the formatter application component.
	 */
	public function getFormatter()
	{
		return $this->getComponent('formatter');
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
			'log' => array(
				'class' => 'yii\log\Logger',
			),
			'errorHandler' => array(
				'class' => 'yii\base\ErrorHandler',
			),
			'formatter' => array(
				'class' => 'yii\base\Formatter',
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
	 * This method is implemented as a PHP exception handler.
	 *
	 * @param \Exception $exception the exception that is not caught
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
				echo $this->renderException($exception);
			}
		} catch (\Exception $e) {
			// exception could be thrown in ErrorHandler::handle()
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
			// load ErrorException manually here because autoloading them will not work
			// when error occurs while autoloading a class
			if (!class_exists('\\yii\\base\\Exception', false)) {
				require_once(__DIR__ . '/Exception.php');
			}
			if (!class_exists('\\yii\\base\\ErrorException', false)) {
				require_once(__DIR__ . '/ErrorException.php');
			}
			$exception = new ErrorException($message, $code, $code, $file, $line);

			// in case error appeared in __toString method we can't throw any exception
			$trace = debug_backtrace(false);
			array_shift($trace);
			foreach ($trace as $frame) {
				if ($frame['function'] == '__toString') {
					$this->handleException($exception);
					return;
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
		// load ErrorException manually here because autoloading them will not work
		// when error occurs while autoloading a class
		if (!class_exists('\\yii\\base\\Exception', false)) {
			require_once(__DIR__ . '/Exception.php');
		}
		if (!class_exists('\\yii\\base\\ErrorException', false)) {
			require_once(__DIR__ . '/ErrorException.php');
		}

		$error = error_get_last();

		if (ErrorException::isFatalError($error)) {
			unset($this->_memoryReserve);
			$exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
			// use error_log because it's too late to use Yii log
			error_log($exception);

			if (($handler = $this->getErrorHandler()) !== null) {
				$handler->handle($exception);
			} else {
				echo $this->renderException($exception);
			}

			exit(1);
		}
	}

	/**
	 * Renders an exception without using rich format.
	 * @param \Exception $exception the exception to be rendered.
	 * @return string the rendering result
	 */
	public function renderException($exception)
	{
		if ($exception instanceof Exception && ($exception instanceof UserException || !YII_DEBUG)) {
			$message = $exception->getName() . ': ' . $exception->getMessage();
			if (Yii::$app->controller instanceof \yii\console\Controller) {
				$message = Yii::$app->controller->ansiFormat($message, Console::FG_RED);
			}
		} else {
			$message = YII_DEBUG ? (string)$exception : 'Error: ' . $exception->getMessage();
		}
		if (PHP_SAPI === 'cli') {
			return $message . "\n";
		} else {
			return '<pre>' . htmlspecialchars($message, ENT_QUOTES, $this->charset) . '</pre>';
		}
	}

	/**
	 * Logs the given exception
	 * @param \Exception $exception the exception to be logged
	 */
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
