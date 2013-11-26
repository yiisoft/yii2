<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

/**
 * Exception represents an exception that is caused by elasticsearch-related operations.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Exception extends \yii\db\Exception
{
	/**
	 * @var array additional information about the http request that caused the error.
	 */
	public $errorInfo = [];

	/**
	 * Constructor.
	 * @param string $message error message
	 * @param array $errorInfo error info
	 * @param integer $code error code
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message, $errorInfo = [], $code = 0, \Exception $previous = null)
	{
		$this->errorInfo = $errorInfo;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return \Yii::t('yii', 'Elasticsearch Database Exception');
	}
}