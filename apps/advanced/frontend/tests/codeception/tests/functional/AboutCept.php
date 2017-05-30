<?php
$I = new TestGuy($scenario);
$I->wantTo('ensure that about page works');
$I->amOnPage('?r=site/about');
$I->see('About','h1');
$I->see('This is the About page. You may modify the following file to customize its content');


