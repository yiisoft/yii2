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
class Exact extends Base
{

	/**
	 * @var boolean if current matcher should consider partial match of given value.
	 */
	public $partial = false;

	/**
	 * Checks if the given value is the same as base one or has partial match with base one.
	 * @param mixed $value
	 */
	public function check($value)
	{
		if (!$this->partial) {
			return (mb_strtolower($this->value, 'utf8') == mb_strtolower($value, 'utf8'));
		} else {
			return (mb_strpos(mb_strtolower($value, 'utf8'), mb_strtolower($this->value,'utf8')) !== false);
		}
	}

}
