<?php
use codeception_frontend\FunctionalTester;
use codeception\frontend\_pages\AboutPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
