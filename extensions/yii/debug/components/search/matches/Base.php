<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matches;

use yii\base\Component;

/**
 * Base mathcer class for all matchers that will be used with filter.
 * 
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
abstract class Base extends Component implements MatcherInterface
{

	/**
	 * @var mixed current value to check for the matcher
	 */
	public $value;

}
