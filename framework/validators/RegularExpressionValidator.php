<?php
/**
 * RegularExpressionValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

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
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the regular expression defined via [[pattern]] should NOT match the attribute value.
	 **/
 	public $not = false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws \yii\base\Exception if the "pattern" is not a valid regular expression
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}
		if ($this->pattern === null) {
			throw new \yii\base\Exception('The "pattern" property must be specified with a valid regular expression.');
		}
		if ((!$this->not && !preg_match($this->pattern, $value)) || ($this->not && preg_match($this->pattern, $value))) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} is invalid.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @throws \yii\base\Exception if the "pattern" is not a valid regular expression
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if ($this->pattern === null) {
			throw new \yii\base\Exception('The "pattern" property must be specified with a valid regular expression.');
		}

		$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} is invalid.');
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		));

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

		return "
if (" . ($this->allowEmpty ? "$.trim(value)!='' && " : '') . ($this->not ? '' : '!') . "value.match($pattern)) {
	messages.push(" . json_encode($message) . ");
}
";
	}
}
