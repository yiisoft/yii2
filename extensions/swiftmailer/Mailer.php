<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\swiftmailer;

use Yii;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

/**
 * Mailer implements a mailer based on SwiftMailer.
 *
 * To use Mailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'email' => [
 *         'class' => 'yii\swiftmailer\Mailer',
 *         'transport' => [
 *             'class' => 'Swift_SmtpTransport',
 *             'host' => 'localhost',
 *             'username' => 'username',
 *             'password' => 'password',
 *             'port' => '587',
 *             'encryption' => 'tls',
 *         ],
 *     ],
 *     ...
 * ],
 * ~~~
 *
 * You may also skip the configuration of the [[transport]] property. In that case, the default
 * PHP `mail()` function will be used to send emails.
 *
 * To send an email, you may use the following code:
 *
 * ~~~
 * Yii::$app->mail->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ~~~
 *
 * @see http://swiftmailer.org
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
	 * @throws InvalidConfigException on invalid argument.
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
	protected function sendMessage($message)
	{
		$address = $message->getTo();
		if (is_array($address)) {
			$address = implode(', ', array_keys($address));
		}
		Yii::info('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);
		return $this->getSwiftMailer()->send($message->getSwiftMessage()) > 0;
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
		if (isset($config['class'])) {
			$className = $config['class'];
			unset($config['class']);
		} else {
			$className = 'Swift_MailTransport';
		}
		/** @var \Swift_MailTransport $transport */
		$transport = $className::newInstance();
		if (!empty($config)) {
			foreach ($config as $name => $value) {
				if (property_exists($transport, $name)) {
					$transport->$name = $value;
				} else {
					$setter = 'set' . $name;
					if (method_exists($transport, $setter) || method_exists($transport, '__call')) {
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
