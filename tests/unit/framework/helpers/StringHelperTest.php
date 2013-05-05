<?php
namespace yiiunit\framework\helpers;
use \yii\helpers\StringHelper as StringHelper;

/**
 * StringHelperTest
 */
class StringHelperTest extends \yii\test\TestCase
{
	public function testStrlen()
	{
		$this->assertEquals(4, StringHelper::strlen('this'));
		$this->assertEquals(6, StringHelper::strlen('это'));
	}

	public function testSubstr()
	{
		$this->assertEquals('th', StringHelper::substr('this', 0, 2));
		$this->assertEquals('э', StringHelper::substr('это', 0, 2));
	}

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

		foreach($testData as $testIn => $testOut) {
			$this->assertEquals($testOut, StringHelper::pluralize($testIn));
			$this->assertEquals(ucfirst($testOut), ucfirst(StringHelper::pluralize($testIn)));
		}
	}

	public function testCamel2words()
	{
		$this->assertEquals('Camel Case', StringHelper::camel2words('camelCase'));
		$this->assertEquals('Lower Case', StringHelper::camel2words('lower_case'));
		$this->assertEquals('Tricky Stuff It Is Testing', StringHelper::camel2words(' tricky_stuff.it-is testing... '));
	}

	public function testCamel2id()
	{
		$this->assertEquals('post-tag', StringHelper::camel2id('PostTag'));
		$this->assertEquals('post_tag', StringHelper::camel2id('PostTag', '_'));

		$this->assertEquals('post-tag', StringHelper::camel2id('postTag'));
		$this->assertEquals('post_tag', StringHelper::camel2id('postTag', '_'));
	}

	public function testId2camel()
	{
		$this->assertEquals('PostTag', StringHelper::id2camel('post-tag'));
		$this->assertEquals('PostTag', StringHelper::id2camel('post_tag', '_'));

		$this->assertEquals('PostTag', StringHelper::id2camel('post-tag'));
		$this->assertEquals('PostTag', StringHelper::id2camel('post_tag', '_'));
	}
}
