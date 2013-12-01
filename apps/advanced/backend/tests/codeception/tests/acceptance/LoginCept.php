<?php
$I = new WebGuy($scenario);
$I->wantTo('test backend login page');
$I->amOnPage('/');
$I->amGoingTo('try to login with not correct credentials');
LoginPage::of($I)->login('some-bad-login', 'some-bad-password');
$I->expectTo('see that password/username are incorrect');
$I->see('Incorrect username or password');
