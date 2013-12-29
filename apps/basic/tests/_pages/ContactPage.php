<?php

namespace tests\_pages;

use yii\codeception\BasePage;

class ContactPage extends BasePage
{
	public $route = 'site/contact';

	/**
	 * @param array $contactData
	 */
	public function submit(array $contactData)
	{
		$data = [];
		foreach ($contactData as $name => $value) {
			$data["ContactForm[$name]"] = $value;
		}
		$this->guy->submitForm('#contact-form', $data);
	}
}
