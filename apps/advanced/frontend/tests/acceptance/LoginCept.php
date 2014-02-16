<?php

use common\tests\_pages\LoginPage;

$I = new WebGuy($scenario);
$I->wantTo('ensure login page works');

$loginPage = LoginPage::openBy($I);

$I->amGoingTo('submit login form with no data');
$loginPage->login('', '');
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('try to login with wrong credentials');
$I->expectTo('see validations errors');
$loginPage->login('admin', 'wrong');
$I->expectTo('see validations errors');
$I->see('Incorrect username or password.');

$I->amGoingTo('try to login with correct credentials');
$loginPage->login('erau', 'password_0');
$I->expectTo('see that user is logged');
$I->see('Logout (erau)');
$I->dontSee('Login');
$I->dontSee('Signup');
$I->click('Logout (erau)');
$I->dontSee('Logout (erau)');
$I->see('Login');