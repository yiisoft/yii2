<?php
/**
 * CFilterValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CFilterValidator transforms the data being validated based on a filter.
 *
 * CFilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter method to process the attribute value
 * and save the processed value back to the attribute. The filter method
 * must follow the following signature:
 * <pre>
 * function foo($value) {...return $newValue; }
 * </pre>
 * Many PHP functions qualify this signature (e.g. trim).
 *
 * To specify the filter method, set {@link filter} property to be the function name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CFilterValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CFilterValidator extends CValidator
{
	/**
	 * @var callback the filter method
	 */
	public $filter;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
		if ($this->filter === null || !is_callable($this->filter))
			throw new CException(Yii::t('yii', 'The "filter" property must be specified with a valid callback.'));
		$object->$attribute = call_user_func_array($this->filter, array($object->$attribute));
	}
}
