<?php
/**
 * Dictionary class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\DictionaryIterator;

/**
 * Dictionary implements a collection that stores key-value pairs.
 *
 * You can access, add or remove an item with a key by using
 * [[itemAt()]], [[add()]], and [[remove()]].
 *
 * To get the number of the items in the dictionary, use [[getCount()]].
 *
 * Because Dictionary implements a set of SPL interfaces, it can be used
 * like a regular PHP array as follows,
 *
 * ~~~
 * $dictionary[$key] = $value;		   // add a key-value pair
 * unset($dictionary[$key]);			 // remove the value with the specified key
 * if (isset($dictionary[$key]))		 // if the dictionary contains the key
 * foreach ($dictionary as $key=>$value) // traverse the items in the dictionary
 * $n = count($dictionary);			  // returns the number of items in the dictionary
 * ~~~
 *
 * @property integer $count the number of items in the dictionary
 * @property array $keys The keys in the dictionary
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CookieCollection extends \yii\base\Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var Cookie[] internal data storage
	 */
	private $_cookies = array();

	/**
	 * Constructor.
	 * Initializes the dictionary with an array or an iterable object.
	 * @param array $cookies the initial data to be populated into the dictionary.
	 * This can be an array or an iterable object.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($cookies = array(), $config = array())
	{
		$this->_cookies = $cookies;
		parent::__construct($config);
	}

	/**
	 * Returns an iterator for traversing the items in the dictionary.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the dictionary.
	 * @return DictionaryIterator an iterator for traversing the items in the dictionary.
	 */
	public function getIterator()
	{
		return new DictionaryIterator($this->_cookies);
	}

	/**
	 * Returns the number of items in the dictionary.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($dictionary)`.
	 * @return integer number of items in the dictionary.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the dictionary.
	 * @return integer the number of items in the dictionary
	 */
	public function getCount()
	{
		return count($this->_cookies);
	}

	/**
	 * Returns the keys stored in the dictionary.
	 * @return array the key list
	 */
	public function getNames()
	{
		return array_keys($this->_cookies);
	}

	/**
	 * Returns the item with the specified key.
	 * @param mixed $name the key
	 * @return Cookie the element with the specified key.
	 * Null if the key cannot be found in the dictionary.
	 */
	public function getCookie($name)
	{
		return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
	}

	/**
	 * Adds an item into the dictionary.
	 * Note, if the specified key already exists, the old value will be overwritten.
	 * @param Cookie $cookie value
	 * @throws Exception if the dictionary is read-only
	 */
	public function add(Cookie $cookie)
	{
		if (isset($this->_cookies[$cookie->name])) {
			$this->remove($this->_cookies[$cookie->name]);
		}
		setcookie($cookie->name, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
		$this->_cookies[$cookie->name] = $cookie;
	}

	/**
	 * Removes an item from the dictionary by its key.
	 * @param mixed $key the key of the item to be removed
	 * @return mixed the removed value, null if no such key exists.
	 * @throws Exception if the dictionary is read-only
	 */
	public function remove(Cookie $cookie)
	{
		setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
		unset($this->_cookies[$cookie->name]);
	}

	/**
	 * Removes all items from the dictionary.
	 * @param boolean $safeClear whether to clear every item by calling [[remove]].
	 * Defaults to false, meaning all items in the dictionary will be cleared directly
	 * without calling [[remove]].
	 */
	public function clear($safeClear = false)
	{
		if ($safeClear) {
			foreach (array_keys($this->_cookies) as $key) {
				$this->remove($key);
			}
		} else {
			$this->_cookies = array();
		}
	}

	/**
	 * Returns the dictionary as a PHP array.
	 * @return array the list of items in array
	 */
	public function toArray()
	{
		return $this->_cookies;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($dictionary[$offset])`.
	 * This is equivalent to [[contains]].
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->_cookies[$offset]);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $dictionary[$offset];`.
	 * This is equivalent to [[itemAt]].
	 * @param mixed $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->getCookie($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$dictionary[$offset] = $item;`.
	 * If the offset is null, the new item will be appended to the dictionary.
	 * Otherwise, the existing item at the offset will be replaced with the new item.
	 * This is equivalent to [[add]].
	 * @param mixed $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->add($item);
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($dictionary[$offset])`.
	 * This is equivalent to [[remove]].
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		if (isset($this->_cookies[$offset])) {
			$this->remove($this->_cookies[$offset]);
		}
	}

	/**
	 * @return array list of validated cookies
	 */
	protected function loadCookies($data)
	{
		$cookies = array();
		if ($this->_request->enableCookieValidation) {
			$sm = Yii::app()->getSecurityManager();
			foreach ($_COOKIE as $name => $value) {
				if (is_string($value) && ($value = $sm->validateData($value)) !== false) {
					$cookies[$name] = new CHttpCookie($name, @unserialize($value));
				}
			}
		} else {
			foreach ($_COOKIE as $name => $value) {
				$cookies[$name] = new CHttpCookie($name, $value);
			}
		}
		return $cookies;
	}
}
