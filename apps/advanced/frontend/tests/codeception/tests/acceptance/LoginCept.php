<?php
use frontend\tests\codeception\tests\_pages\LoginPage;

$I = new WebGuy($scenario);
$loginPage = LoginPage::of($I);
$I->wantTo('ensure that login page works');

$I->amOnPage('?r=site/login');
$I->see('Login','h1');

$I->amGoingTo('try to login with empty credentials');
$loginPage->login('', '');
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('try to login with wrong credentials');
$loginPage->login('admin', 'wrong');
$I->expectTo('see validations errors');
$I->wait(3);
$I->see('Incorrect username or password.');
