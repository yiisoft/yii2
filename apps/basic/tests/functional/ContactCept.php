<?php
$I = new TestGuy($scenario);
$I->wantTo('ensure that contact works');
$I->amOnPage('?r=site/contact');
$I->see('Contact', 'h1');

$I->submitForm('#contact-form', array());
$I->see('Contact', 'h1');
$I->see('Name cannot be blank');
$I->see('Email cannot be blank');
$I->see('Subject cannot be blank');
$I->see('Body cannot be blank');
$I->see('The verification code is incorrect');

$I->submitForm('#contact-form', array(
	'ContactForm[name]' => 'tester',
	'ContactForm[email]' => 'tester.email',
	'ContactForm[subject]' => 'test subject',
	'ContactForm[body]' => 'test content',
	'ContactForm[verifyCode]' => 'testme',
));
$I->dontSee('Name cannot be blank', '.help-inline');
$I->see('Email is not a valid email address.');
$I->dontSee('Subject cannot be blank', '.help-inline');
$I->dontSee('Body cannot be blank', '.help-inline');
$I->dontSee('The verification code is incorrect', '.help-inline');

$I->submitForm('#contact-form', array(
	'ContactForm[name]' => 'tester',
	'ContactForm[email]' => 'tester@example.com',
	'ContactForm[subject]' => 'test subject',
	'ContactForm[body]' => 'test content',
	'ContactForm[verifyCode]' => 'testme',
));
$I->dontSeeElement('#contact-form');
$I->see('Thank you for contacting us. We will respond to you as soon as possible.');
