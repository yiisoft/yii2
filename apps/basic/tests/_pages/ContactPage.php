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
            $inputType = $field === 'body' ? 'textarea' : 'input';
            $this->guy->fillField($inputType . '[name="ContactForm[' . $field . ']"]', $value);
        }
        $this->guy->click('contact-button');
    }
}
