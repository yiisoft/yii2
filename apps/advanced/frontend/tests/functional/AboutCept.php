<?php

use frontend\tests\_pages\AboutPage;

$I = new TestGuy($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
