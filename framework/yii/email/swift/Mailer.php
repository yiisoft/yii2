<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email\swift;

use yii\email\VendorMailer;

/**
 * Mailer based on SwiftMailer library.
 *
 * By default PHP 'mail' function will be used as default email transport.
 * You can setup different email transport via [[vendorMailer]] property:
 * ~~~
 * 'components' => array(
 *     ...
 *     'email' => array(
 *         'class' => 'yii\email\swift\Mailer',
 *         'vendorMailer' => [
 *             'transport' => [
 *                 'type' => 'smtp',
 *                 'host' => 'localhost',
 *                 'username' => 'username',
 *                 'password' => 'password',
 *                 'port' => '587',
 *                 'encryption' => 'tls',
 *             ],
 *         ],
 *     ),
 *     ...
 * ),
 * ~~~
 *
 * @see http://swiftmailer.org
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Mailer extends VendorMailer
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!class_exists('Swift', false) && empty($this->autoload)) {
			$this->autoload = __DIR__ . '/autoload.php';
		}
		parent::init();
	}

	/**
	 * @inheritdoc
	 */
	public function send($message)
	{
		return ($this->getVendorMailer()->send($message->getVendorMessage()) > 0);
	}

	/**
	 * Creates Swift mailer instance from given configuration.
	 * @param array $config mailer configuration.
	 * @return \Swift_Mailer mailer instance.
	 */
	protected function createVendorMailer(array $config)
	{
		if (array_key_exists('transport', $config)) {
			$transportConfig = $config['transport'];
		} else {
			$transportConfig = array();
		}
		return \Swift_Mailer::newInstance($this->createTransport($transportConfig));
	}

	/**
	 * Creates the Swift email message instance.
	 * @return \Swift_Message email message instance.
	 */
	public function createVendorMessage()
	{
		return new \Swift_Message();
	}

	/**
	 * Creates email transport instance by its array configuration.
	 * @param array $config transport configuration.
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
		$transport = call_user_func(array($className, 'newInstance'));
		if (!empty($config)) {
			foreach ($config as $name => $value)
				$transport->{'set' . $name}($value); // sets option with the setter method
		}
		return $transport;
	}
}