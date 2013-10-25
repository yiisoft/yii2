<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use yii\base\InvalidConfigException;

/**
 * FilterValidator converts the attribute value according to a filter.
 *
 * FilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter callback to process the attribute value
 * and save the processed value back to the attribute. The filter must be
 * a valid PHP callback with the following signature:
 *
 * ~~~
 * function foo($value) {...return $newValue; }
 * ~~~
 *
 * Many PHP functions qualify this signature (e.g. `trim()`).
 *
 * To specify the filter, set [[filter]] property to be the callback.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FilterValidator extends Validator
{
	/**
	 * @var callback the filter. This can be a global function name, anonymous function, etc.
	 * The function signature must be as follows,
	 *
	 * ~~~
	 * function foo($value) {...return $newValue; }
	 * ~~~
	 */
	public $filter;
	/**
	 * @var boolean this property is overwritten to be false so that this validator will
	 * be applied when the value being validated is empty.
	 */
	public $skipOnEmpty = false;

	/**
	 * Initializes the validator.
	 * @throws InvalidConfigException if [[filter]] is not set.
	 */
	public function init()
	{
		parent::init();
		if ($this->filter === null) {
			throw new InvalidConfigException('The "filter" property must be set.');
		}
	}

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws InvalidConfigException if filter property is not a valid callback
	 */
	public function validateAttribute($object, $attribute)
	{
		$object->$attribute = call_user_func($this->filter, $object->$attribute);
	}
}
