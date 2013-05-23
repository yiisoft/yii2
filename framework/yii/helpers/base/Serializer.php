<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

/**
 * Serializer provides a set of methods to serialize and unserialize objects and variables.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class Serializer
{
	/**
	 * @var boolean whether `igbinary` PHP extension should be used. `igbinary` offers serialization
	 * into a binary data and known as much faster alternative of the standard [[serialize()]] and
	 * [[unserialize()]] functions.
	 */
	public static $useIgbinary = false;

	/**
	 * Serialize given value
	 * @param mixed $value to be serialized.
	 * @return mixed serialized variant of the value.
	 */
	public static function serialize($value)
	{
		if (static::$useIgbinary) {
			return igbinary_serialize($value);
		} else {
			return serialize($value);
		}
	}

	/**
	 * Deserialize given value.
	 * @param mixed $value to be deserialized.
	 * @return mixed deserialized/original variant of the value.
	 */
	public static function unserialize($value)
	{
		if (static::$useIgbinary) {
			return igbinary_unserialize($value);
		} else {
			return unserialize($value);
		}
	}
}
