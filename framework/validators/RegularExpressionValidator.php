<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
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
	 **/
	public $not = false;

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	protected function validateValue($value)
	{
		$valid = !is_array($value) &&
			(!$this->not && preg_match($this->pattern, $value)
			|| $this->not && !preg_match($this->pattern, $value));
		return $valid ? null : [$this->message, []];
	}

	/**
	 * @inheritdoc
	 */
	public function clientValidateAttribute($object, $attribute, $view)
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

		$options = [
			'pattern' => new JsExpression($pattern),
			'not' => $this->not,
			'message' => Yii::$app->getI18n()->format($this->message, [
				'attribute' => $object->getAttributeLabel($attribute),
			], Yii::$app->language),
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		ValidationAsset::register($view);
		return 'yii.validation.regularExpression(value, messages, ' . Json::encode($options) . ');';
	}
}
