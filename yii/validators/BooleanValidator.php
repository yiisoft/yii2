<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Html;

/**
 * BooleanValidator checks if the attribute value is a boolean value.
 *
 * Possible boolean values can be configured via the [[trueValue]] and [[falseValue]] properties.
 * And the comparison can be either [[strict]] or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BooleanValidator extends Validator
{
	/**
	 * @var mixed the value representing true status. Defaults to '1'.
	 */
	public $trueValue = '1';
	/**
	 * @var mixed the value representing false status. Defaults to '0'.
	 */
	public $falseValue = '0';
	/**
	 * @var boolean whether the comparison to [[trueValue]] and [[falseValue]] is strict.
	 * When this is true, the attribute value and type must both match those of [[trueValue]] or [[falseValue]].
	 * Defaults to false, meaning only the value needs to be matched.
	 */
	public $strict = false;

	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} must be either "{true}" or "{false}".');
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
		if (!$this->validateValue($value)) {
			$this->addError($object, $attribute, $this->message, array(
				'{true}' => $this->trueValue,
				'{false}' => $this->falseValue,
			));
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		return !$this->strict && ($value == $this->trueValue || $value == $this->falseValue)
			|| $this->strict && ($value === $this->trueValue || $value === $this->falseValue);
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @param \yii\base\View $view the view object that is going to be used to render views or view files
	 * containing a model form with this validator applied.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$options = array(
			'trueValue' => $this->trueValue,
			'falseValue' => $this->falseValue,
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
				'{value}' => $object->$attribute,
				'{true}' => $this->trueValue,
				'{false}' => $this->falseValue,
			))),
		);
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}
		if ($this->strict) {
			$options['strict'] = 1;
		}

		$view->registerAssetBundle('yii/form');
		return 'yii.validation.boolean(value, messages, ' . json_encode($options) . ');';
	}
}
