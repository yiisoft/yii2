<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\swiftmailer;

use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use Yii;

/**
 * Mailer based on SwiftMailer library.
 *
 * By default PHP 'mail' function will be used as default email transport.
 * You can setup different email transport via [[vendorMailer]] property:
 * ~~~
 * 'components' => array(
 *     ...
 *     'email' => array(
 *         'class' => 'yii\swiftmailer\Mailer',
 *         'transport' => [
 *             'class' => 'Swift_SmtpTransport',
 *             'host' => 'localhost',
 *             'username' => 'username',
 *             'password' => 'password',
 *             'port' => '587',
 *             'encryption' => 'tls',
 *         ],
 *     ),
 *     ...
 * ),
 * ~~~
 *
 * @see http://swiftmailer.org
 *
 * @method Message createMessage(array $config = []) creates new message instance from given configuration.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Mailer extends BaseMailer
{
	/**
	 * @var string message default class name.
	 */
	public $messageClass = 'yii\swiftmailer\Message';
	/**
	 * @var \Swift_Mailer Swift mailer instance.
	 */
	private $_swiftMailer;
	/**
	 * @var \Swift_Transport|array Swift transport instance or its array configuration.
	 */
	private $_transport = [];

	/**
	 * @return array|\Swift_Mailer Swift mailer instance or array configuration.
	 */
	public function getSwiftMailer()
	{
		if (!is_object($this->_swiftMailer)) {
			$this->_swiftMailer = $this->createSwiftMailer();
		}
		return $this->_swiftMailer;
	}

	/**
	 * @param array|\Swift_Transport $transport
	 * @throws \yii\base\InvalidConfigException on invalid argument.
	 */
	public function setTransport($transport)
	{
		if (!is_array($transport) && !is_object($transport)) {
			throw new InvalidConfigException('"' . get_class($this) . '::transport" should be either object or array, "' . gettype($transport) . '" given.');
		}
		$this->_transport = $transport;
	}

	/**
	 * @return array|\Swift_Transport
	 */
	public function getTransport()
	{
		if (!is_object($this->_transport)) {
			$this->_transport = $this->createTransport($this->_transport);
		}
		return $this->_transport;
	}

	/**
	 * @inheritdoc
	 */
	public function send($message)
	{
		Yii::trace('Sending email message', __METHOD__);
		return ($this->getSwiftMailer()->send($message->getSwiftMessage()) > 0);
	}

	/**
	 * Creates Swift mailer instance.
	 * @return \Swift_Mailer mailer instance.
	 */
	protected function createSwiftMailer()
	{
		return \Swift_Mailer::newInstance($this->getTransport());
	}

	/**
	 * Creates email transport instance by its array configuration.
	 * @param array $config transport configuration.
	 * @throws \yii\base\InvalidConfigException on invalid transport configuration.
	 * @return \Swift_Transport transport instance.
	 */
	protected function createTransport(array $config)
	{
		if (array_key_exists('class', $config)) {
			$className = $config['class'];
			unset($config['class']);
		} else {
			$className = 'Swift_MailTransport';
		}
		$transport = call_user_func([$className, 'newInstance']);
		if (!empty($config)) {
			foreach ($config as $name => $value) {
				if (property_exists($transport, $name)) {
					$transport->$name = $value;
				} else {
					$setter = 'set' . $name;
					if (method_exists($transport, $setter)) {
						$transport->$setter($value);
					} else {
						throw new InvalidConfigException('Setting unknown property: ' . get_class($transport) . '::' . $name);
					}
				}
			}
		}
		return $transport;
	}
}