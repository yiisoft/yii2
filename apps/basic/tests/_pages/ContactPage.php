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
			if (in_array($field, ['name','email','subject','verifyCode'])) {
				$this->guy->fillField('input[name="ContactForm[' . $field .']"]', $value);
			}
		}
		
		if (isset($contactData['body'])) {
			$this->guy->fillField('textarea[name="ContactForm[body]"]',$contactData['body']);
		}
		$this->guy->click('Submit','#contact-form');
	}
}
