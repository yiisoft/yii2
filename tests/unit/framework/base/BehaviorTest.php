<?php

namespace yiiunit\framework\base;

class BarClass extends \yii\base\Component
{

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
		$bar = BarClass::newInstance();
		$behavior = new BarBehavior();
		$bar->attachBehavior('bar', $behavior);
		$this->assertEquals('behavior property', $bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->behaviorMethod());
		$this->assertEquals('behavior property', $bar->bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->bar->behaviorMethod());
	}
}
