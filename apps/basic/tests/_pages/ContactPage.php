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
		foreach ($contactData as $field => $value) {
			if ($field == 'body') {
				$this->guy->fillField('textarea[name="ContactForm[' . $field . ']"]', $value);
			} else {
				$this->guy->fillField('input[name="ContactForm[' . $field .']"]', $value);				
			}
		}
		$this->guy->click('Submit','#contact-form');
	}
}
