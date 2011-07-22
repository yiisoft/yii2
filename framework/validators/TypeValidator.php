<?php
/**
 * CTypeValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CTypeValidator verifies if the attribute is of the type specified by {@link type}.
 *
 * The following data types are supported:
 * <ul>
 * <li><b>integer</b> A 32-bit signed integer data type.</li>
 * <li><b>float</b> A double-precision floating point number data type.</li>
 * <li><b>string</b> A string data type.</li>
 * <li><b>array</b> An array value. </li>
 * <li><b>date</b> A date data type.</li>
 * <li><b>time</b> A time data type (available since version 1.0.5).</li>
 * <li><b>datetime</b> A date and time data type (available since version 1.0.5).</li>
 * </ul>
 *
 * For <b>date</b> type, the property {@link dateFormat}
 * will be used to determine how to parse the date string. If the given date
 * value doesn't follow the format, the attribute is considered as invalid.
 *
 * Starting from version 1.1.7, we have a dedicated date validator {@link CDateValidator}.
 * Please consider using this validator to validate a date-typed value.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTypeValidator.php 3052 2011-03-12 14:27:07Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CTypeValidator extends Validator
{
	/**
	 * @var string the data type that the attribute should be. Defaults to 'string'.
	 * Valid values include 'string', 'integer', 'float', 'array', 'date', 'time' and 'datetime'.
	 * Note that 'time' and 'datetime' have been available since version 1.0.5.
	 */
	public $type = 'string';
	/**
	 * @var string the format pattern that the date value should follow. Defaults to 'MM/dd/yyyy'.
	 * Please see {@link CDateTimeParser} for details about how to specify a date format.
	 * This property is effective only when {@link type} is 'date'.
	 */
	public $dateFormat = 'MM/dd/yyyy';
	/**
	 * @var string the format pattern that the time value should follow. Defaults to 'hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a time format.
	 * This property is effective only when {@link type} is 'time'.
	 * @since 1.0.5
	 */
	public $timeFormat = 'hh:mm';
	/**
	 * @var string the format pattern that the datetime value should follow. Defaults to 'MM/dd/yyyy hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a datetime format.
	 * This property is effective only when {@link type} is 'datetime'.
	 * @since 1.0.5
	 */
	public $datetimeFormat = 'MM/dd/yyyy hh:mm';
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value))
			return;

		if ($this->type === 'integer')
			$valid = preg_match('/^[-+]?[0-9]+$/', trim($value));
		elseif ($this->type === 'float')
			$valid = preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/', trim($value));
		elseif ($this->type === 'date')
			$valid = CDateTimeParser::parse($value, $this->dateFormat, array('month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0)) !== false;
	    elseif ($this->type === 'time')
			$valid = CDateTimeParser::parse($value, $this->timeFormat) !== false;
	    elseif ($this->type === 'datetime')
			$valid = CDateTimeParser::parse($value, $this->datetimeFormat, array('month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0)) !== false;
		elseif ($this->type === 'array')
			$valid = is_array($value);
		else
			return;

		if (!$valid)
		{
			$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} must be {type}.');
			$this->addError($object, $attribute, $message, array('{type}' => $this->type));
		}
	}
}

