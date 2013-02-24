<?php

namespace yiiunit\framework\base;

use yii\base\Behavior;
use yii\base\Component;
use yiiunit\TestCase;

class BarClass extends Component
{

}

class FooClass extends Component
{
	public function behaviors()
	{
		return array(
			'foo' => __NAMESPACE__ . '\BarBehavior',
		);
	}
}

class BarBehavior extends Behavior
{
	public $behaviorProperty = 'behavior property';

	public function behaviorMethod()
	{
		return 'behavior method';
	}
}

class BehaviorTest extends TestCase
{
	public function testAttachAndAccessing()
	{
		$bar = new BarClass();
		$behavior = new BarBehavior();
		$bar->attachBehavior('bar', $behavior);
		$this->assertEquals('behavior property', $bar->behaviorProperty);
		$this->assertEquals('behavior method', $bar->behaviorMethod());
		$this->assertEquals('behavior property', $bar->getBehavior('bar')->behaviorProperty);
		$this->assertEquals('behavior method', $bar->getBehavior('bar')->behaviorMethod());

		$behavior = new BarBehavior(array('behaviorProperty' => 'reattached'));
		$bar->attachBehavior('bar', $behavior);
		$this->assertEquals('reattached', $bar->behaviorProperty);
	}

	public function testAutomaticAttach()
	{
		$foo = new FooClass();
		$this->assertEquals('behavior property', $foo->behaviorProperty);
		$this->assertEquals('behavior method', $foo->behaviorMethod());
	}
}
