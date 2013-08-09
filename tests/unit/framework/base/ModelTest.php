<?php

namespace yiiunit\framework\base;
use yii\base\Model;
use yiiunit\TestCase;
use yiiunit\data\base\Speaker;
use yiiunit\data\base\Singer;
use yiiunit\data\base\InvalidRulesModel;

/**
 * ModelTest
 */
class ModelTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testGetAttributeLabel()
	{
		$speaker = new Speaker();
		$this->assertEquals('First Name', $speaker->getAttributeLabel('firstName'));
		$this->assertEquals('This is the custom label', $speaker->getAttributeLabel('customLabel'));
		$this->assertEquals('Underscore Style', $speaker->getAttributeLabel('underscore_style'));
	}

	public function testGetAttributes()
	{
		$speaker = new Speaker();
		$speaker->firstName = 'Qiang';
		$speaker->lastName = 'Xue';

		$this->assertEquals(array(
			'firstName' => 'Qiang',
			'lastName' => 'Xue',
			'customLabel' => null,
			'underscore_style' => null,
		), $speaker->getAttributes());

		$this->assertEquals(array(
			'firstName' => 'Qiang',
			'lastName' => 'Xue',
		), $speaker->getAttributes(array('firstName', 'lastName')));

		$this->assertEquals(array(
			'firstName' => 'Qiang',
			'lastName' => 'Xue',
		), $speaker->getAttributes(null, array('customLabel', 'underscore_style')));

		$this->assertEquals(array(
			'firstName' => 'Qiang',
		), $speaker->getAttributes(array('firstName', 'lastName'), array('lastName', 'customLabel', 'underscore_style')));
	}

	public function testSetAttributes()
	{
		// by default mass assignment doesn't work at all
		$speaker = new Speaker();
		$speaker->setAttributes(array('firstName' => 'Qiang', 'underscore_style' => 'test'));
		$this->assertNull($speaker->firstName);
		$this->assertNull($speaker->underscore_style);

		// in the test scenario
		$speaker = new Speaker();
		$speaker->setScenario('test');
		$speaker->setAttributes(array('firstName' => 'Qiang', 'underscore_style' => 'test'));
		$this->assertNull($speaker->underscore_style);
		$this->assertEquals('Qiang', $speaker->firstName);

		$speaker->setAttributes(array('firstName' => 'Qiang', 'underscore_style' => 'test'), false);
		$this->assertEquals('test', $speaker->underscore_style);
		$this->assertEquals('Qiang', $speaker->firstName);
	}

	public function testActiveAttributes()
	{
		// by default mass assignment doesn't work at all
		$speaker = new Speaker();
		$this->assertEmpty($speaker->activeAttributes());

		$speaker = new Speaker();
		$speaker->setScenario('test');
		$this->assertEquals(array('firstName', 'lastName', 'underscore_style'), $speaker->activeAttributes());
	}

	public function testIsAttributeSafe()
	{
		// by default mass assignment doesn't work at all
		$speaker = new Speaker();
		$this->assertFalse($speaker->isAttributeSafe('firstName'));

		$speaker = new Speaker();
		$speaker->setScenario('test');
		$this->assertTrue($speaker->isAttributeSafe('firstName'));

	}

	public function testErrors()
	{
		$speaker = new Speaker();

		$this->assertEmpty($speaker->getErrors());
		$this->assertEmpty($speaker->getErrors('firstName'));
		$this->assertEmpty($speaker->getFirstErrors());

		$this->assertFalse($speaker->hasErrors());
		$this->assertFalse($speaker->hasErrors('firstName'));

		$speaker->addError('firstName', 'Something is wrong!');
		$this->assertEquals(array('firstName' => array('Something is wrong!')), $speaker->getErrors());
		$this->assertEquals(array('Something is wrong!'), $speaker->getErrors('firstName'));

		$speaker->addError('firstName', 'Totally wrong!');
		$this->assertEquals(array('firstName' => array('Something is wrong!', 'Totally wrong!')), $speaker->getErrors());
		$this->assertEquals(array('Something is wrong!', 'Totally wrong!'), $speaker->getErrors('firstName'));

		$this->assertTrue($speaker->hasErrors());
		$this->assertTrue($speaker->hasErrors('firstName'));
		$this->assertFalse($speaker->hasErrors('lastName'));

		$this->assertEquals(array('Something is wrong!'), $speaker->getFirstErrors());
		$this->assertEquals('Something is wrong!', $speaker->getFirstError('firstName'));
		$this->assertNull($speaker->getFirstError('lastName'));

		$speaker->addError('lastName', 'Another one!');
		$this->assertEquals(array(
			'firstName' => array(
				'Something is wrong!',
				'Totally wrong!',
			),
			'lastName' => array('Another one!'),
		), $speaker->getErrors());

		$speaker->clearErrors('firstName');
		$this->assertEquals(array(
			'lastName' => array('Another one!'),
		), $speaker->getErrors());

		$speaker->clearErrors();
		$this->assertEmpty($speaker->getErrors());
		$this->assertFalse($speaker->hasErrors());
	}

	public function testArraySyntax()
	{
		$speaker = new Speaker();

		// get
		$this->assertNull($speaker['firstName']);

		// isset
		$this->assertFalse(isset($speaker['firstName']));

		// set
		$speaker['firstName'] = 'Qiang';

		$this->assertEquals('Qiang', $speaker['firstName']);
		$this->assertTrue(isset($speaker['firstName']));

		// iteration
		$attributes = array();
		foreach ($speaker as $key => $attribute) {
			$attributes[$key] = $attribute;
		}
		$this->assertEquals(array(
			'firstName' => 'Qiang',
			'lastName' => null,
			'customLabel' => null,
			'underscore_style' => null,
		), $attributes);

		// unset
		unset($speaker['firstName']);

		// exception isn't expected here
		$this->assertNull($speaker['firstName']);
		$this->assertFalse(isset($speaker['firstName']));
	}

	public function testDefaults()
	{
		$singer = new Model();
		$this->assertEquals(array(), $singer->rules());
		$this->assertEquals(array(), $singer->attributeLabels());
	}

	public function testDefaultScenarios()
	{
		$singer = new Singer();
		$this->assertEquals(array('default' => array('lastName', 'underscore_style')), $singer->scenarios());
	}

	public function testIsAttributeRequired()
	{
		$singer = new Singer();
		$this->assertFalse($singer->isAttributeRequired('firstName'));
		$this->assertTrue($singer->isAttributeRequired('lastName'));
	}

	public function testCreateValidators()
	{
		$this->setExpectedException('yii\base\InvalidConfigException', 'Invalid validation rule: a rule must specify both attribute names and validator type.');

		$invalid = new InvalidRulesModel();
		$invalid->createValidators();
	}
}
