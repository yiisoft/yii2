<?php
$I = new WebGuy($scenario);
$I->wantTo('ensure that index page works');
$I->amOnPage('');
$I->see('Congratulations!');
$I->see('You have successfully created your Yii-powered application.');
