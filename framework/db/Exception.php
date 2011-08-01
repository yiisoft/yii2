<?php
/**
 * CDbException class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbException represents an exception that is caused by some DB-related operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbException.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.db
 * @since 1.0
 */
class CDbException extends CException
{
	/**
	 * @var mixed the error info provided by a PDO exception. This is the same as returned
	 * by {@link http://www.php.net/manual/en/pdo.errorinfo.php PDO::errorInfo}.
	 * @since 1.1.4
	 */
	public $errorInfo;

	/**
	 * Constructor.
	 * @param string $message PDO error message
	 * @param integer $code PDO error code
	 * @param mixed $errorInfo PDO error info
	 */
	public function __construct($message, $code = 0, $errorInfo = null)
	{
		$this->errorInfo = $errorInfo;
		parent::__construct($message, $code);
	}
}