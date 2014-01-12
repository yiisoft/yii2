<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * RangeValidator validates that the attribute value is among a list of values.
 *
 * The range can be specified via the [[range]] property.
 * If the [[not]] property is set true, the validator will ensure the attribute value
 * is NOT among the specified range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RangeValidator extends Validator
{
	/**
	 * @var array list of valid values that the attribute value should be among
	 */
	public $range;
	/**
	 * @var boolean whether the comparison is strict (both type and value must be the same)
	 */
	public $strict = false;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via [[range]].
	 **/
	public $not = false;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (!is_array($this->range)) {
			throw new InvalidConfigException('The "range" property must be set.');
		}
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is invalid.');
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value)
	{
		$valid = !$this->not && in_array($value, $this->range, $this->strict)
			|| $this->not && !in_array($value, $this->range, $this->strict);
		return $valid ? null : [$this->message, []];
	}

	/**
	 * @inheritdoc
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$range = [];
		foreach ($this->range as $value) {
			$range[] = (string)$value;
		}
		$options = [
			'range' => $range,
			'not' => $this->not,
			'message' => Yii::$app->getI18n()->format($this->message, [
				'attribute' => $object->getAttributeLabel($attribute),
			], Yii::$app->language),
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		ValidationAsset::register($view);
		return 'yii.validation.range(value, messages, ' . json_encode($options) . ');';
	}
}
