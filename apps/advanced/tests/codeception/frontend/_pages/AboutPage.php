<?php

namespace codeception\frontend\_pages;

use yii\codeception\BasePage;

/**
 * Represents about page
 * @property \codeception_frontend\AcceptanceTester|\codeception_frontend\FunctionalTester $actor
 */
class AboutPage extends BasePage
{
    public $route = 'site/about';
}
