<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use Yii;
use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;

/**
 * Class VendorMessage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class VendorMessage extends BaseMessage
{
	/**
	 * @var object vendor message instance.
	 */
	private $_vendorMessage;

	public function __get($name)
	{
		try {
			return parent::__get($name);
		} catch (UnknownPropertyException $exception) {
			$vendorMessage = $this->getVendorMessage();
			if (property_exists($vendorMessage, $name)) {
				return $vendorMessage->$name;
			}
			$getter = 'get' . $name;
			if (method_exists($vendorMessage, $getter)) {
				return $vendorMessage->$getter();
			} else {
				throw $exception;
			}
		}
	}

	public function __set($name, $value)
	{
		try {
			parent::__set($name, $value);
		} catch (UnknownPropertyException $exception) {
			$vendorMessage = $this->getVendorMessage();
			if (property_exists($vendorMessage, $name)) {
				$vendorMessage->$name = $value;
				return;
			}
			$setter = 'set' . $name;
			if (method_exists($vendorMessage, $setter)) {
				$vendorMessage->$setter($value);
			} else {
				throw $exception;
			}
		}
	}

	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter() !== null;
		} else {
			$vendorMessage = $this->getVendorMessage();
			if (property_exists($vendorMessage, $name)) {
				return isset($vendorMessage->$name);
			}
			if (method_exists($vendorMessage, $getter)) {
				return ($vendorMessage->$getter() !== null);
			} else {
				return false;
			}
		}
	}

	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
		} else {
			$vendorMessage = $this->getVendorMessage();
			if (property_exists($vendorMessage, $name)) {
				unset($vendorMessage->$name);
			} else {
				if (method_exists($vendorMessage, $setter)) {
					$vendorMessage->$setter(null);
				} elseif (method_exists($vendorMessage, 'get' . $name)) {
					throw new InvalidCallException('Unsetting read-only property: ' . get_class($vendorMessage) . '::' . $name);
				}
			}
		}
	}

	public function __call($name, $params)
	{
		$vendorMessage = $this->getVendorMessage();
		if (method_exists($vendorMessage, $name)) {
			return call_user_func_array(array($vendorMessage, $name), $params);
		}
		return parent::__call($name, $params);
	}

	/**
	 * @return object vendor message instance.
	 */
	public function getVendorMessage()
	{
		if (!is_object($this->_vendorMessage)) {
			$this->_vendorMessage = $this->createVendorMessage();
		}
		return $this->_vendorMessage;
	}

	/**
	 * Creates actual vendor message instance.
	 * @return object vendor message instance.
	 */
	protected function createVendorMessage()
	{
		return Yii::$app->getComponent('email')->createVendorMessage();
	}
}