<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matches;

/**
 * MatcherInterface is the interface that should be implemented by all matchers that will be used in filter.
 * 
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
interface MatcherInterface
{

	/**
	 * Check if the value is correct according current matcher.
	 * @param mixed $value
	 */
	public function check($value);

}
