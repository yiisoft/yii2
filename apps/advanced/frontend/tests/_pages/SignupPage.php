<?php

namespace frontend\tests\_pages;

use \yii\codeception\BasePage;

class SignupPage extends BasePage
{

    public $route = 'site/signup';

    /**
     * @param array $signupData
     */
    public function submit(array $signupData)
    {
        foreach ($signupData as $field => $value) {
            $inputType = $field === 'body' ? 'textarea' : 'input';
            $this->guy->fillField($inputType . '[name="SignupForm[' . $field . ']"]', $value);
        }
        $this->guy->click('signup-button');
    }
}
