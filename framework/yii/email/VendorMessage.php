<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use Yii;

/**
 * Class VendorMessage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class VendorMessage extends BaseMessage
{
	/**
	 * @var object vendor message instance.
	 */
	private $_vendorMessage;

	public function __get($name)
	{
		$vendorMessage = $this->getVendorMessage();
		if (property_exists($vendorMessage, $name)) {
			return $vendorMessage->$name;
		}
		$getter = 'get' . $name;
		if (method_exists($vendorMessage, $getter)) {
			return $vendorMessage->$getter();
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value)
	{
		$vendorMessage = $this->getVendorMessage();
		if (property_exists($vendorMessage, $name)) {
			$vendorMessage->$name = $value;
			return;
		}
		$setter = 'set' . $name;
		if (method_exists($vendorMessage, $setter)) {
			$vendorMessage->$setter($value);
		} else {
			parent::__set($name, $value);
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