<?php
use codeception_frontend\TestGuy;
use codeception\frontend\_pages\AboutPage;

$I = new TestGuy($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
