<?php
$I = new TestGuy($scenario);
$I->wantTo('ensure that about works');
$I->amOnPage('?r=site/about');
$I->see('About', 'h1');
