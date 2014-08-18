<?php

namespace codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents about page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class AboutPage extends BasePage
{
    public $route = 'site/about';
}
