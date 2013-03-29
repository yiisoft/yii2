<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\HttpException;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class User extends Component
{
	const EVENT_BEFORE_LOGIN = 'beforeLogin';
	const EVENT_AFTER_LOGIN = 'afterLogin';
	const EVENT_BEFORE_LOGOUT = 'beforeLogout';
	const EVENT_AFTER_LOGOUT = 'afterLogout';

	/**
	 * @var Identity the identity object associated with the currently logged user.
	 * This property is set automatically be the User component. Do not modify it directly
	 * unless you understand the consequence. You should normally use [[login()]], [[logout()]],
	 * or [[switchIdentity()]] to update the identity associated with the current user.
	 *
	 * If this property is null, it means the current user is a guest (not authenticated).
	 */
	public $identity;
	/**
	 * @var string the class name of the [[identity]] object.
	 */
	public $identityClass;
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
	public $identityCookie = array('name' => '__identity');
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
	 */
	public $autoRenewCookie = true;

	public $idSessionVar = '__id';
	public $authTimeoutSessionVar = '__expire';
	public $returnUrlSessionVar = '__returnUrl';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();

		if ($this->identityClass === null) {
			throw new InvalidConfigException('User::identityClass must be set.');
		}
		if ($this->enableAutoLogin && !isset($this->identityCookie['name'])) {
			throw new InvalidConfigException('User::identityCookie must contain the "name" element.');
		}

		Yii::$app->getSession()->open();

		$this->loadIdentity();

		$this->renewAuthStatus();

		if ($this->enableAutoLogin) {
			if ($this->getIsGuest()) {
				$this->loginByCookie();
			} elseif ($this->autoRenewCookie) {
				$this->renewIdentityCookie();
			}
		}
	}

	public function loadIdentity()
	{
		$id = $this->getId();
		if ($id === null) {
			$this->identity = null;
		} else {
			/** @var $class Identity */
			$class = $this->identityClass;
			$this->identity = $class::findIdentity($this->getId());
		}
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
	 * @param Identity $identity the user identity (which should already be authenticated)
	 * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * If greater than 0, cookie-based login will be used. In this case, {@link enableAutoLogin}
	 * must be set true, otherwise an exception will be thrown.
	 * @return boolean whether the user is logged in
	 */
	public function login($identity, $duration = 0)
	{
		if ($this->beforeLogin($identity, false)) {
			$this->switchIdentity($identity);
			if ($duration > 0 && $this->enableAutoLogin) {
				$this->sendIdentityCookie($identity, $duration);
			}
			$this->afterLogin($identity, false);
		}
		return !$this->getIsGuest();
	}

	/**
	 * Populates the current user object with the information obtained from cookie.
	 * This method is used when automatic login ({@link enableAutoLogin}) is enabled.
	 * The user identity information is recovered from cookie.
	 * Sufficient security measures are used to prevent cookie data from being tampered.
	 * @see sendIdentityCookie
	 */
	protected function loginByCookie()
	{
		$name = $this->identityCookie['name'];
		$value = Yii::$app->getRequest()->getCookies()->getValue($name);
		if ($value !== null) {
			$data = json_decode($value, true);
			if (count($data) === 3 && isset($data[0], $data[1], $data[2])) {
				list ($id, $authKey, $duration) = $data;
				/** @var $class Identity */
				$class = $this->identityClass;
				$identity = $class::findIdentity($id);
				if ($identity !== null && $identity->validateAuthKey($authKey)) {
					if ($this->beforeLogin($identity, true)) {
						$this->switchIdentity($identity);
						if ($this->autoRenewCookie) {
							$this->sendIdentityCookie($identity, $duration);
						}
						$this->afterLogin($identity, true);
					}
				} elseif ($identity !== null) {
					Yii::warning("Invalid auth key attempted for user '$id': $authKey", __METHOD__);
				}
			}
		}
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
		$identity = $this->identity;
		if ($identity !== null && $this->beforeLogout($identity)) {
			$this->switchIdentity(null);
			if ($this->enableAutoLogin) {
				Yii::$app->getResponse()->getCookies()->remove(new Cookie($this->identityCookie));
			}
			if ($destroySession) {
				Yii::$app->getSession()->destroy();
			}
 			$this->afterLogout($identity);
		}
	}

	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the current user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->identity === null;
	}

	/**
	 * Returns a value that uniquely represents the user.
	 * @return mixed the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function getId()
	{
		return Yii::$app->getSession()->get($this->idSessionVar);
	}

	/**
	 * @param mixed $value the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function setId($value)
	{
		Yii::$app->getSession()->set($this->idSessionVar, $value);
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
		$url = Yii::$app->getSession()->get($this->returnUrlSessionVar, $defaultUrl);
		if ($url === null) {
			return Yii::$app->getHomeUrl();
		} else {
			return Html::url($url);
		}
	}

	/**
	 * @param string $value the URL that the user should be redirected to after login.
	 */
	public function setReturnUrl($value)
	{
		Yii::$app->getSession()->set($this->returnUrlSessionVar, $value);
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
		if (($url = $this->loginUrl) !== null) {
			$url = Html::url($url);
			$request = Yii::$app->getRequest();
			if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
				$url = $request->getHostInfo() . $url;
			}
			if ($request->getIsAjaxRequest()) {
				echo json_encode(array(
					'redirect' => $url,
				));
				Yii::$app->end();
			} else {
				Yii::$app->getResponse()->redirect($url);
			}
		} else {
			throw new HttpException(403, Yii::t('yii|Login Required'));
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
	 */
	protected function beforeLogin($identity, $fromCookie)
	{
		$event = new UserEvent(array(
			'identity' => $identity,
			'fromCookie' => $fromCookie,
		));
		$this->trigger(self::EVENT_BEFORE_LOGIN, $event);
		return $event->isValid;
	}

	/**
	 * This method is called after the user is successfully logged in.
	 * You may override this method to do some postprocessing (e.g. log the user
	 * login IP and time; load the user permission information).
	 * @param boolean $fromCookie whether the login is based on cookie.
	 */
	protected function afterLogin($identity, $fromCookie)
	{
		$this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent(array(
			'identity' => $identity,
			'fromCookie' => $fromCookie,
		)));
	}

	/**
	 * This method is invoked when calling {@link logout} to log out a user.
	 * If this method return false, the logout action will be cancelled.
	 * You may override this method to provide additional check before
	 * logging out a user.
	 * @return boolean whether to log out the user
	 */
	protected function beforeLogout($identity)
	{
		$event = new UserEvent(array(
			'identity' => $identity,
		));
		$this->trigger(self::EVENT_BEFORE_LOGOUT, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after a user is logged out.
	 * You may override this method to do some extra cleanup work for the user.
	 */
	protected function afterLogout($identity)
	{
		$this->trigger(self::EVENT_AFTER_LOGOUT, new UserEvent(array(
			'identity' => $identity,
		)));
	}


	/**
	 * Renews the identity cookie.
	 * This method will set the expiration time of the identity cookie to be the current time
	 * plus the originally specified cookie duration.
	 */
	protected function renewIdentityCookie()
	{
		$name = $this->identityCookie['name'];
		$value = Yii::$app->getRequest()->getCookies()->getValue($name);
		if ($value !== null) {
			$data = json_decode($value, true);
			if (is_array($data) && isset($data[2])) {
				$cookie = new Cookie($this->identityCookie);
				$cookie->value = $value;
				$cookie->expire = time() + (int)$data[2];
				Yii::$app->getResponse()->getCookies()->add($cookie);
			}
		}
	}

	/**
	 * Saves necessary user data into a cookie.
	 * This method is used when automatic login ({@link enableAutoLogin}) is enabled.
	 * This method saves user ID, username, other identity states and a validation key to cookie.
	 * These information are used to do authentication next time when user visits the application.
	 * @param Identity $identity
	 * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
	 * @see loginByCookie
	 */
	protected function sendIdentityCookie($identity, $duration)
	{
		$cookie = new Cookie($this->identityCookie);
		$cookie->value = json_encode(array(
			$identity->getId(),
			$identity->getAuthKey(),
			$duration,
		));
		$cookie->expire = time() + $duration;
		Yii::$app->getResponse()->getCookies()->add($cookie);
	}

	/**
	 * Changes the current user with the specified identity information.
	 * This method is called by {@link login} and {@link restoreFromCookie}
	 * when the current user needs to be populated with the corresponding
	 * identity information. Derived classes may override this method
	 * by retrieving additional user-related information. Make sure the
	 * parent implementation is called first.
	 * @param Identity $identity a unique identifier for the user
	 */
	protected function switchIdentity($identity)
	{
		Yii::$app->getSession()->regenerateID(true);
		$this->identity = $identity;
		if ($identity instanceof Identity) {
			$this->setId($identity->getId());
			if ($this->authTimeout !== null) {
				Yii::$app->getSession()->set($this->authTimeoutSessionVar, time() + $this->authTimeout);
			}
		} else {
			$session = Yii::$app->getSession();
			$session->remove($this->idSessionVar);
			$session->remove($this->authTimeoutSessionVar);
		}
	}

	/**
	 * Updates the authentication status according to {@link authTimeout}.
	 * If the user has been inactive for {@link authTimeout} seconds,
	 * he will be automatically logged out.
	 */
	protected function renewAuthStatus()
	{
		if ($this->authTimeout !== null && !$this->getIsGuest()) {
			$expire = Yii::$app->getSession()->get($this->authTimeoutSessionVar);
			if ($expire !== null && $expire < time()) {
				$this->logout(false);
			} else {
				Yii::$app->getSession()->set($this->authTimeoutSessionVar, time() + $this->authTimeout);
			}
		}
	}
}
