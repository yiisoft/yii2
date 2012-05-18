<?php
/**
 * Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\base\Exception;

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
 * <li>{@link beforeRequest}: preprocess the user request; `beforeRequest` event raised.</li>
 * <li>{@link processRequest}: process the user request;</li>
 * <li>{@link afterRequest}: postprocess the user request; `afterRequest` event raised.</li>
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
class Application extends Module
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
	 * @see language
	 */
	public $sourceLanguage = 'en_us';
	/**
	 * @var array IDs of application components that need to be loaded when the application starts.
	 * The default value is `array('errorHandler')`, which loads the [[errorHandler]] component
	 * to ensure errors and exceptions can be handled nicely.
	 */
	public $preload = array('errorHandler');
	/**
	 * @var Controller the currently active controller instance
	 */
	public $controller;

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
	 */
	public function __construct($id, $basePath)
	{
		\Yii::$application = $this;
		$this->id = $id;
		$this->setBasePath($basePath);
		\Yii::$aliases['@application'] = $this->getBasePath();
		\Yii::$aliases['@entry'] = dirname($_SERVER['SCRIPT_FILENAME']);
		\Yii::$aliases['@www'] = '';
		$this->registerCoreComponents();
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
	 * Raises the [[beforeRequest]] event right BEFORE the application processes the request.
	 */
	public function beforeRequest()
	{
		$this->trigger('beforeRequest');
	}

	/**
	 * Raises the [[afterRequest]] event right AFTER the application processes the request.
	 */
	public function afterRequest()
	{
		$this->trigger('afterRequest');
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
	 * Runs a controller with the given route and parameters.
	 * @param string $route the route (e.g. `post/create`)
	 * @param array $params the parameters to be passed to the controller action
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 * @throws Exception if the route cannot be resolved into a controller
	 */
	public function runController($route, $params = array())
	{
		$result = $this->createController($route);
		if ($result === false) {
			throw new Exception(\Yii::t('yii', 'Unable to resolve the request.'));
		}
		list($controller, $action) = $result;
		$priorController = $this->controller;
		$this->controller = $controller;
		$status = $controller->run($action, $params);
		$this->controller = $priorController;
		return $status;
	}

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
	 * @throws Exception if the directory does not exist or is not writable
	 */
	public function setRuntimePath($path)
	{
		if (!is_dir($path) || !is_writable($path)) {
			throw new Exception("Application runtime path \"$path\" is invalid. Please make sure it is a directory writable by the Web server process.");
		}
		$this->_runtimePath = $path;
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
	 * @return \yii\db\dao\Connection the database connection
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
	 * Returns the security manager component.
	 * @return SecurityManager the security manager application component.
	 */
	public function getSecurityManager()
	{
		return $this->getComponent('securityManager');
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
	 * Returns the core message translations component.
	 * @return \yii\i18n\MessageSource the core message translations
	 */
	public function getCoreMessages()
	{
		return $this->getComponent('coreMessages');
	}

	/**
	 * Returns the application message translations component.
	 * @return \yii\i18n\MessageSource the application message translations
	 */
	public function getMessages()
	{
		return $this->getComponent('messages');
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
	 * Registers the core application components.
	 * @see setComponents
	 */
	public function registerCoreComponents()
	{
		$this->setComponents(array(
			'errorHandler' => array(
				'class' => 'yii\base\ErrorHandler',
			),
			'request' => array(
				'class' => 'yii\base\Request',
			),
			'response' => array(
				'class' => 'yii\base\Response',
			),
			'format' => array(
				'class' => 'yii\base\Formatter',
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
		));
	}
}
