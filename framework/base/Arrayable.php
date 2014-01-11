<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Arrayable should be implemented by classes that need to be represented in array format.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Arrayable
{
	/**
	 * Converts the object into an array.
	 * @return array the array representation of this object
	 */
	public function toArray();
}
