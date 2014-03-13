<?php

namespace tests\_pages;

use yii\codeception\BasePage;

class LoginPage extends BasePage
{
    public $route = 'site/login';

    /**
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $this->guy->fillField('input[name="LoginForm[username]"]', $username);
        $this->guy->fillField('input[name="LoginForm[password]"]', $password);
        $this->guy->click('login-button');
    }
}
