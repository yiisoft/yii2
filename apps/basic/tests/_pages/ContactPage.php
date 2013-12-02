<?php

namespace tests\_pages;

class ContactPage extends BasePage
{

	public static $URL = '?r=site/contact';

	/**
	 * contact form name text field locator
	 * @var string 
	 */
	public $name = 'input[name="ContactForm[name]"]';

	/**
	 * contact form email text field locator
	 * @var string
	 */
	public $email = 'input[name="ContactForm[email]"]';

	/**
	 * contact form subject text field locator
	 * @var string
	 */
	public $subject = 'input[name="ContactForm[subject]"]';

	/**
	 * contact form body textarea locator
	 * @var string
	 */
	public $body = 'textarea[name="ContactForm[body]"]';

	/**
	 * contact form verification code text field locator
	 * @var string
	 */
	public $verifyCode = 'input[name="ContactForm[verifyCode]"]';

	/**
	 * contact form submit button
	 * @var string
	 */
	public $button = 'button[type=submit]';

	/**
	 * 
	 * @param array $contactData
	 */
	public function submit(array $contactData)
	{
		if (!empty($contactData))
		{
			$this->guy->fillField($this->name,$contactData['name']);
			$this->guy->fillField($this->email,$contactData['email']);
			$this->guy->fillField($this->subject,$contactData['subject']);
			$this->guy->fillField($this->body,$contactData['body']);
			$this->guy->fillField($this->verifyCode,$contactData['verifyCode']);
		}
		$this->guy->click($this->button);
	}

}
