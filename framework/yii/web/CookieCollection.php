<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use ArrayIterator;
use yii\helpers\SecurityHelper;

/**
 * CookieCollection maintains the cookies available in the current request.
 *
 * @property integer $count the number of cookies in the collection
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CookieCollection extends \yii\base\Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var boolean whether to enable cookie validation. By setting this property to true,
	 * if a cookie is tampered on the client side, it will be ignored when received on the server side.
	 */
	public $enableValidation = true;
	/**
	 * @var string the secret key used for cookie validation. If not set, a random key will be generated and used.
	 */
	public $validationKey;

	/**
	 * @var Cookie[] the cookies in this collection (indexed by the cookie names)
	 */
	private $_cookies = array();

	/**
	 * Constructor.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_cookies = $this->loadCookies();
	}

	/**
	 * Returns an iterator for traversing the cookies in the collection.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the collection.
	 * @return ArrayIterator an iterator for traversing the cookies in the collection.
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->_cookies);
	}

	/**
	 * Returns the number of cookies in the collection.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($collection)`.
	 * @return integer the number of cookies in the collection.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of cookies in the collection.
	 * @return integer the number of cookies in the collection.
	 */
	public function getCount()
	{
		return count($this->_cookies);
	}

	/**
	 * Returns the cookie with the specified name.
	 * @param string $name the cookie name
	 * @return Cookie the cookie with the specified name. Null if the named cookie does not exist.
	 * @see getValue()
	 */
	public function get($name)
	{
		return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
	}

	/**
	 * Returns the value of the named cookie.
	 * @param string $name the cookie name
	 * @param mixed $defaultValue the value that should be returned when the named cookie does not exist.
	 * @return mixed the value of the named cookie.
	 * @see get()
	 */
	public function getValue($name, $defaultValue = null)
	{
		return isset($this->_cookies[$name]) ? $this->_cookies[$name]->value : $defaultValue;
	}

	/**
	 * Adds a cookie to the collection.
	 * If there is already a cookie with the same name in the collection, it will be removed first.
	 * @param Cookie $cookie the cookie to be added
	 */
	public function add($cookie)
	{
		if (isset($this->_cookies[$cookie->name])) {
			$c = $this->_cookies[$cookie->name];
			setcookie($c->name, '', 0, $c->path, $c->domain, $c->secure, $c->httponly);
		}

		$value = $cookie->value;
		if ($this->enableValidation) {
			if ($this->validationKey === null) {
				$key = SecurityHelper::getSecretKey(__CLASS__ . '/' . Yii::$app->id);
			} else {
				$key = $this->validationKey;
			}
			$value = SecurityHelper::hashData(serialize($value), $key);
		}

		setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httponly);
		$this->_cookies[$cookie->name] = $cookie;
	}

	/**
	 * Removes a cookie from the collection.
	 * @param Cookie|string $cookie the cookie object or the name of the cookie to be removed.
	 */
	public function remove($cookie)
	{
		if (is_string($cookie) && isset($this->_cookies[$cookie])) {
			$cookie = $this->_cookies[$cookie];
		}
		if ($cookie instanceof Cookie) {
			setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httponly);
			unset($this->_cookies[$cookie->name]);
		}
	}

	/**
	 * Removes all cookies.
	 */
	public function removeAll()
	{
		foreach ($this->_cookies as $cookie) {
			setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httponly);
		}
		$this->_cookies = array();
	}

	/**
	 * Returns the collection as a PHP array.
	 * @return array the array representation of the collection.
	 * The array keys are cookie names, and the array values are the corresponding
	 * cookie objects.
	 */
	public function toArray()
	{
		return $this->_cookies;
	}

	/**
	 * Returns whether there is a cookie with the specified name.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($collection[$name])`.
	 * @param string $name the cookie name
	 * @return boolean whether the named cookie exists
	 */
	public function offsetExists($name)
	{
		return isset($this->_cookies[$name]);
	}

	/**
	 * Returns the cookie with the specified name.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$cookie = $collection[$name];`.
	 * This is equivalent to [[get()]].
	 * @param string $name the cookie name
	 * @return Cookie the cookie with the specified name, null if the named cookie does not exist.
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * Adds the cookie to the collection.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$collection[$name] = $cookie;`.
	 * This is equivalent to [[add()]].
	 * @param string $name the cookie name
	 * @param Cookie $cookie the cookie to be added
	 */
	public function offsetSet($name, $cookie)
	{
		$this->add($cookie);
	}

	/**
	 * Removes the named cookie.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($collection[$name])`.
	 * This is equivalent to [[remove()]].
	 * @param string $name the cookie name
	 */
	public function offsetUnset($name)
	{
		$this->remove($name);
	}

	/**
	 * Returns the current cookies in terms of [[Cookie]] objects.
	 * @return Cookie[] list of current cookies
	 */
	protected function loadCookies()
	{
		$cookies = array();
		if ($this->enableValidation) {
			if ($this->validationKey === null) {
				$key = SecurityHelper::getSecretKey(__CLASS__ . '/' . Yii::$app->id);
			} else {
				$key = $this->validationKey;
			}
			foreach ($_COOKIE as $name => $value) {
				if (is_string($value) && ($value = SecurityHelper::validateData($value, $key)) !== false) {
					$cookies[$name] = new Cookie(array(
						'name' => $name,
						'value' => @unserialize($value),
					));
				}
			}
		} else {
			foreach ($_COOKIE as $name => $value) {
				$cookies[$name] = new Cookie(array(
					'name' => $name,
					'value' => $value,
				));
			}
		}
		return $cookies;
	}
}
