<?php
$I = new TestGuy($scenario);
$I->wantTo('test backend login page in functional');
$I->amOnPage('?r=site/login');

$I->amGoingTo('try to login with empty credentials');
$I->submitForm('#login-form', []);
$I->expectTo('see validations error');
$I->see('Username cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('try to login with not correct credentials');
$I->submitForm('#login-form', [
	'LoginForm[username]' => 'admin',
	'LoginForm[password]' => 'wrong',
]);

$I->expectTo('see validations error');
$I->see('Incorrect username or password.');
