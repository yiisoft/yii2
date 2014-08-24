<?php
use tests\codeception\frontend\FunctionalTester;
use tests\codeception\frontend\_pages\AboutPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
