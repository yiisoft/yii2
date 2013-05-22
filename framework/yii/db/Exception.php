<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Exception represents an exception that is caused by some DB-related operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \yii\base\Exception
{
	/**
	 * @var mixed the error info provided by a PDO exception. This is the same as returned
	 * by [PDO::errorInfo](http://www.php.net/manual/en/pdo.errorinfo.php).
	 */
	public $errorInfo;

	/**
	 * Constructor.
	 * @param string $message PDO error message
	 * @param mixed $errorInfo PDO error info
	 * @param integer $code PDO error code
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message, $errorInfo = null, $code = 0, \Exception $previous = null)
	{
		$this->errorInfo = $errorInfo;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return \Yii::t('yii', 'Database Exception');
	}
}
