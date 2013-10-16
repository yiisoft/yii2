<?php

namespace yii\email\swift;

use yii\email\VendorMailer;

/**
 * Class Mailer
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Mailer extends VendorMailer
{
	/**
	 * Initializes the object.
	 */
	public function init()
	{
		if (empty($this->autoload)) {
			$this->autoload = __DIR__ . '/autoload.php';
		}
		parent::init();
	}

	public function send($message)
	{
		return ($this->getVendorMailer()->send($message->getVendorMessage()) > 0);
	}

	protected function createVendorMailer(array $config)
	{
		if (array_key_exists('transport', $config)) {
			$transportConfig = $config['transport'];
		} else {
			$transportConfig = array();
		}
		return \Swift_Mailer::newInstance($this->createTransport($transportConfig));
	}

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