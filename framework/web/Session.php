<?php
/**
 * Session class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * Session provides session-level data management and the related configurations.
 *
 * To start the session, call [[open()]]; To complete and send out session data, call [[close()]];
 * To destroy the session, call [[destroy()]].
 *
 * If [[autoStart]] is set true, the session will be started automatically
 * when the application component is initialized by the application.
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
 * Session is a Web application component that can be accessed via
 * `Yii::$app->session`.
 *
 * @property boolean $useCustomStorage read-only. Whether to use custom storage.
 * @property boolean $isActive Whether the session has started.
 * @property string $id The current session ID.
 * @property string $name The current session name.
 * @property string $savePath The current session save path, defaults to '/tmp'.
 * @property array $cookieParams The session cookie parameters.
 * @property string $cookieMode How to use cookie to store session ID. Defaults to 'Allow'.
 * @property float $gcProbability The probability (percentage) that the gc (garbage collection) process is started on every session initialization.
 * @property boolean $useTransparentSessionID Whether transparent sid support is enabled or not, defaults to false.
 * @property integer $timeout The number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 1440 seconds.
 * @property SessionIterator $iterator An iterator for traversing the session variables.
 * @property integer $count The number of session variables.
 * @property array $keys The list of session variable names.
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
	 * Initializes the application component.
	 * This method is required by IApplicationComponent and is invoked by application.
	 */
	public function init()
	{
		parent::init();
		if ($this->autoStart) {
			$this->open();
		}
		register_shutdown_function(array($this, 'close'));
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
		// this is available in PHP 5.4.0+
		if (function_exists('session_status')) {
			if (session_status() == PHP_SESSION_ACTIVE) {
				return;
			}
		}

		if ($this->getUseCustomStorage()) {
			@session_set_save_handler(
				array($this, 'openSession'),
				array($this, 'closeSession'),
				array($this, 'readSession'),
				array($this, 'writeSession'),
				array($this, 'destroySession'),
				array($this, 'gcSession')
			);
		}

		@session_start();

		if (session_id() == '') {
			$error = error_get_last();
			$message = isset($error['message']) ? $error['message'] : 'Failed to start session.';
			Yii::warning($message, __CLASS__);
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
		if (function_exists('session_status')) {
			// available in PHP 5.4.0+
			return session_status() == PHP_SESSION_ACTIVE;
		} else {
			// this is not very reliable
			return session_id() !== '';
		}
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
	 * Please refer to [[http://php.net/session_regenerate_id]] for more details.
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
		if ($path !== false && is_dir($path)) {
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
		return session_get_cookie_params();
	}

	/**
	 * Sets the session cookie parameters.
	 * The effect of this method only lasts for the duration of the script.
	 * Call this method before the session starts.
	 * @param array $value cookie parameters, valid keys include: lifetime, path, domain, secure and httponly.
	 * @throws InvalidParamException if the parameters are incomplete.
	 * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function setCookieParams($value)
	{
		$data = session_get_cookie_params();
		extract($data);
		extract($value);
		if (isset($lifetime, $path, $domain, $secure, $httponly)) {
			session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
		} else {
			throw new InvalidParamException('Please make sure these parameters are provided: lifetime, path, domain, secure and httponly.');
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
	 * Note, if the specified name already exists, the old value will be removed first.
	 * @param mixed $key session variable name
	 * @param mixed $value session variable value
	 */
	public function add($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Removes a session variable.
	 * @param mixed $key the name of the session variable to be removed
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
	public function clear()
	{
		foreach (array_keys($_SESSION) as $key) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @param mixed $key session variable name
	 * @return boolean whether there is the named session variable
	 */
	public function contains($key)
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
