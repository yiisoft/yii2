<?php

require_once(realpath(__DIR__.'/../../../../yii/requirements/YiiRequirementChecker.php'));

use yiiunit\TestCase;

/**
 * Test case for {@link YiiRequirementChecker}.
 * @see YiiRequirementChecker
 */
class YiiRequirementCheckerTest extends TestCase
{
	public function testCreate() {
		$requirementChecker = new YiiRequirementChecker();
		$this->assertTrue(is_object($requirementChecker));
	}
}
