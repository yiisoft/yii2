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
		$array = array(
			'type' => get_class($this),
			'name' => $this->getName(),
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
		);
		if (($prev = $this->getPrevious()) !== null) {
			if ($prev instanceof self) {
				$array['previous'] = $prev->toArray();
			} else {
				$array['previous'] = array(
					'type' => get_class($prev),
					'name' => 'Exception',
					'message' => $prev->getMessage(),
					'code' => $prev->getCode(),
				);
			}
		}
		return $array;
	}
}
