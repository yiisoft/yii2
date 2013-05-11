<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if (version_compare(PHP_VERSION, '4.3', '<')) {
	echo 'At least PHP 4.3 is required to run this script!';
	exit(1);
}

/**
 * YiiRequirementChecker allows checking, if current system meets the requirements for running the Yii application.
 * This class allows rendering of the check report for the web and console application interface.
 *
 * Example:
 * <code>
 * require_once('path/to/YiiRequirementChecker.php');
 * $requirementsChecker = YiiRequirementChecker();
 * $requirements = array(
 *     array(
 *         'name' => 'PHP Some Extension',
 *         'mandatory' => true,
 *         'condition' => extension_loaded('some_extension'),
 *         'by' => 'Some application feature',
 *         'memo' => 'PHP extension "some_extension" required',
 *     ),
 * );
 * $requirementsChecker->checkYii()->check($requirements)->render();
 * <code>
 *
 * If you wish to render the report with your own representation, use [[getResult()]] instead of [[render()]]
 *
 * Requirement condition could be in format "eval:PHP expression".
 * In this case specified PHP expression will be evaluated in the context of this class instance.
 * For example:
 * <code>
 * $requirements = array(
 *     array(
 *         'name' => 'Upload max file size',
 *         'condition' => 'eval:$this->checkUploadMaxFileSize("5M")',
 *     ),
 * );
 * </code>
 *
 * @property array|null $result the check results, this property is for internal usage only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YiiRequirementChecker
{
	/**
	 * Check the given requirements, collecting results into internal field.
	 * This method can be invoked several times checking different requirement sets.
	 * Use [[getResult()]] or [[render()]] to get the results.
	 * @param array|string $requirements requirements to be checked.
	 * If an array, it is treated as the set of requirements;
	 * If a string, it is treated as the path of the file, which contains the requirements;
	 * @return YiiRequirementChecker self instance.
	 */
	function check($requirements)
	{
		if (is_string($requirements)) {
			$requirements = require($requirements);
		}
		if (!is_array($requirements)) {
			$this->usageError('Requirements must be an array, "'.gettype($requirements).'" has been given!');
		}
		if (!isset($this->result) || !is_array($this->result)) {
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
	 * Performs the check for the Yii core requirements.
	 * @return YiiRequirementChecker self instance.
	 */
	public function checkYii()
	{
		return $this->check(dirname(__FILE__).DIRECTORY_SEPARATOR.'yiirequirements.php');
	}

	/**
	 * Return the check results.
	 * @return array|null check results in format:
	 * <code>
	 * array(
	 *     'summary' => array(
	 *         'total' => total number of checks,
	 *         'errors' => number of errors,
	 *         'warnings' => number of warnings,
	 *     ),
	 *     'requirements' => array(
	 *         array(
	 *             ...
	 *             'error' => is there an error,
	 *             'warning' => is there a warning,
	 *         ),
	 *         ...
	 *     ),
	 * )
	 * </code>
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
	 * Checks if the given PHP extension is available and its version matches the given one.
	 * @param string $extensionName PHP extension name.
	 * @param string $version required PHP extension version.
	 * @param string $compare comparison operator, by default '>='
	 * @return boolean if PHP extension version matches.
	 */
	function checkPhpExtensionVersion($extensionName, $version, $compare='>=')
	{
		if (!extension_loaded($extensionName)) {
			return false;
		}
		$extensionVersion = phpversion($extensionName);
		if (empty($extensionVersion)) {
			return false;
		}
		return version_compare($extensionVersion, $version, $compare);
	}

	/**
	 * Checks if PHP configuration option (from php.ini) is on.
	 * @param string $name configuration option name.
	 * @return boolean option is on.
	 */
	function checkPhpIniOn($name)
	{
		$value = ini_get($name);
		if (empty($value)) {
			return false;
		}
		return ((integer)$value==1 || strtolower($value) == 'on');
	}

	/**
	 * Checks if PHP configuration option (from php.ini) is off.
	 * @param string $name configuration option name.
	 * @return boolean option is off.
	 */
	function checkPhpIniOff($name)
	{
		$value = ini_get($name);
		if (empty($value)) {
			return true;
		}
		return (strtolower($value) == 'off');
	}

	/**
	 * Compare byte sizes of values given in the verbose representation,
	 * like '5M', '15K' etc.
	 * @param string $a first value.
	 * @param string $b second value.
	 * @param string $compare comparison operator, by default '>='.
	 * @return boolean comparison result.
	 */
	function compareByteSize($a, $b, $compare='>=')
	{
		$compareExpression = '('.$this->getByteSize($a).$compare.$this->getByteSize($b).')';
		return $this->evaluateExpression($compareExpression);
	}

	/**
	 * Gets the size in bytes from verbose size representation.
	 * For example: '5K' => 5*1024
	 * @param string $verboseSize verbose size representation.
	 * @return integer actual size in bytes.
	 */
	function getByteSize($verboseSize)
	{
		if (empty($verboseSize)) {
			return 0;
		}
		if (is_numeric($verboseSize)) {
			return (integer)$verboseSize;
		}
		$sizeUnit = trim($verboseSize, '0123456789');
		$size = str_replace($sizeUnit, '', $verboseSize);
		$size = trim($size);
		if (!is_numeric($size)) {
			return 0;
		}
		switch (strtolower($sizeUnit)) {
			case 'kb':
			case 'k': {
				return $size*1024;
			}
			case 'mb':
			case 'm': {
				return $size*1024*1024;
			}
			case 'gb':
			case 'g': {
				return $size*1024*1024*1024;
			}
			default: {
				return 0;
			}
		}
	}

	/**
	 * Checks if upload max file size matches the given range.
	 * @param string|null $min verbose file size minimum required value, pass null to skip minimum check.
	 * @param string|null $max verbose file size maximum required value, pass null to skip maximum check.
	 * @return boolean success.
	 */
	function checkUploadMaxFileSize($min=null, $max=null)
	{
		$postMaxSize = ini_get('post_max_size');
		$uploadMaxFileSize = ini_get('upload_max_filesize');
		if ($min!==null) {
			$minCheckResult = $this->compareByteSize($postMaxSize, $min, '>=') && $this->compareByteSize($uploadMaxFileSize, $min, '>=');
		} else {
			$minCheckResult = true;
		}
		if ($max!==null) {
			var_dump($postMaxSize, $uploadMaxFileSize, $max);
			$maxCheckResult = $this->compareByteSize($postMaxSize, $max, '<=') && $this->compareByteSize($uploadMaxFileSize, $max, '<=');
		} else {
			$maxCheckResult = true;
		}
		return ($minCheckResult && $maxCheckResult);
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
