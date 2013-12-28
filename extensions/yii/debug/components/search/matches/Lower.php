<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matches;

/**
 * 
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Lower extends Base
{

	/**
	 * Checks if the given value is the same as base one or has partial match with base one.
	 * @param mixed $value
	 */
	public function check($value)
	{
		return ($value < $this->value);
	}

}
