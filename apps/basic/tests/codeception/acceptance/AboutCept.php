<?php

use codeception\_pages\AboutPage;

$I = new WebGuy($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
