<?php

namespace tests\functional\_pages;

class ContactPage extends \tests\_pages\ContactPage
{
	/**
	 * contact form name text field locator
	 * @var string 
	 */
	public $name = 'ContactForm[name]';
	/**
	 * contact form email text field locator
	 * @var string
	 */
	public $email = 'ContactForm[email]';
	/**
	 * contact form subject text field locator
	 * @var string
	 */
	public $subject = 'ContactForm[subject]';
	/**
	 * contact form body textarea locator
	 * @var string
	 */
	public $body = 'ContactForm[body]';
	/**
	 * contact form verification code text field locator
	 * @var string
	 */
	public $verifyCode = 'ContactForm[verifyCode]';

	/**
	 * 
	 * @param array $contactData
	 */
	public function submit(array $contactData)
	{
		if (empty($contactData)) {
			$this->guy->submitForm('#contact-form', []);
		} else {
			$this->guy->submitForm('#contact-form', [
				$this->name			=>	$contactData['name'],
				$this->email		=>	$contactData['email'],
				$this->subject		=>	$contactData['subject'],
				$this->body			=>	$contactData['body'],
				$this->verifyCode	=>	$contactData['verifyCode'],
			]);
		}
	}
}
