<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pklimov
 * Date: 22/10/13
 * Time: 12:47
 * To change this template use File | Settings | File Templates.
 */

namespace yiiunit\framework\base;

use yii\base\BehaviorTrait;
use yii\base\Component;
use yiiunit\TestCase;

class BehaviorTraitTest extends TestCase
{
	public function testEvent() {
		$component = new TestComponent();

		$expectedOutput = implode("\n", [
				'yiiunit\framework\base\TestComponent::runEventTest',
				'yiiunit\framework\base\TestTrait::onAfterEventTest_TestTrait',
				'custom event handler',
			]) . "\n";
		$this->expectOutputString($expectedOutput);
		$component->runEventTest();
	}
}

class TestComponent extends Component
{
	use BehaviorTrait, TestTrait;

	public function init()
	{
		$this->on('afterEventTest', function() {
			echo "custom event handler\n";
		});
	}

	public function runEventTest()
	{
		echo __METHOD__ . "\n";
		$this->trigger('afterEventTest');
	}
}

trait TestTrait
{
	public function onAfterEventTest_TestTrait()
	{
		echo __METHOD__ . "\n";
	}
}