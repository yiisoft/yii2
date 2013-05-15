<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\Json;

/**
 * RegularExpressionValidator validates that the attribute value matches the specified [[pattern]].
 *
 * If the [[not]] property is set true, the validator will ensure the attribute value do NOT match the [[pattern]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RegularExpressionValidator extends Validator
{
	/**
	 * @var string the regular expression to be matched with
	 */
	public $pattern;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the regular expression defined via [[pattern]] should NOT match the attribute value.
	 * @throws InvalidConfigException if the "pattern" is not a valid regular expression
	 **/
 	public $not = false;

	/**
	 * Initializes the validator.
	 * @throws InvalidConfigException if [[pattern]] is not set.
	 */
	public function init()
	{
		parent::init();
		if ($this->pattern === null) {
			throw new InvalidConfigException('The "pattern" property must be set.');
		}
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is invalid.');
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
			$this->addError($object, $attribute, $this->message);
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		return !is_array($value) &&
			(!$this->not && preg_match($this->pattern, $value)
			|| $this->not && !preg_match($this->pattern, $value));
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @throws InvalidConfigException if the "pattern" is not a valid regular expression
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$pattern = $this->pattern;
		$pattern = preg_replace('/\\\\x\{?([0-9a-fA-F]+)\}?/', '\u$1', $pattern);
		$deliminator = substr($pattern, 0, 1);
		$pos = strrpos($pattern, $deliminator, 1);
		$flag = substr($pattern, $pos + 1);
		if ($deliminator !== '/') {
			$pattern = '/' . str_replace('/', '\\/', substr($pattern, 1, $pos - 1)) . '/';
		} else {
			$pattern = substr($pattern, 0, $pos + 1);
		}
		if (!empty($flag)) {
			$pattern .= preg_replace('/[^igm]/', '', $flag);
		}

		$options = array(
			'pattern' => new JsExpression($pattern),
			'not' => $this->not,
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
				'{value}' => $object->$attribute,
			))),
		);
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		return 'yii.validation.regularExpression(value, messages, ' . Json::encode($options) . ');';
	}
}
