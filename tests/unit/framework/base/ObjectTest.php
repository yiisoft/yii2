<?php
class Foo extends \yii\base\Object
{
	public $prop;
}

/**
 * ObjectTest
 */
class ObjectTest extends \yii\test\TestCase
{
	public function testCreate()
	{
		$foo = Foo::create(array(
			'prop' => array(
				'test' => 'test',
			),
		));

		$this->assertEquals('test', $foo->prop['test']);
	}
}
