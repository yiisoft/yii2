<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

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
	 * Constructs the exception.
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

		if (function_exists('xdebug_get_function_stack')) {
			$trace = array_slice(array_reverse(xdebug_get_function_stack()), 3, -1);
			foreach ($trace as &$frame) {
				if (!isset($frame['function'])) {
					$frame['function'] = 'unknown';
				}

				// XDebug < 2.1.1: http://bugs.xdebug.org/view.php?id=695
				if (!isset($frame['type']) || $frame['type'] === 'static') {
					$frame['type'] = '::';
				} elseif ($frame['type'] === 'dynamic') {
					$frame['type'] = '->';
				}

				// XDebug has a different key name
				if (isset($frame['params']) && !isset($frame['args'])) {
					$frame['args'] = $frame['params'];
				}
			}

			$ref = new \ReflectionProperty('Exception', 'trace');
			$ref->setAccessible(true);
			$ref->setValue($this, $trace);
		}
	}

	/**
	 * Returns if error is one of fatal type.
	 *
	 * @param array $error error got from error_get_last()
	 * @return bool if error is one of fatal type
	 */
	public static function isFatalError($error)
	{
		return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
	}

	/**
	 * Gets the exception severity.
	 * @link http://php.net/manual/en/errorexception.getseverity.php
	 * @return int the severity level of the exception.
	 */
	final public function getSeverity()
	{
		return $this->severity;
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		$names = [
			E_ERROR => 'PHP Fatal Error',
			E_PARSE => 'PHP Parse Error',
			E_CORE_ERROR => 'PHP Core Error',
			E_COMPILE_ERROR => 'PHP Compile Error',
			E_USER_ERROR => 'PHP User Error',
			E_WARNING => 'PHP Warning',
			E_CORE_WARNING => 'PHP Core Warning',
			E_COMPILE_WARNING => 'PHP Compile Warning',
			E_USER_WARNING => 'PHP User Warning',
			E_STRICT => 'PHP Strict Warning',
			E_NOTICE => 'PHP Notice',
			E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
			E_DEPRECATED => 'PHP Deprecated Warning',
		];
		return isset($names[$this->getCode()]) ? $names[$this->getCode()] : 'Error';
	}
}
