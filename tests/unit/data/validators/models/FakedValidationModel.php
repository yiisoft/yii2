<?php

namespace yiiunit\data\validators\models;

use yii\base\Model;

class FakedValidationModel extends Model
{
	public $val_attr_a;
	public $val_attr_b;
	public $val_attr_c;
	public $val_attr_d;
	private $attr = [];

	/**
	 * @param array $attributes
	 * @return self
	 */
	public static function createWithAttributes($attributes = [])
	{
		$m = new static();
		foreach ($attributes as $attribute => $value) {
			$m->$attribute = $value;
		}
		return $m;
	}

	public function rules()
	{
		return [
			[['val_attr_a', 'val_attr_b'], 'required', 'on' => 'reqTest'],
			['val_attr_c', 'integer'],
            ['attr_images', 'file', 'maxFiles' => 3, 'types' => ['png'], 'on' => 'validateMultipleFiles'],
            ['attr_image', 'file', 'types' => ['png'], 'on' => 'validateFile']
		];
	}

	public function inlineVal($attribute, $params = [])
	{
		return true;
	}

	public function __get($name)
	{
		if (stripos($name, 'attr') === 0) {
			return isset($this->attr[$name]) ? $this->attr[$name] : null;
		}

		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		if (stripos($name, 'attr') === 0) {
			$this->attr[$name] = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function getAttributeLabel($attr)
	{
		return $attr;
	}
}
