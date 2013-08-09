<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Exception represents a generic exception for all purposes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \Exception implements Arrayable
{
	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return \Yii::t('yii', 'Exception');
	}

	/**
	 * Returns the array representation of this object.
	 * @return array the array representation of this object.
	 */
	public function toArray()
	{
		return $this->toArrayRecursive($this);
	}

	/**
	 * Returns the array representation of the exception and all previous exceptions recursively.
	 * @param \Exception exception object
	 * @return array the array representation of the exception.
	 */
	protected function toArrayRecursive($exception)
	{
		if ($exception instanceof self) {
			$array = array(
				'type' => get_class($this),
				'name' => $this->getName(),
				'message' => $this->getMessage(),
				'code' => $this->getCode(),
			);
		} else {
			$array = array(
				'type' => get_class($exception),
				'name' => 'Exception',
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
			);
		}
		if (($prev = $exception->getPrevious()) !== null) {
			$array['previous'] = $this->toArrayRecursive($prev);
		}
		return $array;
	}
}
