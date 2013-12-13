<?php
use tests\_pages\AboutPage;

$I = new WebGuy($scenario);
$I->wantTo('ensure that about works');
$I->amOnPage(AboutPage::$URL);
$I->see('About', 'h1');
