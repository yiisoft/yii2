<?php
$I = new TestGuy($scenario);
$I->wantTo('ensure that home page works');
$I->amOnPage('');
$I->see('My Company');
$I->seeLink('About');
$I->click('About');
$I->see('This is the About page.');
