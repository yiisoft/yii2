<?php

use tests\_pages\AboutPage;

$I = new TestGuy($scenario);
$I->wantTo('ensure that about works');
$I->amOnPage(AboutPage::$URL);
$I->see('About', 'h1');
