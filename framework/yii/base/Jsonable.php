<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Jsonable should be implemented by classes that need to be represented in JSON format.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Jsonable
{
	/**
	 * @return string the JSON representation of this object
	 */
	public function toJson();
}
