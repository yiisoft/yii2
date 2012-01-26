<?php

namespace yiiunit\framework\base;

class BarClass extends \yii\base\Component
{

}

class FooClass extends \yii\base\Component
{
	public function behaviors()
	{
		return array(
			'foo' => __NAMESPACE__ . '\BarBehavior',
		);
	}
}

class BarBehavior extends \yii\base\Behavior
{
	public $behaviorProperty = 'behavior property';

	public function behaviorMethod()
	{
		return 'behavior method';
	}
}

class BehaviorTest extends \yiiunit\TestCase
{
	public function testAttachAndAccessing()
	{
		$bar = new BarClass();
		$behavior = new BarBehavior();
		$bar->attachBehavior('bar', $behavior);
		$this->assertEquals('behavior property', $bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->behaviorMethod());
		$this->assertEquals('behavior property', $bar->asa('bar')->behaviorProperty);
		$this->assertEquals('behavior method', $bar->asa('bar')->behaviorMethod());
	}

	public function testAutomaticAttach()
	{
		$foo = new FooClass();
		$this->assertEquals('behavior property', $foo->behaviorProperty);
		$this->assertEquals('behavior method', $foo->behaviorMethod());
	}
}
