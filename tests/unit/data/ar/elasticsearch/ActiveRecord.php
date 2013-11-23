<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar\elasticsearch;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\elasticsearch\ActiveRecord
{
	public static $db;

	/**
	 * @return \yii\elasticsearch\Connection
	 */
	public static function getDb()
	{
		return self::$db;
	}
}
