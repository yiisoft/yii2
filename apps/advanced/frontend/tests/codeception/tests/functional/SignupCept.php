<?php
use \frontend\tests\codeception\tests\functional\_pages\SignupPage;

$I = new TestGuy($scenario);
$signupPage = SignupPage::of($I);

$I->wantTo('ensure that signup page works');
$I->amOnPage('?r=site/signup');
$I->see('Signup','h1');

$I->amGoingTo('submit signup form with no data');
$signupPage->signup([]);
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Email cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('submit signup form with correct data');
$signupPage->signup([
	'username'	=>	'tester',
	'email'		=>	'tester@example.com',
	'password'	=>	'testerpassword',
]);
$I->expectTo('see created user info');
$I->see('Logout (tester)');
