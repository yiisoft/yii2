<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * Session provides session data management and the related configurations.
 *
 * Session is a Web application component that can be accessed via `Yii::$app->session`.
 *
 * To start the session, call [[open()]]; To complete and send out session data, call [[close()]];
 * To destroy the session, call [[destroy()]].
 *
 * By default, [[autoStart]] is true which means the session will be started automatically
 * when the session component is accessed the first time.
 *
 * Session can be used like an array to set and get session data. For example,
 *
 * ~~~
 * $session = new Session;
 * $session->open();
 * $value1 = $session['name1'];  // get session variable 'name1'
 * $value2 = $session['name2'];  // get session variable 'name2'
 * foreach ($session as $name => $value) // traverse all session variables
 * $session['name3'] = $value3;  // set session variable 'name3'
 * ~~~
 *
 * Session can be extended to support customized session storage.
 * To do so, override [[useCustomStorage()]] so that it returns true, and
 * override these methods with the actual logic about using custom storage:
 * [[openSession()]], [[closeSession()]], [[readSession()]], [[writeSession()]],
 * [[destroySession()]] and [[gcSession()]].
 *
 * Session also supports a special type of session data, called *flash messages*.
 * A flash message is available only in the current request and the next request.
 * After that, it will be deleted automatically. Flash messages are particularly
 * useful for displaying confirmation messages. To use flash messages, simply
 * call methods such as [[setFlash()]], [[getFlash()]].
 *
 * @property array $allFlashes Flash messages (key => message). This property is read-only.
 * @property array $cookieParams The session cookie parameters. This property is read-only.
 * @property integer $count The number of session variables. This property is read-only.
 * @property string $flash The key identifying the flash message. Note that flash messages and normal session
 * variables share the same name space. If you have a normal session variable using the same name, its value will
 * be overwritten by this method. This property is write-only.
 * @property float $gCProbability The probability (percentage) that the GC (garbage collection) process is
 * started on every session initialization, defaults to 1 meaning 1% chance.
 * @property string $id The current session ID.
 * @property boolean $isActive Whether the session has started. This property is read-only.
 * @property SessionIterator $iterator An iterator for traversing the session variables. This property is
 * read-only.
 * @property string $name The current session name.
 * @property string $savePath The current session save path, defaults to '/tmp'.
 * @property integer $timeout The number of seconds after which data will be seen as 'garbage' and cleaned up.
 * The default value is 1440 seconds (or the value of "session.gc_maxlifetime" set in php.ini).
 * @property boolean|null $useCookies The value indicating whether cookies should be used to store session
 * IDs.
 * @property boolean $useCustomStorage Whether to use custom storage. This property is read-only.
 * @property boolean $useTransparentSessionID Whether transparent sid support is enabled or not, defaults to
 * false.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Session extends Component implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var boolean whether the session should be automatically started when the session component is initialized.
	 */
	public $autoStart = true;
	/**
	 * @var string the name of the session variable that stores the flash message data.
	 */
	public $flashVar = '__flash';
	/**
	 * @var \SessionHandlerInterface|array an object implementing the SessionHandlerInterface or a configuration array. If set, will be used to provide persistency instead of build-in methods.
	 */
	public $handler;
	/**
	 * @var array parameter-value pairs to override default session cookie parameters that are used for session_set_cookie_params() function
	 * Array may have the following possible keys: 'lifetime', 'path', 'domain', 'secure', 'httpOnly'
	 * @see http://www.php.net/manual/en/function.session-set-cookie-params.php
	 */
	private $_cookieParams = ['httpOnly' => true];

	/**
	 * Initializes the application component.
	 * This method is required by IApplicationComponent and is invoked by application.
	 */
	public function init()
	{
		parent::init();
		if ($this->autoStart) {
			$this->open();
		}
		register_shutdown_function([$this, 'close']);
	}

	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method should be overridden to return true by child classes that implement custom session storage.
	 * To implement custom session storage, override these methods: [[openSession()]], [[closeSession()]],
	 * [[readSession()]], [[writeSession()]], [[destroySession()]] and [[gcSession()]].
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return false;
	}

	/**
	 * Starts the session.
	 */
	public function open()
	{
		if (session_status() == PHP_SESSION_ACTIVE) {
			return;
		}

		if ($this->handler !== null) {
			if (!is_object($this->handler)) {
				$this->handler = Yii::createObject($this->handler);
			}
			if (!$this->handler instanceof \SessionHandlerInterface) {
				throw new InvalidConfigException('"' . get_class($this) . '::handler" must implement the SessionHandlerInterface.');
			}
			@session_set_save_handler($this->handler, false);
		} elseif ($this->getUseCustomStorage()) {
			@session_set_save_handler(
				[$this, 'openSession'],
				[$this, 'closeSession'],
				[$this, 'readSession'],
				[$this, 'writeSession'],
				[$this, 'destroySession'],
				[$this, 'gcSession']
			);
		}

		$this->setCookieParamsInternal();

		@session_start();

		if (session_id() == '') {
			$error = error_get_last();
			$message = isset($error['message']) ? $error['message'] : 'Failed to start session.';
			Yii::error($message, __METHOD__);
		} else {
			$this->updateFlashCounters();
		}
	}

	/**
	 * Ends the current session and store session data.
	 */
	public function close()
	{
		if (session_id() !== '') {
			@session_write_close();
		}
	}

	/**
	 * Frees all session variables and destroys all data registered to a session.
	 */
	public function destroy()
	{
		if (session_id() !== '') {
			@session_unset();
			@session_destroy();
		}
	}

	/**
	 * @return boolean whether the session has started
	 */
	public function getIsActive()
	{
		return session_status() == PHP_SESSION_ACTIVE;
	}

	/**
	 * @return string the current session ID
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * @param string $value the session ID for the current session
	 */
	public function setId($value)
	{
		session_id($value);
	}

	/**
	 * Updates the current session ID with a newly generated one .
	 * Please refer to <http://php.net/session_regenerate_id> for more details.
	 * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
	 */
	public function regenerateID($deleteOldSession = false)
	{
		session_regenerate_id($deleteOldSession);
	}

	/**
	 * @return string the current session name
	 */
	public function getName()
	{
		return session_name();
	}

	/**
	 * @param string $value the session name for the current session, must be an alphanumeric string.
	 * It defaults to "PHPSESSID".
	 */
	public function setName($value)
	{
		session_name($value);
	}

	/**
	 * @return string the current session save path, defaults to '/tmp'.
	 */
	public function getSavePath()
	{
		return session_save_path();
	}

	/**
	 * @param string $value the current session save path. This can be either a directory name or a path alias.
	 * @throws InvalidParamException if the path is not a valid directory
	 */
	public function setSavePath($value)
	{
		$path = Yii::getAlias($value);
		if (is_dir($path)) {
			session_save_path($path);
		} else {
			throw new InvalidParamException("Session save path is not a valid directory: $value");
		}
	}

	/**
	 * @return array the session cookie parameters.
	 * @see http://us2.php.net/manual/en/function.session-get-cookie-params.php
	 */
	public function getCookieParams()
	{
		$params = session_get_cookie_params();
		if (isset($params['httponly'])) {
			$params['httpOnly'] = $params['httponly'];
			unset($params['httponly']);
		}
		return array_merge($params, $this->_cookieParams);
	}

	/**
	 * Sets the session cookie parameters.
	 * The cookie parameters passed to this method will be merged with the result
	 * of `session_get_cookie_params()`.
	 * @param array $value cookie parameters, valid keys include: `lifetime`, `path`, `domain`, `secure` and `httpOnly`.
	 * @throws InvalidParamException if the parameters are incomplete.
	 * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function setCookieParams(array $value)
	{
		$this->_cookieParams = $value;
	}

	/**
	 * Sets the session cookie parameters.
	 * This method is called by [[open()]] when it is about to open the session.
	 * @throws InvalidParamException if the parameters are incomplete.
	 * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
	 */
	private function setCookieParamsInternal()
	{
		$data = $this->getCookieParams();
		extract($data);
		if (isset($lifetime, $path, $domain, $secure, $httpOnly)) {
			session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
		} else {
			throw new InvalidParamException('Please make sure cookieParams contains these elements: lifetime, path, domain, secure and httpOnly.');
		}
	}

	/**
	 * Returns the value indicating whether cookies should be used to store session IDs.
	 * @return boolean|null the value indicating whether cookies should be used to store session IDs.
	 * @see setUseCookies()
	 */
	public function getUseCookies()
	{
		if (ini_get('session.use_cookies') === '0') {
			return false;
		} elseif (ini_get('session.use_only_cookies') === '1') {
			return true;
		} else {
			return null;
		}
	}

	/**
	 * Sets the value indicating whether cookies should be used to store session IDs.
	 * Three states are possible:
	 *
	 * - true: cookies and only cookies will be used to store session IDs.
	 * - false: cookies will not be used to store session IDs.
	 * - null: if possible, cookies will be used to store session IDs; if not, other mechanisms will be used (e.g. GET parameter)
	 *
	 * @param boolean|null $value the value indicating whether cookies should be used to store session IDs.
	 */
	public function setUseCookies($value)
	{
		if ($value === false) {
			ini_set('session.use_cookies', '0');
			ini_set('session.use_only_cookies', '0');
		} elseif ($value === true) {
			ini_set('session.use_cookies', '1');
			ini_set('session.use_only_cookies', '1');
		} else {
			ini_set('session.use_cookies', '1');
			ini_set('session.use_only_cookies', '0');
		}
	}

	/**
	 * @return float the probability (percentage) that the GC (garbage collection) process is started on every session initialization, defaults to 1 meaning 1% chance.
	 */
	public function getGCProbability()
	{
		return (float)(ini_get('session.gc_probability') / ini_get('session.gc_divisor') * 100);
	}

	/**
	 * @param float $value the probability (percentage) that the GC (garbage collection) process is started on every session initialization.
	 * @throws InvalidParamException if the value is not between 0 and 100.
	 */
	public function setGCProbability($value)
	{
		if ($value >= 0 && $value <= 100) {
			// percent * 21474837 / 2147483647 ≈ percent * 0.01
			ini_set('session.gc_probability', floor($value * 21474836.47));
			ini_set('session.gc_divisor', 2147483647);
		} else {
			throw new InvalidParamException('GCProbability must be a value between 0 and 100.');
		}
	}

	/**
	 * @return boolean whether transparent sid support is enabled or not, defaults to false.
	 */
	public function getUseTransparentSessionID()
	{
		return ini_get('session.use_trans_sid') == 1;
	}

	/**
	 * @param boolean $value whether transparent sid support is enabled or not.
	 */
	public function setUseTransparentSessionID($value)
	{
		ini_set('session.use_trans_sid', $value ? '1' : '0');
	}

	/**
	 * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * The default value is 1440 seconds (or the value of "session.gc_maxlifetime" set in php.ini).
	 */
	public function getTimeout()
	{
		return (int)ini_get('session.gc_maxlifetime');
	}

	/**
	 * @param integer $value the number of seconds after which data will be seen as 'garbage' and cleaned up
	 */
	public function setTimeout($value)
	{
		ini_set('session.gc_maxlifetime', $value);
	}

	/**
	 * Session open handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @param string $savePath session save path
	 * @param string $sessionName session name
	 * @return boolean whether session is opened successfully
	 */
	public function openSession($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * Session close handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @return boolean whether session is closed successfully
	 */
	public function closeSession()
	{
		return true;
	}

	/**
	 * Session read handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		return '';
	}

	/**
	 * Session write handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id, $data)
	{
		return true;
	}

	/**
	 * Session destroy handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		return true;
	}

	/**
	 * Session GC (garbage collection) handler.
	 * This method should be overridden if [[useCustomStorage()]] returns true.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
		return true;
	}

	/**
	 * Returns an iterator for traversing the session variables.
	 * This method is required by the interface IteratorAggregate.
	 * @return SessionIterator an iterator for traversing the session variables.
	 */
	public function getIterator()
	{
		return new SessionIterator;
	}

	/**
	 * Returns the number of items in the session.
	 * @return integer the number of session variables
	 */
	public function getCount()
	{
		return count($_SESSION);
	}

	/**
	 * Returns the number of items in the session.
	 * This method is required by Countable interface.
	 * @return integer number of items in the session.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the session variable value with the session variable name.
	 * If the session variable does not exist, the `$defaultValue` will be returned.
	 * @param string $key the session variable name
	 * @param mixed $defaultValue the default value to be returned when the session variable does not exist.
	 * @return mixed the session variable value, or $defaultValue if the session variable does not exist.
	 */
	public function get($key, $defaultValue = null)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}

	/**
	 * Adds a session variable.
	 * If the specified name already exists, the old value will be overwritten.
	 * @param string $key session variable name
	 * @param mixed $value session variable value
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Removes a session variable.
	 * @param string $key the name of the session variable to be removed
	 * @return mixed the removed value, null if no such session variable.
	 */
	public function remove($key)
	{
		if (isset($_SESSION[$key])) {
			$value = $_SESSION[$key];
			unset($_SESSION[$key]);
			return $value;
		} else {
			return null;
		}
	}

	/**
	 * Removes all session variables
	 */
	public function removeAll()
	{
		foreach (array_keys($_SESSION) as $key) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @param mixed $key session variable name
	 * @return boolean whether there is the named session variable
	 */
	public function has($key)
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * @return array the list of all session variables in array
	 */
	public function toArray()
	{
		return $_SESSION;
	}

	/**
	 * Updates the counters for flash messages and removes outdated flash messages.
	 * This method should only be called once in [[init()]].
	 */
	protected function updateFlashCounters()
	{
		$counters = $this->get($this->flashVar, []);
		if (is_array($counters)) {
			foreach ($counters as $key => $count) {
				if ($count) {
					unset($counters[$key], $_SESSION[$key]);
				} else {
					$counters[$key]++;
				}
			}
			$_SESSION[$this->flashVar] = $counters;
		} else {
			// fix the unexpected problem that flashVar doesn't return an array
			unset($_SESSION[$this->flashVar]);
		}
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current request and the next request.
	 * @param string $key the key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message does not exist.
	 * @param boolean $delete whether to delete this flash message right after this method is called.
	 * If false, the flash message will be automatically deleted after the next request.
	 * @return mixed the flash message
	 */
	public function getFlash($key, $defaultValue = null, $delete = false)
	{
		$counters = $this->get($this->flashVar, []);
		if (isset($counters[$key])) {
			$value = $this->get($key, $defaultValue);
			if ($delete) {
				$this->removeFlash($key);
			}
			return $value;
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Returns all flash messages.
	 * @return array flash messages (key => message).
	 */
	public function getAllFlashes()
	{
		$counters = $this->get($this->flashVar, []);
		$flashes = [];
		foreach (array_keys($counters) as $key) {
			if (isset($_SESSION[$key])) {
				$flashes[$key] = $_SESSION[$key];
			}
		}
		return $flashes;
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current request and the next request.
	 * @param string $key the key identifying the flash message. Note that flash messages
	 * and normal session variables share the same name space. If you have a normal
	 * session variable using the same name, its value will be overwritten by this method.
	 * @param mixed $value flash message
	 */
	public function setFlash($key, $value = true)
	{
		$counters = $this->get($this->flashVar, []);
		$counters[$key] = 0;
		$_SESSION[$key] = $value;
		$_SESSION[$this->flashVar] = $counters;
	}

	/**
	 * Removes a flash message.
	 * Note that flash messages will be automatically removed after the next request.
	 * @param string $key the key identifying the flash message. Note that flash messages
	 * and normal session variables share the same name space.  If you have a normal
	 * session variable using the same name, it will be removed by this method.
	 * @return mixed the removed flash message. Null if the flash message does not exist.
	 */
	public function removeFlash($key)
	{
		$counters = $this->get($this->flashVar, []);
		$value = isset($_SESSION[$key], $counters[$key]) ? $_SESSION[$key] : null;
		unset($counters[$key], $_SESSION[$key]);
		$_SESSION[$this->flashVar] = $counters;
		return $value;
	}

	/**
	 * Removes all flash messages.
	 * Note that flash messages and normal session variables share the same name space.
	 * If you have a normal session variable using the same name, it will be removed
	 * by this method.
	 */
	public function removeAllFlashes()
	{
		$counters = $this->get($this->flashVar, []);
		foreach (array_keys($counters) as $key) {
			unset($_SESSION[$key]);
		}
		unset($_SESSION[$this->flashVar]);
	}

	/**
	 * Returns a value indicating whether there is a flash message associated with the specified key.
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash($key)
	{
		return $this->getFlash($key) !== null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($_SESSION[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$_SESSION[$offset] = $item;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($_SESSION[$offset]);
	}
}
