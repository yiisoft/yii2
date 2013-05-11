<?php

require_once(realpath(__DIR__.'/../../../../yii/requirements/YiiRequirementChecker.php'));

use yiiunit\TestCase;

/**
 * Test case for {@link YiiRequirementChecker}.
 * @see YiiRequirementChecker
 */
class YiiRequirementCheckerTest extends TestCase
{
	public function testCheck()
	{
		$requirementsChecker = new YiiRequirementChecker();

		$requirements = array(
			'requirementPass' => array(
				'name' => 'Requirement 1',
				'mandatory' => true,
				'condition' => true,
				'by' => 'Requirement 1',
				'memo' => 'Requirement 1',
			),
			'requirementError' => array(
				'name' => 'Requirement 2',
				'mandatory' => true,
				'condition' => false,
				'by' => 'Requirement 2',
				'memo' => 'Requirement 2',
			),
			'requirementWarning' => array(
				'name' => 'Requirement 3',
				'mandatory' => false,
				'condition' => false,
				'by' => 'Requirement 3',
				'memo' => 'Requirement 3',
			),
		);

		$checkResult = $requirementsChecker->check($requirements)->getResult();
		$summary = $checkResult['summary'];

		$this->assertEquals(count($requirements), $summary['total'], 'Wrong summary total!');
		$this->assertEquals(1, $summary['errors'], 'Wrong summary errors!');
		$this->assertEquals(1, $summary['warnings'], 'Wrong summary warnings!');

		$checkedRequirements = $checkResult['requirements'];

		$this->assertEquals(false, $checkedRequirements['requirementPass']['error'], 'Passed requirement has an error!');
		$this->assertEquals(false, $checkedRequirements['requirementPass']['warning'], 'Passed requirement has a warning!');

		$this->assertEquals(true, $checkedRequirements['requirementError']['error'], 'Error requirement has no error!');

		$this->assertEquals(false, $checkedRequirements['requirementWarning']['error'], 'Error requirement has an error!');
		$this->assertEquals(true, $checkedRequirements['requirementWarning']['warning'], 'Error requirement has no warning!');
	}

	public function testCheckEval() {
		$requirementsChecker = new YiiRequirementChecker();

		$requirements = array(
			'requirementPass' => array(
				'name' => 'Requirement 1',
				'mandatory' => true,
				'condition' => 'eval:2>1',
				'by' => 'Requirement 1',
				'memo' => 'Requirement 1',
			),
			'requirementError' => array(
				'name' => 'Requirement 2',
				'mandatory' => true,
				'condition' => 'eval:2<1',
				'by' => 'Requirement 2',
				'memo' => 'Requirement 2',
			),
		);

		$checkResult = $requirementsChecker->check($requirements)->getResult();
		$checkedRequirements = $checkResult['requirements'];

		$this->assertEquals(false, $checkedRequirements['requirementPass']['error'], 'Passed requirement has an error!');
		$this->assertEquals(false, $checkedRequirements['requirementPass']['warning'], 'Passed requirement has a warning!');

		$this->assertEquals(true, $checkedRequirements['requirementError']['error'], 'Error requirement has no error!');
	}
}
