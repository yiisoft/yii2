<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Inflector;
use yiiunit\TestCase;

class InflectorTest extends TestCase
{


    public function testPluralize()
    {
        $testData = array(
            'move' => 'moves',
            'foot' => 'feet',
            'child' => 'children',
            'human' => 'humans',
            'man' => 'men',
            'staff' => 'staff',
            'tooth' => 'teeth',
            'person' => 'people',
            'mouse' => 'mice',
            'touch' => 'touches',
            'hash' => 'hashes',
            'shelf' => 'shelves',
            'potato' => 'potatoes',
            'bus' => 'buses',
            'test' => 'tests',
            'car' => 'cars',
        );

        foreach ($testData as $testIn => $testOut) {
            $this->assertEquals($testOut, Inflector::pluralize($testIn));
            $this->assertEquals(ucfirst($testOut), ucfirst(Inflector::pluralize($testIn)));
        }
    }

    public function testSingularize()
    {
		$testData = array(
			'moves' => 'move',
			'feet' => 'foot',
			'children' => 'child',
			'humans' => 'human',
			'men' => 'man',
			'staff' => 'staff',
			'teeth' => 'tooth',
			'people' => 'person',
			'mice' => 'mouse',
			'touches' => 'touch',
			'hashes' => 'hash',
			'shelves' => 'shelf',
			'potatoes' => 'potato',
			'buses' => 'bus',
			'tests' => 'test',
			'cars' => 'car',
		);
		foreach ($testData as $testIn => $testOut) {
			$this->assertEquals($testOut, Inflector::singularize($testIn));
			$this->assertEquals(ucfirst($testOut), ucfirst(Inflector::singularize($testIn)));
		}
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
        $this->assertEquals("me_my_self_and_i", Inflector::underscore('MeMySelfAndI'));
    }

    public function testCamel2words()
    {
        $this->assertEquals('Camel Case', Inflector::camel2words('camelCase'));
        $this->assertEquals('Lower Case', Inflector::camel2words('lower_case'));
        $this->assertEquals('Tricky Stuff It Is Testing', Inflector::camel2words(' tricky_stuff.it-is testing... '));
    }

    public function testCamel2id()
    {
        $this->assertEquals('post-tag', Inflector::camel2id('PostTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('PostTag', '_'));

        $this->assertEquals('post-tag', Inflector::camel2id('postTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('postTag', '_'));
    }

    public function testId2camel()
    {
        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));

        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));
    }

    public function testHumanize()
    {
        $this->assertEquals("Me my self and i", Inflector::humanize('me_my_self_and_i'));
        $this->assertEquals("Me My Self And I", Inflector::humanize('me_my_self_and_i', true));
    }

    public function testVariablize()
    {
        $this->assertEquals("customerTable", Inflector::variablize('customer_table'));
    }

    public function testTableize()
    {
        $this->assertEquals("customer_tables", Inflector::tableize('customerTable'));
    }

    public function testSlug()
    {
        $this->assertEquals("this-is-a-title", Inflector::slug('this is a title'));
    }

    public function testClassify()
    {
        $this->assertEquals("CustomerTable", Inflector::classify('customer_tables'));
    }

    public function testOrdinalize()
    {
        $this->assertEquals('21st', Inflector::ordinalize('21'));
    }
}
