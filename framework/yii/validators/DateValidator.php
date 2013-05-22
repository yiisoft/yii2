<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use DateTime;

/**
 * DateValidator verifies if the attribute represents a date, time or datetime in a proper format.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DateValidator extends Validator
{
	/**
	 * @var string the date format that the value being validated should follow.
	 * Please refer to [[http://www.php.net/manual/en/datetime.createfromformat.php]] on
	 * supported formats.
	 */
	public $format = 'Y-m-d';
	/**
	 * @var string the name of the attribute to receive the parsing result.
	 * When this property is not null and the validation is successful, the named attribute will
	 * receive the parsing result.
	 */
	public $timestampAttribute;

	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', 'The format of {attribute} is invalid.');
		}
	}

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if (is_array($value)) {
			$this->addError($object, $attribute, $this->message);
			return;
		}
		$date = DateTime::createFromFormat($this->format, $value);
		if ($date === false) {
			$this->addError($object, $attribute, $this->message);
		} elseif ($this->timestampAttribute !== false) {
			$object->{$this->timestampAttribute} = $date->getTimestamp();
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		return DateTime::createFromFormat($this->format, $value) !== false;
	}
}

