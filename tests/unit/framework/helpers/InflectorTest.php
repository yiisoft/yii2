<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Inflector;
use yiiunit\TestCase;

class InflectorTest extends TestCase
{


    public function testPluralize()
    {
        $this->assertEquals("people", Inflector::pluralize('person'));
        $this->assertEquals("fish", Inflector::pluralize('fish'));
        $this->assertEquals("men", Inflector::pluralize('man'));
        $this->assertEquals("tables", Inflector::pluralize('table'));
    }

    public function testSingularize()
    {
        $this->assertEquals("person", Inflector::singularize('people'));
        $this->assertEquals("fish", Inflector::singularize('fish'));
        $this->assertEquals("man", Inflector::singularize('men'));
        $this->assertEquals("table", Inflector::singularize('tables'));
    }

    public function testTitleize()
    {
        $this->assertEquals("Me my self and i", Inflector::titleize('MeMySelfAndI'));
        $this->assertEquals("Me My Self And I", Inflector::titleize('MeMySelfAndI', true));
    }

    public function testCamelize()
    {
        $this->assertEquals("MeMySelfAndI", Inflector::camelize('me my_self-andI'));
    }

    public function testUnderscore()
    {
        $this->assertEquals("me_my_self_and_i", Inflector::underscore('Me my self and I'));
    }

    public function testHumanize()
    {
        $this->assertEquals("Me my self and i", Inflector::humanize('me_my_self_and_i'));
        $this->assertEquals("Me My Self And i", Inflector::humanize('me_my_self_and_i'), true);
    }

}
