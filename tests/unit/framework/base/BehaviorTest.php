<?php
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

/**
 * BehaviorTest
 */
class BehaviorTest extends \yii\test\TestCase
{
	public function testAttachAndAccessing()
	{
		$bar = BarClass::create();
		$behavior = new BarBehavior();
		$bar->attachBehavior('bar', $bar);
		$this->assertEquals('behavior property', $bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->behaviorMethod);
		$this->assertEquals('behavior property', $bar->bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->bar->behaviorMethod);
	}
}
