<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\base\InvalidConfigException;

/**
 * Class VendorMailer
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class VendorMailer extends BaseMailer
{
	/**
	 * @var string|callback vendor autoloader callback or path to autoloader file.
	 * If the vendor classes autoloading is already managed in some other place,
	 * for example via Composer, you should leave this field blank.
	 */
	public $autoload;
	/**
	 * @var array|object vendor mailer instance or its array configuration.
	 * Note: different implementation of [[VendorMailer]] may process configuration
	 * in the different way.
	 */
	private $_vendorMailer = array();

	/**
	 * Initializes the object.
	 * Registers the vendor autoloader if any.
	 */
	public function init()
	{
		$this->setupVendorAutoload();
	}

	/**
	 * @param array|object $value mailer instance or configuration.
	 * @throws \yii\base\InvalidConfigException on invalid argument.
	 */
	public function setVendorMailer($value)
	{
		if (!is_array($value) && !is_object($value)) {
			throw new InvalidConfigException('"' . get_class($this) . '::vendorMailer" should be either object or array, "' . gettype($value) . '" given.');
		}
		$this->_vendorMailer = $value;
	}

	/**
	 * @return object vendor mailer instance.
	 */
	public function getVendorMailer()
	{
		if (!is_object($this->_vendorMailer)) {
			$this->_vendorMailer = $this->createVendorMailer($this->_vendorMailer);
		}
		return $this->_vendorMailer;
	}

	/**
	 * Sets up the vendor autoloader if any is specified.
	 */
	protected function setupVendorAutoload()
	{
		if (!empty($this->autoload)) {
			if (is_string($this->autoload) && file_exists($this->autoload)) {
				require_once($this->autoload);
			} else {
				spl_autoload_register($this->autoload, true, true);
			}
		}
	}

	/**
	 * Creates vendor mailer instance from given configuration.
	 * @param array $config mailer configuration.
	 * @return object mailer instance.
	 */
	abstract protected function createVendorMailer(array $config);

	/**
	 * Creates the vendor email message instance.
	 * @return object email message instance.
	 */
	abstract public function createVendorMessage();
}