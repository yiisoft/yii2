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

/**
 * User is the class for the "user" application component that manages the user authentication status.
 *
 * In particular, [[User::isGuest]] returns a value indicating whether the current user is a guest or not.
 * Through methods [[login()]] and [[logout()]], you can change the user authentication status.
 *
 * User works with a class implementing the [[Identity]] interface. This class implements
 * the actual user authentication logic and is often backed by a user database table.
 *
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
	public $identityCookie = array('name' => '_identity', 'httponly' => true);
	/**
	 * @var integer the number of seconds in which the user will be logged out automatically if he
	 * remains inactive. If this property is not set, the user will be logged out after
	 * the current session expires (c.f. [[Session::timeout]]).
	 */
	public $authTimeout;
	/**
	 * @var boolean whether to automatically renew the identity cookie each time a page is requested.
	 * This property is effective only when [[enableAutoLogin]] is true.
	 * When this is false, the identity cookie will expire after the specified duration since the user
	 * is initially logged in. When this is true, the identity cookie will expire after the specified duration
	 * since the user visits the site the last time.
	 * @see enableAutoLogin
	 */
	public $autoRenewCookie = true;
	/**
	 * @var string the session variable name used to store the value of [[id]].
	 */
	public $idVar = '__id';
	/**
	 * @var string the session variable name used to store the value of expiration timestamp of the authenticated state.
	 * This is used when [[authTimeout]] is set.
	 */
	public $authTimeoutVar = '__expire';
	/**
	 * @var string the session variable name used to store the value of [[returnUrl]].
	 */
	public $returnUrlVar = '__returnUrl';

	private $_access = array();


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

		$this->renewAuthStatus();

		if ($this->enableAutoLogin) {
			if ($this->getIsGuest()) {
				$this->loginByCookie();
			} elseif ($this->autoRenewCookie) {
				$this->renewIdentityCookie();
			}
		}
	}

	private $_identity = false;

	/**
	 * Returns the identity object associated with the currently logged user.
	 * @return Identity the identity object associated with the currently logged user.
	 * Null is returned if the user is not logged in (not authenticated).
	 * @see login
	 * @see logout
	 */
	public function getIdentity()
	{
		if ($this->_identity === false) {
			$id = $this->getId();
			if ($id === null) {
				$this->_identity = null;
			} else {
				/** @var $class Identity */
				$class = Yii::import($this->identityClass);
				$this->_identity = $class::findIdentity($id);
			}
		}
		return $this->_identity;
	}

	/**
	 * Sets the identity object.
	 * This method should be mainly be used by the User component or its child class
	 * to maintain the identity object.
	 *
	 * You should normally update the user identity via methods [[login()]], [[logout()]]
	 * or [[switchIdentity()]].
	 *
	 * @param Identity $identity the identity object associated with the currently logged user.
	 */
	public function setIdentity($identity)
	{
		$this->_identity = $identity;
	}

	/**
	 * Logs in a user.
	 *
	 * This method stores the necessary session information to keep track
	 * of the user identity information. If `$duration` is greater than 0
	 * and [[enableAutoLogin]] is true, it will also send out an identity
	 * cookie to support cookie-based login.
	 *
	 * @param Identity $identity the user identity (which should already be authenticated)
	 * @param integer $duration number of seconds that the user can remain in logged-in status.
	 * Defaults to 0, meaning login till the user closes the browser or the session is manually destroyed.
	 * If greater than 0 and [[enableAutoLogin]] is true, cookie-based login will be supported.
	 * @return boolean whether the user is logged in
	 */
	public function login($identity, $duration = 0)
	{
		if ($this->beforeLogin($identity, false)) {
			$this->switchIdentity($identity, $duration);
			$this->afterLogin($identity, false);
		}
		return !$this->getIsGuest();
	}

	/**
	 * Logs in a user by cookie.
	 *
	 * This method attempts to log in a user using the ID and authKey information
	 * provided by the given cookie.
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
						$this->switchIdentity($identity, $this->autoRenewCookie ? $duration : 0);
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
	 * If `$destroySession` is true, all session data will be removed.
	 * @param boolean $destroySession whether to destroy the whole session. Defaults to true.
	 */
	public function logout($destroySession = true)
	{
		$identity = $this->getIdentity();
		if ($identity !== null && $this->beforeLogout($identity)) {
			$this->switchIdentity(null);
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
		return $this->getIdentity() === null;
	}

	/**
	 * Returns a value that uniquely represents the user.
	 * @return string|integer the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function getId()
	{
		return Yii::$app->getSession()->get($this->idVar);
	}

	/**
	 * Returns the URL that the user should be redirected to after successful login.
	 * This property is usually used by the login action. If the login is successful,
	 * the action should read this property and use it to redirect the user browser.
	 * @param string|array $defaultUrl the default return URL in case it was not set previously.
	 * If this is null, it means [[Application::homeUrl]] will be redirected to.
	 * Please refer to [[\yii\helpers\Html::url()]] on acceptable URL formats.
	 * @return string the URL that the user should be redirected to after login.
	 * @see loginRequired
	 */
	public function getReturnUrl($defaultUrl = null)
	{
		$url = Yii::$app->getSession()->get($this->returnUrlVar, $defaultUrl);
		return $url === null ? Yii::$app->getHomeUrl() : $url;
	}

	/**
	 * @param string|array $url the URL that the user should be redirected to after login.
	 * Please refer to [[\yii\helpers\Html::url()]] on acceptable URL formats.
	 */
	public function setReturnUrl($url)
	{
		Yii::$app->getSession()->set($this->returnUrlVar, $url);
	}

	/**
	 * Redirects the user browser to the login page.
	 * Before the redirection, the current URL (if it's not an AJAX url) will be
	 * kept as [[returnUrl]] so that the user browser may be redirected back
	 * to the current page after successful login. Make sure you set [[loginUrl]]
	 * so that the user browser can be redirected to the specified login URL after
	 * calling this method.
	 * After calling this method, the current request processing will be terminated.
	 */
	public function loginRequired()
	{
		$request = Yii::$app->getRequest();
		if (!$request->getIsAjaxRequest()) {
			$this->setReturnUrl($request->getUrl());
		}
		if ($this->loginUrl !== null) {
			Yii::$app->getResponse()->redirect($this->loginUrl);
		} else {
			throw new HttpException(403, Yii::t('yii', 'Login Required'));
		}
	}

	/**
	 * This method is called before logging in a user.
	 * The default implementation will trigger the [[EVENT_BEFORE_LOGIN]] event.
	 * If you override this method, make sure you call the parent implementation
	 * so that the event is triggered.
	 * @param Identity $identity the user identity information
	 * @param boolean $cookieBased whether the login is cookie-based
	 * @return boolean whether the user should continue to be logged in
	 */
	protected function beforeLogin($identity, $cookieBased)
	{
		$event = new UserEvent(array(
			'identity' => $identity,
			'cookieBased' => $cookieBased,
		));
		$this->trigger(self::EVENT_BEFORE_LOGIN, $event);
		return $event->isValid;
	}

	/**
	 * This method is called after the user is successfully logged in.
	 * The default implementation will trigger the [[EVENT_AFTER_LOGIN]] event.
	 * If you override this method, make sure you call the parent implementation
	 * so that the event is triggered.
	 * @param Identity $identity the user identity information
	 * @param boolean $cookieBased whether the login is cookie-based
	 */
	protected function afterLogin($identity, $cookieBased)
	{
		$this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent(array(
			'identity' => $identity,
			'cookieBased' => $cookieBased,
		)));
	}

	/**
	 * This method is invoked when calling [[logout()]] to log out a user.
	 * The default implementation will trigger the [[EVENT_BEFORE_LOGOUT]] event.
	 * If you override this method, make sure you call the parent implementation
	 * so that the event is triggered.
	 * @param Identity $identity the user identity information
	 * @return boolean whether the user should continue to be logged out
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
	 * This method is invoked right after a user is logged out via [[logout()]].
	 * The default implementation will trigger the [[EVENT_AFTER_LOGOUT]] event.
	 * If you override this method, make sure you call the parent implementation
	 * so that the event is triggered.
	 * @param Identity $identity the user identity information
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
	 * Sends an identity cookie.
	 * This method is used when [[enableAutoLogin]] is true.
	 * It saves [[id]], [[Identity::getAuthKey()|auth key]], and the duration of cookie-based login
	 * information in the cookie.
	 * @param Identity $identity
	 * @param integer $duration number of seconds that the user can remain in logged-in status.
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
	 * Switches to a new identity for the current user.
	 *
	 * This method will save necessary session information to keep track of the user authentication status.
	 * If `$duration` is provided, it will also send out appropriate identity cookie
	 * to support cookie-based login.
	 *
	 * This method is mainly called by [[login()]], [[logout()]] and [[loginByCookie()]]
	 * when the current user needs to be associated with the corresponding identity information.
	 *
	 * @param Identity $identity the identity information to be associated with the current user.
	 * If null, it means switching to be a guest.
	 * @param integer $duration number of seconds that the user can remain in logged-in status.
	 * This parameter is used only when `$identity` is not null.
	 */
	public function switchIdentity($identity, $duration = 0)
	{
		$session = Yii::$app->getSession();
		$session->regenerateID(true);
		$this->setIdentity($identity);
		$session->remove($this->idVar);
		$session->remove($this->authTimeoutVar);
		if ($identity instanceof Identity) {
			$session->set($this->idVar, $identity->getId());
			if ($this->authTimeout !== null) {
				$session->set($this->authTimeoutVar, time() + $this->authTimeout);
			}
			if ($duration > 0 && $this->enableAutoLogin) {
				$this->sendIdentityCookie($identity, $duration);
			}
		} elseif ($this->enableAutoLogin) {
			Yii::$app->getResponse()->getCookies()->remove(new Cookie($this->identityCookie));
		}
	}

	/**
	 * Updates the authentication status according to [[authTimeout]].
	 * This method is called during [[init()]].
	 * It will update the user's authentication status if it has not outdated yet.
	 * Otherwise, it will logout the user.
	 */
	protected function renewAuthStatus()
	{
		if ($this->authTimeout !== null && !$this->getIsGuest()) {
			$expire = Yii::$app->getSession()->get($this->authTimeoutVar);
			if ($expire !== null && $expire < time()) {
				$this->logout(false);
			} else {
				Yii::$app->getSession()->set($this->authTimeoutVar, time() + $this->authTimeout);
			}
		}
	}

	/**
	 * Performs access check for this user.
	 * @param string $operation the name of the operation that need access check.
	 * @param array $params name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user. A param with name 'userId' is added to
	 * this array, which holds the value of [[id]] when [[DbAuthManager]] or
	 * [[PhpAuthManager]] is used.
	 * @param boolean $allowCaching whether to allow caching the result of access check.
	 * When this parameter is true (default), if the access check of an operation was performed
	 * before, its result will be directly returned when calling this method to check the same
	 * operation. If this parameter is false, this method will always call
	 * [[AuthManager::checkAccess()]] to obtain the up-to-date access result. Note that this
	 * caching is effective only within the same request and only works when `$params = array()`.
	 * @return boolean whether the operations can be performed by this user.
	 */
	public function checkAccess($operation, $params = array(), $allowCaching = true)
	{
		$auth = Yii::$app->getAuthManager();
		if ($auth === null) {
			return false;
		}
		if ($allowCaching && empty($params) && isset($this->_access[$operation])) {
			return $this->_access[$operation];
		}
		$access = $auth->checkAccess($this->getId(), $operation, $params);
		if ($allowCaching && empty($params)) {
			$this->_access[$operation] = $access;
		}
		return $access;
	}
}
