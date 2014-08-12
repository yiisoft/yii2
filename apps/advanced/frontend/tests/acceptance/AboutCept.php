<?php

use frontend\tests\_pages\AboutPage;
use frontend\WebGuy;

$I = new WebGuy($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
