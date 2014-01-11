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
	 * Please refer to <http://www.php.net/manual/en/datetime.createfromformat.php> on
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
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', 'The format of {attribute} is invalid.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		$result = $this->validateValue($value);
		if (!empty($result)) {
			$this->addError($object, $attribute, $result[0], $result[1]);
		} elseif ($this->timestampAttribute !== null) {
			$date = DateTime::createFromFormat($this->format, $value);
			$object->{$this->timestampAttribute} = $date->getTimestamp();
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value)
	{
		if (is_array($value)) {
			return [$this->message, []];
		}
		$date = DateTime::createFromFormat($this->format, $value);
		$errors = DateTime::getLastErrors();
		$invalid = $date === false || $errors['error_count'] || $errors['warning_count'];
		return $invalid ? [$this->message, []] : null;
	}
}
