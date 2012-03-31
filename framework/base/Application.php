<?php
/**
 * Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

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
 * Application will undergo the following lifecycles when processing a user request:
 * <ol>
 * <li>load application configuration;</li>
 * <li>set up class autoloader and error handling;</li>
 * <li>load static application components;</li>
 * <li>{@link onBeginRequest}: preprocess the user request;</li>
 * <li>{@link processRequest}: process the user request;</li>
 * <li>{@link onEndRequest}: postprocess the user request;</li>
 * </ol>
 *
 * Starting from lifecycle 3, if a PHP error or an uncaught exception occurs,
 * the application will switch to its error handling logic and jump to step 6 afterwards.
 *
 * @property string $basePath Returns the root path of the application.
 * @property CCache $cache Returns the cache component.
 * @property CPhpMessageSource $coreMessages Returns the core message translations.
 * @property CDateFormatter $dateFormatter Returns the locale-dependent date formatter.
 * @property CDbConnection $db Returns the database connection component.
 * @property CErrorHandler $errorHandler Returns the error handler component.
 * @property string $extensionPath Returns the root directory that holds all third-party extensions.
 * @property string $id Returns the unique identifier for the application.
 * @property string $language Returns the language that the user is using and the application should be targeted to.
 * @property CLocale $locale Returns the locale instance.
 * @property string $localeDataPath Returns the directory that contains the locale data.
 * @property CMessageSource $messages Returns the application message translations component.
 * @property CNumberFormatter $numberFormatter The locale-dependent number formatter.
 * @property CHttpRequest $request Returns the request component.
 * @property string $runtimePath Returns the directory that stores runtime files.
 * @property CSecurityManager $securityManager Returns the security manager component.
 * @property CStatePersister $statePersister Returns the state persister component.
 * @property string $timeZone Returns the time zone used by this application.
 * @property CUrlManager $urlManager Returns the URL manager component.
 * @property string $baseUrl Returns the relative URL for the application
 * @property string $homeUrl the homepage URL
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Application extends Module
{
	/**
	 * @var string the application name. Defaults to 'My Application'.
	 */
	public $name = 'My Application';
	/**
	 * @var string the charset currently used for the application. Defaults to 'UTF-8'.
	 */
	public $charset = 'UTF-8';
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are in. Defaults to 'en_us' (US English).
	 */
	public $sourceLanguage = 'en_us';

	private $_id;
	private $_basePath;
	private $_runtimePath;
	private $_extensionPath;
	private $_globalState;
	private $_stateChanged;
	private $_ended = false;
	private $_language;
	private $_homeUrl;

	/**
	 * Processes the request.
	 * This is the place where the actual request processing work is done.
	 * Derived classes should override this method.
	 */
	abstract public function processRequest();

	/**
	 * Constructor.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link getBasePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public function __construct($config = null)
	{
		\Yii::$application = $this;

		// set basePath at early as possible to avoid trouble
		if (is_string($config)) {
			$config = require($config);
		}
		if (isset($config['basePath'])) {
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		} else
		{
			$this->setBasePath('protected');
		}
		\Yii::setAlias('application', $this->getBasePath());
		\Yii::setAlias('webroot', dirname($_SERVER['SCRIPT_FILENAME']));
		\Yii::setAlias('ext', $this->getBasePath() . DIRECTORY_SEPARATOR . 'extensions');

		$this->preinit();

		$this->initSystemHandlers();
		$this->registerCoreComponents();

		$this->configure($config);
		$this->attachBehaviors($this->behaviors);
		$this->preloadComponents();

		$this->init();
	}


	/**
	 * Runs the application.
	 * This method loads static application components. Derived classes usually overrides this
	 * method to do more application-specific tasks.
	 * Remember to call the parent implementation so that static application components are loaded.
	 */
	public function run()
	{
		if ($this->hasEventHandlers('onBeginRequest')) {
			$this->onBeginRequest(new CEvent($this));
		}
		$this->processRequest();
		if ($this->hasEventHandlers('onEndRequest')) {
			$this->onEndRequest(new CEvent($this));
		}
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by calling
	 * {@link onEndRequest} before exiting.
	 * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param boolean $exit whether to exit the current request. This parameter has been available since version 1.1.5.
	 * It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
	 */
	public function end($status = 0, $exit = true)
	{
		if ($this->hasEventHandlers('onEndRequest')) {
			$this->onEndRequest(new CEvent($this));
		}
		if ($exit) {
			exit($status);
		}
	}

	/**
	 * Raised right BEFORE the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public function onBeginRequest($event)
	{
		$this->raiseEvent('onBeginRequest', $event);
	}

	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public function onEndRequest($event)
	{
		if (!$this->_ended) {
			$this->_ended = true;
			$this->raiseEvent('onEndRequest', $event);
		}
	}

	/**
	 * Returns the unique identifier for the application.
	 * @return string the unique identifier for the application.
	 */
	public function getId()
	{
		if ($this->_id !== null) {
			return $this->_id;
		} else
		{
			return $this->_id = sprintf('%x', crc32($this->getBasePath() . $this->name));
		}
	}

	/**
	 * Sets the unique identifier for the application.
	 * @param string $id the unique identifier for the application.
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * Returns the root path of the application.
	 * @return string the root directory of the application. Defaults to 'protected'.
	 */
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the application.
	 * This method can only be invoked at the begin of the constructor.
	 * @param string $path the root directory of the application.
	 * @throws CException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath)) {
			throw new \yii\base\Exception(\Yii::t('yii', 'Application base path "{path}" is not a valid directory.',
				array('{path}' => $path)));
		}
	}

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath !== null) {
			return $this->_runtimePath;
		} else
		{
			$this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
			return $this->_runtimePath;
		}
	}

	/**
	 * Sets the directory that stores runtime files.
	 * @param string $path the directory that stores runtime files.
	 * @throws CException if the directory does not exist or is not writable
	 */
	public function setRuntimePath($path)
	{
		if (($runtimePath = realpath($path)) === false || !is_dir($runtimePath) || !is_writable($runtimePath)) {
			throw new \yii\base\Exception(\Yii::t('yii', 'Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.',
				array('{path}' => $path)));
		}
		$this->_runtimePath = $runtimePath;
	}

	/**
	 * Returns the root directory that holds all third-party extensions.
	 * @return string the directory that contains all extensions. Defaults to the 'extensions' directory under 'protected'.
	 */
	public function getExtensionPath()
	{
		return \Yii::getPathOfAlias('ext');
	}

	/**
	 * Sets the root directory that holds all third-party extensions.
	 * @param string $path the directory that contains all third-party extensions.
	 */
	public function setExtensionPath($path)
	{
		if (($extensionPath = realpath($path)) === false || !is_dir($extensionPath)) {
			throw new \yii\base\Exception(\Yii::t('yii', 'Extension path "{path}" does not exist.',
				array('{path}' => $path)));
		}
		\Yii::setAlias('ext', $extensionPath);
	}

	/**
	 * Returns the language that the user is using and the application should be targeted to.
	 * @return string the language that the user is using and the application should be targeted to.
	 * Defaults to the {@link sourceLanguage source language}.
	 */
	public function getLanguage()
	{
		return $this->_language === null ? $this->sourceLanguage : $this->_language;
	}

	/**
	 * Specifies which language the application is targeted to.
	 *
	 * This is the language that the application displays to end users.
	 * If set null, it uses the {@link sourceLanguage source language}.
	 *
	 * Unless your application needs to support multiple languages, you should always
	 * set this language to null to maximize the application's performance.
	 * @param string $language the user language (e.g. 'en_US', 'zh_CN').
	 * If it is null, the {@link sourceLanguage} will be used.
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

	/**
	 * Returns the localized version of a specified file.
	 *
	 * The searching is based on the specified language code. In particular,
	 * a file with the same name will be looked for under the subdirectory
	 * named as the locale ID. For example, given the file "path/to/view.php"
	 * and locale ID "zh_cn", the localized file will be looked for as
	 * "path/to/zh_cn/view.php". If the file is not found, the original file
	 * will be returned.
	 *
	 * For consistency, it is recommended that the locale ID is given
	 * in lower case and in the format of LanguageID_RegionID (e.g. "en_us").
	 *
	 * @param string $srcFile the original file
	 * @param string $srcLanguage the language that the original file is in. If null, the application {@link sourceLanguage source language} is used.
	 * @param string $language the desired language that the file should be localized to. If null, the {@link getLanguage application language} will be used.
	 * @return string the matching localized file. The original file is returned if no localized version is found
	 * or if source language is the same as the desired language.
	 */
	public function findLocalizedFile($srcFile, $srcLanguage = null, $language = null)
	{
		if ($srcLanguage === null) {
			$srcLanguage = $this->sourceLanguage;
		}
		if ($language === null) {
			$language = $this->getLanguage();
		}
		if ($language === $srcLanguage) {
			return $srcFile;
		}
		$desiredFile = dirname($srcFile) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . basename($srcFile);
		return is_file($desiredFile) ? $desiredFile : $srcFile;
	}

	/**
	 * Returns the locale instance.
	 * @param string $localeID the locale ID (e.g. en_US). If null, the {@link getLanguage application language ID} will be used.
	 * @return CLocale the locale instance
	 */
	public function getLocale($localeID = null)
	{
		return CLocale::getInstance($localeID === null ? $this->getLanguage() : $localeID);
	}

	/**
	 * Returns the directory that contains the locale data.
	 * @return string the directory that contains the locale data. It defaults to 'framework/i18n/data'.
	 */
	public function getLocaleDataPath()
	{
		return CLocale::$dataPath === null ? \Yii::getPathOfAlias('system.i18n.data') : CLocale::$dataPath;
	}

	/**
	 * Sets the directory that contains the locale data.
	 * @param string $value the directory that contains the locale data.
	 */
	public function setLocaleDataPath($value)
	{
		CLocale::$dataPath = $value;
	}

	/**
	 * @return CNumberFormatter the locale-dependent number formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	public function getNumberFormatter()
	{
		return $this->getLocale()->getNumberFormatter();
	}

	/**
	 * Returns the locale-dependent date formatter.
	 * @return CDateFormatter the locale-dependent date formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	public function getDateFormatter()
	{
		return $this->getLocale()->getDateFormatter();
	}

	/**
	 * Returns the database connection component.
	 * @return CDbConnection the database connection
	 */
	public function getDb()
	{
		return $this->getComponent('db');
	}

	/**
	 * Returns the error handler component.
	 * @return CErrorHandler the error handler application component.
	 */
	public function getErrorHandler()
	{
		return $this->getComponent('errorHandler');
	}

	/**
	 * Returns the security manager component.
	 * @return CSecurityManager the security manager application component.
	 */
	public function getSecurityManager()
	{
		return $this->getComponent('securityManager');
	}

	/**
	 * Returns the state persister component.
	 * @return CStatePersister the state persister application component.
	 */
	public function getStatePersister()
	{
		return $this->getComponent('statePersister');
	}

	/**
	 * Returns the cache component.
	 * @return CCache the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * Returns the core message translations component.
	 * @return CPhpMessageSource the core message translations
	 */
	public function getCoreMessages()
	{
		return $this->getComponent('coreMessages');
	}

	/**
	 * Returns the application message translations component.
	 * @return CMessageSource the application message translations
	 */
	public function getMessages()
	{
		return $this->getComponent('messages');
	}

	/**
	 * Returns the request component.
	 * @return CHttpRequest the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the URL manager component.
	 * @return CUrlManager the URL manager component
	 */
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
	}

	/**
	 * @return CController the currently active controller. Null is returned in this base class.
	 */
	public function getController()
	{
		return null;
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public function createUrl($route, $params = array(), $ampersand = '&')
	{
		return $this->getUrlManager()->createUrl($route, $params, $ampersand);
	}

	/**
	 * Creates an absolute URL based on the given controller and action information.
	 * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public function createAbsoluteUrl($route, $params = array(), $schema = '', $ampersand = '&')
	{
		$url = $this->createUrl($route, $params, $ampersand);
		if (strpos($url, 'http') === 0) {
			return $url;
		} else
		{
			return $this->getRequest()->getHostInfo($schema) . $url;
		}
	}

	/**
	 * Returns the relative URL for the application.
	 * This is a shortcut method to {@link CHttpRequest::getBaseUrl()}.
	 * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
	 * This parameter has been available since 1.0.2.
	 * @return string the relative URL for the application
	 * @see CHttpRequest::getBaseUrl()
	 */
	public function getBaseUrl($absolute = false)
	{
		return $this->getRequest()->getBaseUrl($absolute);
	}

	/**
	 * @return string the homepage URL
	 */
	public function getHomeUrl()
	{
		if ($this->_homeUrl === null) {
			if ($this->getUrlManager()->showScriptName) {
				return $this->getRequest()->getScriptUrl();
			} else
			{
				return $this->getRequest()->getBaseUrl() . '/';
			}
		} else
		{
			return $this->_homeUrl;
		}
	}

	/**
	 * @param string $value the homepage URL
	 */
	public function setHomeUrl($value)
	{
		$this->_homeUrl = $value;
	}

	/**
	 * Returns a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * @param string $key the name of the value to be returned
	 * @param mixed $defaultValue the default value. If the named global value is not found, this will be returned instead.
	 * @return mixed the named global value
	 * @see setGlobalState
	 */
	public function getGlobalState($key, $defaultValue = null)
	{
		if ($this->_globalState === null) {
			$this->loadGlobalState();
		}
		if (isset($this->_globalState[$key])) {
			return $this->_globalState[$key];
		} else
		{
			return $defaultValue;
		}
	}

	/**
	 * Sets a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * Make sure that the value is serializable and unserializable.
	 * @param string $key the name of the value to be saved
	 * @param mixed $value the global value to be saved. It must be serializable.
	 * @param mixed $defaultValue the default value. If the named global value is the same as this value, it will be cleared from the current storage.
	 * @see getGlobalState
	 */
	public function setGlobalState($key, $value, $defaultValue = null)
	{
		if ($this->_globalState === null) {
			$this->loadGlobalState();
		}

		$changed = $this->_stateChanged;
		if ($value === $defaultValue) {
			if (isset($this->_globalState[$key])) {
				unset($this->_globalState[$key]);
				$this->_stateChanged = true;
			}
		} elseif (!isset($this->_globalState[$key]) || $this->_globalState[$key] !== $value)
		{
			$this->_globalState[$key] = $value;
			$this->_stateChanged = true;
		}

		if ($this->_stateChanged !== $changed) {
			$this->attachEventHandler('onEndRequest', array($this, 'saveGlobalState'));
		}
	}

	/**
	 * Clears a global value.
	 *
	 * The value cleared will no longer be available in this request and the following requests.
	 * @param string $key the name of the value to be cleared
	 */
	public function clearGlobalState($key)
	{
		$this->setGlobalState($key, true, true);
	}

	/**
	 * Loads the global state data from persistent storage.
	 * @see getStatePersister
	 * @throws \yii\base\Exception if the state persister is not available
	 */
	public function loadGlobalState()
	{
		$persister = $this->getStatePersister();
		if (($this->_globalState = $persister->load()) === null) {
			$this->_globalState = array();
		}
		$this->_stateChanged = false;
		$this->detachEventHandler('onEndRequest', array($this, 'saveGlobalState'));
	}

	/**
	 * Saves the global state data into persistent storage.
	 * @see getStatePersister
	 * @throws CException if the state persister is not available
	 */
	public function saveGlobalState()
	{
		if ($this->_stateChanged) {
			$this->_stateChanged = false;
			$this->detachEventHandler('onEndRequest', array($this, 'saveGlobalState'));
			$this->getStatePersister()->save($this->_globalState);
		}
	}

	/**
	 * Handles uncaught PHP exceptions.
	 *
	 * This method is implemented as a PHP exception handler. It requires
	 * that constant YII_ENABLE_EXCEPTION_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onException} event.
	 * If the exception is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the exception.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param Exception $exception exception that is not caught
	 */
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors
		restore_error_handler();
		restore_exception_handler();

		$category = 'exception.' . get_class($exception);
		if ($exception instanceof \yii\web\HttpException) {
			$category .= '.' . $exception->statusCode;
		}
		// php <5.2 doesn't support string conversion auto-magically
		$message = $exception->__toString();
		if (isset($_SERVER['REQUEST_URI'])) {
			$message .= ' REQUEST_URI=' . $_SERVER['REQUEST_URI'];
		}
		\Yii::error($message, $category);

		try
		{
			// TODO: do we need separate exception class as it was in 1.1?
			//$event = new CExceptionEvent($this, $exception);
			$event = new Event($this, array('exception' => $exception));
			$this->onException($event);
			if (!$event->handled) {
				// try an error handler
				if (($handler = $this->getErrorHandler()) !== null) {
					$handler->handle($event);
				} else
				{
					$this->displayException($exception);
				}
			}
		}
		catch (Exception $e)
		{
			$this->displayException($e);
		}

		try
		{
			$this->end(1);
		}
		catch (Exception $e)
		{
			// use the most primitive way to log error
			$msg = get_class($e) . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
			$msg .= $e->getTraceAsString() . "\n";
			$msg .= "Previous exception:\n";
			$msg .= get_class($exception) . ': ' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ")\n";
			$msg .= $exception->getTraceAsString() . "\n";
			$msg .= '$_SERVER=' . var_export($_SERVER, true);
			error_log($msg);
			exit(1);
		}
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is implemented as a PHP error handler. It requires
	 * that constant YII_ENABLE_ERROR_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onError} event.
	 * If the error is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the error.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 */
	public function handleError($code, $message, $file, $line)
	{
		if ($code & error_reporting()) {
			// disable error capturing to avoid recursive errors
			restore_error_handler();
			restore_exception_handler();

			$log = "$message ($file:$line)\nStack trace:\n";
			$trace = debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if (count($trace) > 3) {
				$trace = array_slice($trace, 3);
			}
			foreach ($trace as $i => $t)
			{
				if (!isset($t['file'])) {
					$t['file'] = 'unknown';
				}
				if (!isset($t['line'])) {
					$t['line'] = 0;
				}
				if (!isset($t['function'])) {
					$t['function'] = 'unknown';
				}
				$log .= "#$i  {$t['file']}( {$t['line']}): ";
				if (isset($t['object']) && is_object($t['object'])) {
					$log .= get_class($t['object']) . '->';
				}
				$log .= " {$t['function']}()\n";
			}
			if (isset($_SERVER['REQUEST_URI'])) {
				$log .= 'REQUEST_URI=' . $_SERVER['REQUEST_URI'];
			}
			\Yii::error($log, 'php');

			try
			{
				\Yii::import('CErrorEvent', true);
				$event = new CErrorEvent($this, $code, $message, $file, $line);
				$this->onError($event);
				if (!$event->handled) {
					// try an error handler
					if (($handler = $this->getErrorHandler()) !== null) {
						$handler->handle($event);
					} else
					{
						$this->displayError($code, $message, $file, $line);
					}
				}
			}
			catch (Exception $e)
			{
				$this->displayException($e);
			}

			try
			{
				$this->end(1);
			}
			catch (Exception $e)
			{
				// use the most primitive way to log error
				$msg = get_class($e) . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
				$msg .= $e->getTraceAsString() . "\n";
				$msg .= "Previous error:\n";
				$msg .= $log . "\n";
				$msg .= '$_SERVER=' . var_export($_SERVER, true);
				error_log($msg);
				exit(1);
			}
		}
	}

	/**
	 * Raised when an uncaught PHP exception occurs.
	 *
	 * An event handler can set the {@link CExceptionEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CExceptionEvent $event event parameter
	 */
	public function onException($event)
	{
		$this->raiseEvent('onException', $event);
	}

	/**
	 * Raised when a PHP execution error occurs.
	 *
	 * An event handler can set the {@link CErrorEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CErrorEvent $event event parameter
	 */
	public function onError($event)
	{
		$this->raiseEvent('onError', $event);
	}

	/**
	 * Displays the captured PHP error.
	 * This method displays the error in HTML when there is
	 * no active error handler.
	 * @param integer $code error code
	 * @param string $message error message
	 * @param string $file error file
	 * @param string $line error line
	 */
	public function displayError($code, $message, $file, $line)
	{
		if (YII_DEBUG) {
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';

			$trace = debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if (count($trace) > 3) {
				$trace = array_slice($trace, 3);
			}
			foreach ($trace as $i => $t)
			{
				if (!isset($t['file'])) {
					$t['file'] = 'unknown';
				}
				if (!isset($t['line'])) {
					$t['line'] = 0;
				}
				if (!isset($t['function'])) {
					$t['function'] = 'unknown';
				}
				echo "#$i  {$t['file']}( {$t['line']}): ";
				if (isset($t['object']) && is_object($t['object'])) {
					echo get_class($t['object']) . '->';
				}
				echo " {$t['function']}()\n";
			}

			echo '</pre>';
		} else
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message</p>\n";
		}
	}

	/**
	 * Displays the uncaught PHP exception.
	 * This method displays the exception in HTML when there is
	 * no active error handler.
	 * @param Exception $exception the uncaught exception
	 */
	public function displayException($exception)
	{
		if (YII_DEBUG) {
			echo '<h1>' . get_class($exception) . "</h1>\n";
			echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
			echo '<pre>' . $exception->getTraceAsString() . '</pre>';
		} else
		{
			echo '<h1>' . get_class($exception) . "</h1>\n";
			echo '<p>' . $exception->getMessage() . '</p>';
		}
	}

	/**
	 * Initializes the class autoloader and error handlers.
	 */
	protected function initSystemHandlers()
	{
		if (YII_ENABLE_EXCEPTION_HANDLER) {
			set_exception_handler(array($this, 'handleException'));
		}
		if (YII_ENABLE_ERROR_HANDLER) {
			set_error_handler(array($this, 'handleError'), error_reporting());
		}
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		$components = array(
			'coreMessages' => array(
				'class' => 'CPhpMessageSource',
				'language' => 'en_us',
				'basePath' => YII_PATH . DIRECTORY_SEPARATOR . 'messages',
			),
			'db' => array(
				'class' => 'CDbConnection',
			),
			'messages' => array(
				'class' => 'CPhpMessageSource',
			),
			// TODO: uncomment when error handler is properly implemented
//			'errorHandler' => array(
//				'class' => 'CErrorHandler',
//			),
			'securityManager' => array(
				'class' => 'CSecurityManager',
			),
			'statePersister' => array(
				'class' => 'CStatePersister',
			),
			'urlManager' => array(
				'class' => 'CUrlManager',
			),
			'request' => array(
				'class' => 'CHttpRequest',
			),
			'format' => array(
				'class' => 'CFormatter',
			),
		);

		$this->setComponents($components);
	}
}
