<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matchers;

/**
 * Checks if the given value is greater than the base one.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class GreaterThan extends Base
{
	/**
	 * @inheritdoc
	 */
	public function match($value)
	{
		return ($value > $this->baseValue);
	}
}
