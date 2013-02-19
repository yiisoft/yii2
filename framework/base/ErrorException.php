<?php
/**
 * ErrorException class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ErrorException represents a PHP error.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class ErrorException extends Exception
{
	protected $severity;

	/**
	 * Constructs the exception
	 * @link http://php.net/manual/en/errorexception.construct.php
	 * @param $message [optional]
	 * @param $code [optional]
	 * @param $severity [optional]
	 * @param $filename [optional]
	 * @param $lineno [optional]
	 * @param $previous [optional]
	 */
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->severity = $severity;
		$this->file = $filename;
		$this->line = $lineno;
	}

	/**
	 * Gets the exception severity
	 * @link http://php.net/manual/en/errorexception.getseverity.php
	 * @return int the severity level of the exception.
	 */
	final public function getSeverity()
	{
		return $this->severity;
	}

	public static function getFatalCodes()
	{
		return array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		$names = array(
			E_ERROR => \Yii::t('yii|Fatal Error'),
			E_PARSE => \Yii::t('yii|Parse Error'),
			E_CORE_ERROR => \Yii::t('yii|Core Error'),
			E_COMPILE_ERROR => \Yii::t('yii|Compile Error'),
			E_USER_ERROR => \Yii::t('yii|User Error'),
			E_WARNING => \Yii::t('yii|Warning'),
			E_CORE_WARNING => \Yii::t('yii|Core Warning'),
			E_COMPILE_WARNING => \Yii::t('yii|Compile Warning'),
			E_USER_WARNING => \Yii::t('yii|User Warning'),
			E_STRICT => \Yii::t('yii|Strict'),
			E_NOTICE => \Yii::t('yii|Notice'),
			E_RECOVERABLE_ERROR => \Yii::t('yii|Recoverable Error'),
			E_DEPRECATED => \Yii::t('yii|Deprecated'),
		);
		return isset($names[$this->getCode()]) ? $names[$this->getCode()] : \Yii::t('yii|Error');
	}
}
