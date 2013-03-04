<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Component;

/**
 * CWebUser represents the persistent state for a Web application user.
 *
 * CWebUser is used as an application component whose ID is 'user'.
 * Therefore, at any place one can access the user state via
 * <code>Yii::app()->user</code>.
 *
 * CWebUser should be used together with an {@link IUserIdentity identity}
 * which implements the actual authentication algorithm.
 *
 * A typical authentication process using CWebUser is as follows:
 * <ol>
 * <li>The user provides information needed for authentication.</li>
 * <li>An {@link IUserIdentity identity instance} is created with the user-provided information.</li>
 * <li>Call {@link IUserIdentity::authenticate} to check if the identity is valid.</li>
 * <li>If valid, call {@link CWebUser::login} to login the user, and
 *     Redirect the user browser to {@link returnUrl}.</li>
 * <li>If not valid, retrieve the error code or message from the identity
 * instance and display it.</li>
 * </ol>
 *
 * The property {@link id} and {@link name} are both identifiers
 * for the user. The former is mainly used internally (e.g. primary key), while
 * the latter is for display purpose (e.g. username). The {@link id} property
 * is a unique identifier for a user that is persistent
 * during the whole user session. It can be a username, or something else,
 * depending on the implementation of the {@link IUserIdentity identity class}.
 *
 * Both {@link id} and {@link name} are persistent during the user session.
 * Besides, an identity may have additional persistent data which can
 * be accessed by calling {@link getState}.
 * Note, when {@link allowAutoLogin cookie-based authentication} is enabled,
 * all these persistent data will be stored in cookie. Therefore, do not
 * store password or other sensitive data in the persistent storage. Instead,
 * you should store them directly in session on the server side if needed.
 *
 * @property boolean $isGuest Whether the current application user is a guest.
 * @property mixed $id The unique identifier for the user. If null, it means the user is a guest.
 * @property string $name The user name. If the user is not logged in, this will be {@link guestName}.
 * @property string $returnUrl The URL that the user should be redirected to after login.
 * @property string $stateKeyPrefix A prefix for the name of the session variables storing user session data.
 * @property array $flashes Flash messages (key => message).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class User extends Component
{
	const FLASH_KEY_PREFIX = 'Yii.CWebUser.flash.';
	const FLASH_COUNTERS = 'Yii.CWebUser.flashcounters';
	const STATES_VAR = '__states';
	const AUTH_TIMEOUT_VAR = '__timeout';

	/**
	 * @var boolean whether to enable cookie-based login. Defaults to false.
	 */
	public $allowAutoLogin = false;
	/**
	 * @var string the name for a guest user. Defaults to 'Guest'.
	 * This is used by {@link getName} when the current user is a guest (not authenticated).
	 */
	public $guestName = 'Guest';
	/**
	 * @var string|array the URL for login. If using array, the first element should be
	 * the route to the login action, and the rest name-value pairs are GET parameters
	 * to construct the login URL (e.g. array('/site/login')). If this property is null,
	 * a 403 HTTP exception will be raised instead.
	 * @see CController::createUrl
	 */
	public $loginUrl = array('/site/login');
	/**
	 * @var array the property values (in name-value pairs) used to initialize the identity cookie.
	 * Any property of {@link CHttpCookie} may be initialized.
	 * This property is effective only when {@link allowAutoLogin} is true.
	 */
	public $identityCookie;
	/**
	 * @var integer timeout in seconds after which user is logged out if inactive.
	 * If this property is not set, the user will be logged out after the current session expires
	 * (c.f. {@link CHttpSession::timeout}).
	 * @since 1.1.7
	 */
	public $authTimeout;
	/**
	 * @var boolean whether to automatically renew the identity cookie each time a page is requested.
	 * Defaults to false. This property is effective only when {@link allowAutoLogin} is true.
	 * When this is false, the identity cookie will expire after the specified duration since the user
	 * is initially logged in. When this is true, the identity cookie will expire after the specified duration
	 * since the user visits the site the last time.
	 * @see allowAutoLogin
	 * @since 1.1.0
	 */
	public $autoRenewCookie = false;
	/**
	 * @var boolean whether to automatically update the validity of flash messages.
	 * Defaults to true, meaning flash messages will be valid only in the current and the next requests.
	 * If this is set false, you will be responsible for ensuring a flash message is deleted after usage.
	 * (This can be achieved by calling {@link getFlash} with the 3rd parameter being true).
	 * @since 1.1.7
	 */
	public $autoUpdateFlash = true;
	/**
	 * @var string value that will be echoed in case that user session has expired during an ajax call.
	 * When a request is made and user session has expired, {@link loginRequired} redirects to {@link loginUrl} for login.
	 * If that happens during an ajax call, the complete HTML login page is returned as the result of that ajax call. That could be
	 * a problem if the ajax call expects the result to be a json array or a predefined string, as the login page is ignored in that case.
	 * To solve this, set this property to the desired return value.
	 *
	 * If this property is set, this value will be returned as the result of the ajax call in case that the user session has expired.
	 * @since 1.1.9
	 * @see loginRequired
	 */
	public $loginRequiredAjaxResponse;

	private $_keyPrefix;
	private $_access = array();

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 */
	public function __get($name)
	{
		if ($this->hasState($name)) {
			return $this->getState($name);
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be set like properties.
	 * @param string $name property name
	 * @param mixed $value property value
	 */
	public function __set($name, $value)
	{
		if ($this->hasState($name)) {
			$this->setState($name, $value);
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be checked for null value.
	 * @param string $name property name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if ($this->hasState($name)) {
			return $this->getState($name) !== null;
		} else {
			return parent::__isset($name);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be unset.
	 * @param string $name property name
	 * @throws CException if the property is read only.
	 */
	public function __unset($name)
	{
		if ($this->hasState($name)) {
			$this->setState($name, null);
		} else {
			parent::__unset($name);
		}
	}

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by starting session,
	 * performing cookie-based authentication if enabled, and updating the flash variables.
	 */
	public function init()
	{
		parent::init();
		Yii::app()->getSession()->open();
		if ($this->getIsGuest() && $this->allowAutoLogin) {
			$this->restoreFromCookie();
		} elseif ($this->autoRenewCookie && $this->allowAutoLogin) {
			$this->renewCookie();
		}
		if ($this->autoUpdateFlash) {
			$this->updateFlash();
		}

		$this->updateAuthStatus();
	}

	/**
	 * Logs in a user.
	 *
	 * The user identity information will be saved in storage that is
	 * persistent during the user session. By default, the storage is simply
	 * the session storage. If the duration parameter is greater than 0,
	 * a cookie will be sent to prepare for cookie-based login in future.
	 *
	 * Note, you have to set {@link allowAutoLogin} to true
	 * if you want to allow user to be authenticated based on the cookie information.
	 *
	 * @param IUserIdentity $identity the user identity (which should already be authenticated)
	 * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * If greater than 0, cookie-based login will be used. In this case, {@link allowAutoLogin}
	 * must be set true, otherwise an exception will be thrown.
	 * @return boolean whether the user is logged in
	 */
	public function login($identity, $duration = 0)
	{
		$id = $identity->getId();
		$states = $identity->getPersistentStates();
		if ($this->beforeLogin($id, $states, false)) {
			$this->changeIdentity($id, $identity->getName(), $states);

			if ($duration > 0) {
				if ($this->allowAutoLogin) {
					$this->saveToCookie($duration);
				} else {
					throw new CException(Yii::t('yii', '{class}.allowAutoLogin must be set true in order to use cookie-based authentication.',
						array('{class}' => get_class($this))));
				}
			}

			$this->afterLogin(false);
		}
		return !$this->getIsGuest();
	}

	/**
	 * Logs out the current user.
	 * This will remove authentication-related session data.
	 * If the parameter is true, the whole session will be destroyed as well.
	 * @param boolean $destroySession whether to destroy the whole session. Defaults to true. If false,
	 * then {@link clearStates} will be called, which removes only the data stored via {@link setState}.
	 */
	public function logout($destroySession = true)
	{
		if ($this->beforeLogout()) {
			if ($this->allowAutoLogin) {
				Yii::app()->getRequest()->getCookies()->remove($this->getStateKeyPrefix());
				if ($this->identityCookie !== null) {
					$cookie = $this->createIdentityCookie($this->getStateKeyPrefix());
					$cookie->value = null;
					$cookie->expire = 0;
					Yii::app()->getRequest()->getCookies()->add($cookie->name, $cookie);
				}
			}
			if ($destroySession) {
				Yii::app()->getSession()->destroy();
			} else {
				$this->clearStates();
			}
			$this->_access = array();
			$this->afterLogout();
		}
	}

	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the current application user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->getState('__id') === null;
	}

	/**
	 * Returns a value that uniquely represents the user.
	 * @return mixed the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function getId()
	{
		return $this->getState('__id');
	}

	/**
	 * @param mixed $value the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function setId($value)
	{
		$this->setState('__id', $value);
	}

	/**
	 * Returns the unique identifier for the user (e.g. username).
	 * This is the unique identifier that is mainly used for display purpose.
	 * @return string the user name. If the user is not logged in, this will be {@link guestName}.
	 */
	public function getName()
	{
		if (($name = $this->getState('__name')) !== null) {
			return $name;
		} else {
			return $this->guestName;
		}
	}

	/**
	 * Sets the unique identifier for the user (e.g. username).
	 * @param string $value the user name.
	 * @see getName
	 */
	public function setName($value)
	{
		$this->setState('__name', $value);
	}

	/**
	 * Returns the URL that the user should be redirected to after successful login.
	 * This property is usually used by the login action. If the login is successful,
	 * the action should read this property and use it to redirect the user browser.
	 * @param string $defaultUrl the default return URL in case it was not set previously. If this is null,
	 * the application entry URL will be considered as the default return URL.
	 * @return string the URL that the user should be redirected to after login.
	 * @see loginRequired
	 */
	public function getReturnUrl($defaultUrl = null)
	{
		if ($defaultUrl === null) {
			$defaultReturnUrl = Yii::app()->getUrlManager()->showScriptName ? Yii::app()->getRequest()->getScriptUrl() : Yii::app()->getRequest()->getBaseUrl() . '/';
		} else {
			$defaultReturnUrl = CHtml::normalizeUrl($defaultUrl);
		}
		return $this->getState('__returnUrl', $defaultReturnUrl);
	}

	/**
	 * @param string $value the URL that the user should be redirected to after login.
	 */
	public function setReturnUrl($value)
	{
		$this->setState('__returnUrl', $value);
	}

	/**
	 * Redirects the user browser to the login page.
	 * Before the redirection, the current URL (if it's not an AJAX url) will be
	 * kept in {@link returnUrl} so that the user browser may be redirected back
	 * to the current page after successful login. Make sure you set {@link loginUrl}
	 * so that the user browser can be redirected to the specified login URL after
	 * calling this method.
	 * After calling this method, the current request processing will be terminated.
	 */
	public function loginRequired()
	{
		$app = Yii::app();
		$request = $app->getRequest();

		if (!$request->getIsAjaxRequest()) {
			$this->setReturnUrl($request->getUrl());
		} elseif (isset($this->loginRequiredAjaxResponse)) {
			echo $this->loginRequiredAjaxResponse;
			Yii::app()->end();
		}

		if (($url = $this->loginUrl) !== null) {
			if (is_array($url)) {
				$route = isset($url[0]) ? $url[0] : $app->defaultController;
				$url = $app->createUrl($route, array_splice($url, 1));
			}
			$request->redirect($url);
		} else {
			throw new CHttpException(403, Yii::t('yii', 'Login Required'));
		}
	}

	/**
	 * This method is called before logging in a user.
	 * You may override this method to provide additional security check.
	 * For example, when the login is cookie-based, you may want to verify
	 * that the user ID together with a random token in the states can be found
	 * in the database. This will prevent hackers from faking arbitrary
	 * identity cookies even if they crack down the server private key.
	 * @param mixed $id the user ID. This is the same as returned by {@link getId()}.
	 * @param array $states a set of name-value pairs that are provided by the user identity.
	 * @param boolean $fromCookie whether the login is based on cookie
	 * @return boolean whether the user should be logged in
	 * @since 1.1.3
	 */
	protected function beforeLogin($id, $states, $fromCookie)
	{
		return true;
	}

	/**
	 * This method is called after the user is successfully logged in.
	 * You may override this method to do some postprocessing (e.g. log the user
	 * login IP and time; load the user permission information).
	 * @param boolean $fromCookie whether the login is based on cookie.
	 * @since 1.1.3
	 */
	protected function afterLogin($fromCookie)
	{
	}

	/**
	 * This method is invoked when calling {@link logout} to log out a user.
	 * If this method return false, the logout action will be cancelled.
	 * You may override this method to provide additional check before
	 * logging out a user.
	 * @return boolean whether to log out the user
	 * @since 1.1.3
	 */
	protected function beforeLogout()
	{
		return true;
	}

	/**
	 * This method is invoked right after a user is logged out.
	 * You may override this method to do some extra cleanup work for the user.
	 * @since 1.1.3
	 */
	protected function afterLogout()
	{
	}

	/**
	 * Populates the current user object with the information obtained from cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * The user identity information is recovered from cookie.
	 * Sufficient security measures are used to prevent cookie data from being tampered.
	 * @see saveToCookie
	 */
	protected function restoreFromCookie()
	{
		$app = Yii::app();
		$request = $app->getRequest();
		$cookie = $request->getCookies()->itemAt($this->getStateKeyPrefix());
		if ($cookie && !empty($cookie->value) && is_string($cookie->value) && ($data = $app->getSecurityManager()->validateData($cookie->value)) !== false) {
			$data = @unserialize($data);
			if (is_array($data) && isset($data[0], $data[1], $data[2], $data[3])) {
				list($id, $name, $duration, $states) = $data;
				if ($this->beforeLogin($id, $states, true)) {
					$this->changeIdentity($id, $name, $states);
					if ($this->autoRenewCookie) {
						$cookie->expire = time() + $duration;
						$request->getCookies()->add($cookie->name, $cookie);
					}
					$this->afterLogin(true);
				}
			}
		}
	}

	/**
	 * Renews the identity cookie.
	 * This method will set the expiration time of the identity cookie to be the current time
	 * plus the originally specified cookie duration.
	 * @since 1.1.3
	 */
	protected function renewCookie()
	{
		$request = Yii::app()->getRequest();
		$cookies = $request->getCookies();
		$cookie = $cookies->itemAt($this->getStateKeyPrefix());
		if ($cookie && !empty($cookie->value) && ($data = Yii::app()->getSecurityManager()->validateData($cookie->value)) !== false) {
			$data = @unserialize($data);
			if (is_array($data) && isset($data[0], $data[1], $data[2], $data[3])) {
				$cookie->expire = time() + $data[2];
				$cookies->add($cookie->name, $cookie);
			}
		}
	}

	/**
	 * Saves necessary user data into a cookie.
	 * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
	 * This method saves user ID, username, other identity states and a validation key to cookie.
	 * These information are used to do authentication next time when user visits the application.
	 * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * @see restoreFromCookie
	 */
	protected function saveToCookie($duration)
	{
		$app = Yii::app();
		$cookie = $this->createIdentityCookie($this->getStateKeyPrefix());
		$cookie->expire = time() + $duration;
		$data = array(
			$this->getId(),
			$this->getName(),
			$duration,
			$this->saveIdentityStates(),
		);
		$cookie->value = $app->getSecurityManager()->hashData(serialize($data));
		$app->getRequest()->getCookies()->add($cookie->name, $cookie);
	}

	/**
	 * Creates a cookie to store identity information.
	 * @param string $name the cookie name
	 * @return CHttpCookie the cookie used to store identity information
	 */
	protected function createIdentityCookie($name)
	{
		$cookie = new CHttpCookie($name, '');
		if (is_array($this->identityCookie)) {
			foreach ($this->identityCookie as $name => $value) {
				$cookie->$name = $value;
			}
		}
		return $cookie;
	}

	/**
	 * @return string a prefix for the name of the session variables storing user session data.
	 */
	public function getStateKeyPrefix()
	{
		if ($this->_keyPrefix !== null) {
			return $this->_keyPrefix;
		} else {
			return $this->_keyPrefix = md5('Yii.' . get_class($this) . '.' . Yii::app()->getId());
		}
	}

	/**
	 * @param string $value a prefix for the name of the session variables storing user session data.
	 */
	public function setStateKeyPrefix($value)
	{
		$this->_keyPrefix = $value;
	}

	/**
	 * Returns the value of a variable that is stored in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * A variable, if stored in user session using {@link setState} can be
	 * retrieved back using this function.
	 *
	 * @param string $key variable name
	 * @param mixed $defaultValue default value
	 * @return mixed the value of the variable. If it doesn't exist in the session,
	 * the provided default value will be returned
	 * @see setState
	 */
	public function getState($key, $defaultValue = null)
	{
		$key = $this->getStateKeyPrefix() . $key;
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}

	/**
	 * Stores a variable in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link getState}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string $key variable name
	 * @param mixed $value variable value
	 * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be
	 * removed from the session
	 * @see getState
	 */
	public function setState($key, $value, $defaultValue = null)
	{
		$key = $this->getStateKeyPrefix() . $key;
		if ($value === $defaultValue) {
			unset($_SESSION[$key]);
		} else {
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Returns a value indicating whether there is a state of the specified name.
	 * @param string $key state name
	 * @return boolean whether there is a state of the specified name.
	 */
	public function hasState($key)
	{
		$key = $this->getStateKeyPrefix() . $key;
		return isset($_SESSION[$key]);
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * This will remove the data stored via {@link setState}.
	 */
	public function clearStates()
	{
		$keys = array_keys($_SESSION);
		$prefix = $this->getStateKeyPrefix();
		$n = strlen($prefix);
		foreach ($keys as $key) {
			if (!strncmp($key, $prefix, $n)) {
				unset($_SESSION[$key]);
			}
		}
	}

	/**
	 * Returns all flash messages.
	 * This method is similar to {@link getFlash} except that it returns all
	 * currently available flash messages.
	 * @param boolean $delete whether to delete the flash messages after calling this method.
	 * @return array flash messages (key => message).
	 * @since 1.1.3
	 */
	public function getFlashes($delete = true)
	{
		$flashes = array();
		$prefix = $this->getStateKeyPrefix() . self::FLASH_KEY_PREFIX;
		$keys = array_keys($_SESSION);
		$n = strlen($prefix);
		foreach ($keys as $key) {
			if (!strncmp($key, $prefix, $n)) {
				$flashes[substr($key, $n)] = $_SESSION[$key];
				if ($delete) {
					unset($_SESSION[$key]);
				}
			}
		}
		if ($delete) {
			$this->setState(self::FLASH_COUNTERS, array());
		}
		return $flashes;
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true.
	 * @return mixed the message message
	 */
	public function getFlash($key, $defaultValue = null, $delete = true)
	{
		$value = $this->getState(self::FLASH_KEY_PREFIX . $key, $defaultValue);
		if ($delete) {
			$this->setFlash($key, null);
		}
		return $value;
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash($key, $value, $defaultValue = null)
	{
		$this->setState(self::FLASH_KEY_PREFIX . $key, $value, $defaultValue);
		$counters = $this->getState(self::FLASH_COUNTERS, array());
		if ($value === $defaultValue) {
			unset($counters[$key]);
		} else {
			$counters[$key] = 0;
		}
		$this->setState(self::FLASH_COUNTERS, $counters, array());
	}

	/**
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash($key)
	{
		return $this->getFlash($key, null, false) !== null;
	}

	/**
	 * Changes the current user with the specified identity information.
	 * This method is called by {@link login} and {@link restoreFromCookie}
	 * when the current user needs to be populated with the corresponding
	 * identity information. Derived classes may override this method
	 * by retrieving additional user-related information. Make sure the
	 * parent implementation is called first.
	 * @param mixed $id a unique identifier for the user
	 * @param string $name the display name for the user
	 * @param array $states identity states
	 */
	protected function changeIdentity($id, $name, $states)
	{
		Yii::app()->getSession()->regenerateID(true);
		$this->setId($id);
		$this->setName($name);
		$this->loadIdentityStates($states);
	}

	/**
	 * Retrieves identity states from persistent storage and saves them as an array.
	 * @return array the identity states
	 */
	protected function saveIdentityStates()
	{
		$states = array();
		foreach ($this->getState(self::STATES_VAR, array()) as $name => $dummy) {
			$states[$name] = $this->getState($name);
		}
		return $states;
	}

	/**
	 * Loads identity states from an array and saves them to persistent storage.
	 * @param array $states the identity states
	 */
	protected function loadIdentityStates($states)
	{
		$names = array();
		if (is_array($states)) {
			foreach ($states as $name => $value) {
				$this->setState($name, $value);
				$names[$name] = true;
			}
		}
		$this->setState(self::STATES_VAR, $names);
	}

	/**
	 * Updates the internal counters for flash messages.
	 * This method is internally used by {@link CWebApplication}
	 * to maintain the availability of flash messages.
	 */
	protected function updateFlash()
	{
		$counters = $this->getState(self::FLASH_COUNTERS);
		if (!is_array($counters)) {
			return;
		}
		foreach ($counters as $key => $count) {
			if ($count) {
				unset($counters[$key]);
				$this->setState(self::FLASH_KEY_PREFIX . $key, null);
			} else {
				$counters[$key]++;
			}
		}
		$this->setState(self::FLASH_COUNTERS, $counters, array());
	}

	/**
	 * Updates the authentication status according to {@link authTimeout}.
	 * If the user has been inactive for {@link authTimeout} seconds,
	 * he will be automatically logged out.
	 * @since 1.1.7
	 */
	protected function updateAuthStatus()
	{
		if ($this->authTimeout !== null && !$this->getIsGuest()) {
			$expires = $this->getState(self::AUTH_TIMEOUT_VAR);
			if ($expires !== null && $expires < time()) {
				$this->logout(false);
			} else {
				$this->setState(self::AUTH_TIMEOUT_VAR, time() + $this->authTimeout);
			}
		}
	}

	/**
	 * Performs access check for this user.
	 * @param string $operation the name of the operation that need access check.
	 * @param array $params name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 * Since version 1.1.11 a param with name 'userId' is added to this array, which holds the value of
	 * {@link getId()} when {@link CDbAuthManager} or {@link CPhpAuthManager} is used.
	 * @param boolean $allowCaching whether to allow caching the result of access check.
	 * When this parameter
	 * is true (default), if the access check of an operation was performed before,
	 * its result will be directly returned when calling this method to check the same operation.
	 * If this parameter is false, this method will always call {@link CAuthManager::checkAccess}
	 * to obtain the up-to-date access result. Note that this caching is effective
	 * only within the same request and only works when <code>$params=array()</code>.
	 * @return boolean whether the operations can be performed by this user.
	 */
	public function checkAccess($operation, $params = array(), $allowCaching = true)
	{
		if ($allowCaching && $params === array() && isset($this->_access[$operation])) {
			return $this->_access[$operation];
		}

		$access = Yii::app()->getAuthManager()->checkAccess($operation, $this->getId(), $params);
		if ($allowCaching && $params === array()) {
			$this->_access[$operation] = $access;
		}

		return $access;
	}
}
