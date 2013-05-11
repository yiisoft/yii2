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
		$requirementsKeys = array_flip(array_keys($requirements));

		$this->assertEquals(false, $checkedRequirements[$requirementsKeys['requirementPass']]['error'], 'Passed requirement has an error!');
		$this->assertEquals(false, $checkedRequirements[$requirementsKeys['requirementPass']]['warning'], 'Passed requirement has a warning!');

		$this->assertEquals(true, $checkedRequirements[$requirementsKeys['requirementError']]['error'], 'Error requirement has no error!');

		$this->assertEquals(false, $checkedRequirements[$requirementsKeys['requirementWarning']]['error'], 'Error requirement has an error!');
		$this->assertEquals(true, $checkedRequirements[$requirementsKeys['requirementWarning']]['warning'], 'Error requirement has no warning!');
	}

	/**
	 * @depends testCheck
	 */
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
		$requirementsKeys = array_flip(array_keys($requirements));

		$this->assertEquals(false, $checkedRequirements[$requirementsKeys['requirementPass']]['error'], 'Passed requirement has an error!');
		$this->assertEquals(false, $checkedRequirements[$requirementsKeys['requirementPass']]['warning'], 'Passed requirement has a warning!');

		$this->assertEquals(true, $checkedRequirements[$requirementsKeys['requirementError']]['error'], 'Error requirement has no error!');
	}

	/**
	 * @depends testCheck
	 */
	public function testCheckChained()
	{
		$requirementsChecker = new YiiRequirementChecker();

		$requirements1 = array(
			array(
				'name' => 'Requirement 1',
				'mandatory' => true,
				'condition' => true,
				'by' => 'Requirement 1',
				'memo' => 'Requirement 1',
			),
		);
		$requirements2 = array(
			array(
				'name' => 'Requirement 2',
				'mandatory' => true,
				'condition' => true,
				'by' => 'Requirement 2',
				'memo' => 'Requirement 2',
			),
		);
		$checkResult = $requirementsChecker->check($requirements1)->check($requirements2)->getResult();

		$mergedRequirements = array_merge($requirements1, $requirements2);

		$this->assertEquals(count($mergedRequirements), $checkResult['summary']['total'], 'Wrong total checks count!');
		foreach ($mergedRequirements as $key => $mergedRequirement) {
			$this->assertEquals($mergedRequirement['name'], $checkResult['requirements'][$key]['name'], 'Wrong requirements list!');
		}
	}
}
