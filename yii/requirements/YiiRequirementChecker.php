<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * YiiRequirementChecker allows checking, if current system meets the requirements for running the application.
 *
 * @property array|null $result the check results.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YiiRequirementChecker
{
	/**
	 * Check the given requirements, collecting results into internal field.
	 * This method can be invoked several times checking different requirement sets.
	 * Use {@link getResult()} or {@link render()} to get the results.
	 * @param array $requirements requirements to be checked.
	 * @return YiiRequirementChecker self instance.
	 */
	function check($requirements)
	{
		if (!is_array($requirements)) {
			$this->usageError("Requirements must be an array!");
		}
		if (!isset($this->result)) {
			$this->result = array(
				'summary' => array(
					'total' => 0,
					'errors' => 0,
					'warnings' => 0,
				),
				'requirements' => array(),
			);
		}
		foreach ($requirements as $key => $rawRequirement) {
			$requirement = $this->normalizeRequirement($rawRequirement, $key);
			$this->result['summary']['total']++;
			if (!$requirement['condition']) {
				if ($requirement['mandatory']) {
					$requirement['error'] = true;
					$requirement['warning'] = true;
					$this->result['summary']['errors']++;
				} else {
					$requirement['error'] = false;
					$requirement['warning'] = true;
					$this->result['summary']['warnings']++;
				}
			} else {
				$requirement['error'] = false;
				$requirement['warning'] = false;
			}
			$this->result['requirements'][] = $requirement;
		}
		return $this;
	}

	/**
	 * Return the check results.
	 * @return array|null check results.
	 */
	function getResult()
	{
		if (isset($this->result)) {
			return $this->result;
		} else {
			return null;
		}
	}

	/**
	 * Renders the requirements check result.
	 * The output will vary depending is a script running from web or from console.
	 */
	function render()
	{
		if (!isset($this->result)) {
			$this->usageError('Nothing to render!');
		}
		$baseViewFilePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'views';
		if (array_key_exists('argv', $_SERVER)) {
			$viewFileName = $baseViewFilePath.DIRECTORY_SEPARATOR.'console'.DIRECTORY_SEPARATOR.'index.php';
		} else {
			$viewFileName = $baseViewFilePath.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'index.php';
		}
		$this->renderViewFile($viewFileName, $this->result);
	}

	/**
	 * Renders a view file.
	 * This method includes the view file as a PHP script
	 * and captures the display result if required.
	 * @param string $_viewFile_ view file
	 * @param array $_data_ data to be extracted and made available to the view file
	 * @param boolean $_return_ whether the rendering result should be returned as a string
	 * @return string the rendering result. Null if the rendering result is not required.
	 */
	function renderViewFile($_viewFile_, $_data_=null, $_return_=false)
	{
		// we use special variable names here to avoid conflict when extracting data
		if (is_array($_data_)) {
			extract($_data_, EXTR_PREFIX_SAME, 'data');
		} else {
			$data = $_data_;
		}
		if ($_return_) {
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		} else {
			require($_viewFile_);
		}
	}

	/**
	 * Normalizes requirement ensuring it has correct format.
	 * @param array $requirement raw requirement.
	 * @param int $requirementKey requirement key in the list.
	 * @return array normalized requirement.
	 */
	function normalizeRequirement($requirement, $requirementKey=0)
	{
		if (!is_array($requirement)) {
			$this->usageError('Requirement must be an array!');
		}
		if (!array_key_exists('condition', $requirement)) {
			$this->usageError("Requirement '{$requirementKey}' has no condition!");
		} else {
			$evalPrefix = 'eval:';
			if (is_string($requirement['condition']) && strpos($requirement['condition'], $evalPrefix)===0) {
				$expression = substr($requirement['condition'], strlen($evalPrefix));
				$requirement['condition'] = $this->evaluateExpression($expression);
			}
		}
		if (!array_key_exists('name', $requirement)) {
			$requirement['name'] = is_numeric($requirementKey) ? 'Requirement #'.$requirementKey : $requirementKey;
		}
		if (!array_key_exists('mandatory', $requirement)) {
			if (array_key_exists('required', $requirement)) {
				$requirement['mandatory'] = $requirement['required'];
			} else {
				$requirement['mandatory'] = false;
			}
		}
		if (!array_key_exists('by', $requirement)) {
			$requirement['by'] = 'Unknown';
		}
		if (!array_key_exists('memo', $requirement)) {
			$requirement['memo'] = '';
		}
		return $requirement;
	}

	/**
	 * Displays a usage error.
	 * This method will then terminate the execution of the current application.
	 * @param string $message the error message
	 */
	function usageError($message)
	{
		echo "Error: $message\n\n";
		exit(1);
	}

	/**
	 * Evaluates a PHP expression under the context of this class.
	 * @param string $expression a PHP expression to be evaluated.
	 * @return mixed the expression result.
	 */
	function evaluateExpression($expression)
	{
		return eval('return '.$expression.';');
	}

	/**
	 * Returns the server information.
	 * @return string server information.
	 */
	function getServerInfo()
	{
		$info = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
		return $info;
	}

	/**
	 * Returns the now date if possible in string representation.
	 * @return string now date.
	 */
	function getNowDate()
	{
		$nowDate = @strftime('%Y-%m-%d %H:%M', time());
		return $nowDate;
	}
}
