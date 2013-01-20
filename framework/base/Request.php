<?php
/**
 * Request class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends Component
{
	private $_scriptFile;
	private $_isConsoleRequest;

	/**
	 * Returns a value indicating whether the current request is made via command line
	 * @return boolean the value indicating whether the current request is made via console
	 */
	public function getIsConsoleRequest()
	{
		return $this->_isConsoleRequest !== null ? $this->_isConsoleRequest : PHP_SAPI === 'cli';
	}

	/**
	 * Sets the value indicating whether the current request is made via command line
	 * @param boolean $value the value indicating whether the current request is made via command line
	 */
	public function setIsConsoleRequest($value)
	{
		$this->_isConsoleRequest = $value;
	}

	/**
	 * Returns entry script file path.
	 * @return string entry script file path (processed w/ realpath())
	 */
	public function getScriptFile()
	{
		if ($this->_scriptFile === null) {
			$this->_scriptFile = realpath($_SERVER['SCRIPT_FILENAME']);
		}
		return $this->_scriptFile;
	}

	/**
	 * Sets the entry script file path.
	 * This can be an absolute or relative file path, or a path alias.
	 * Note that you normally do not have to set the script file path
	 * as [[getScriptFile()]] can determine it based on `$_SERVER['SCRIPT_FILENAME']`.
	 * @param string $value the entry script file
	 */
	public function setScriptFile($value)
	{
		$this->_scriptFile = realpath(\Yii::getAlias($value));
	}
}
