<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class User extends Component
{
	const STATES_VAR = '__states';
	const AUTH_TIMEOUT_VAR = '__timeout';

	/**
	 * @var boolean whether to enable cookie-based login. Defaults to false.
	 */
	public $enableAutoLogin = false;
	/**
	 * @var string|array the URL for login when [[loginRequired()]] is called. 
	 * If an array is given, [[UrlManager::createUrl()]] will be called to create the corresponding URL.
	 * The first element of the array should be the route to the login action, and the rest of 
	 * the name-value pairs are GET parameters used to construct the login URL. For example,
	 * 
	 * ~~~
	 * array('site/login', 'ref' => 1)
	 * ~~~
	 *
	 * If this property is null, a 403 HTTP exception will be raised when [[loginRequired()]] is called.
	 */
	public $loginUrl = array('site/login');
	/**
	 * @var array the configuration of the identity cookie. This property is used only when [[enableAutoLogin]] is true.
	 * @see Cookie
	 */
	public $identityCookie;
	/**
	 * @var integer the number of seconds in which the user will be logged out automatically if he
	 * remains inactive. If this property is not set, the user will be logged out after
	 * the current session expires (c.f. [[Session::timeout]]).
	 */
	public $authTimeout;
	/**
	 * @var boolean whether to automatically renew the identity cookie each time a page is requested.
	 * Defaults to false. This property is effective only when {@link enableAutoLogin} is true.
	 * When this is false, the identity cookie will expire after the specified duration since the user
	 * is initially logged in. When this is true, the identity cookie will expire after the specified duration
	 * since the user visits the site the last time.
	 * @see enableAutoLogin
	 * @since 1.1.0
	 */
	public $autoRenewCookie = false;
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
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		Yii::$app->getSession()->open();
		if ($this->getIsGuest() && $this->enableAutoLogin) {
			$this->restoreFromCookie();
		} elseif ($this->autoRenewCookie && $this->enableAutoLogin) {
			$this->renewCookie();
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
	 * Note, you have to set {@link enableAutoLogin} to true
	 * if you want to allow user to be authenticated based on the cookie information.
	 *
	 * @param IUserIdentity $identity the user identity (which should already be authenticated)
	 * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * If greater than 0, cookie-based login will be used. In this case, {@link enableAutoLogin}
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
				if ($this->enableAutoLogin) {
					$this->saveToCookie($duration);
				} else {
					throw new CException(Yii::t('yii', '{class}.enableAutoLogin must be set true in order to use cookie-based authentication.',
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
			if ($this->enableAutoLogin) {
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
	 * This method is used when automatic login ({@link enableAutoLogin}) is enabled.
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
	 * This method is used when automatic login ({@link enableAutoLogin}) is enabled.
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
	 * Updates the authentication status according to {@link authTimeout}.
	 * If the user has been inactive for {@link authTimeout} seconds,
	 * he will be automatically logged out.
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
}
