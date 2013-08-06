<?php

namespace yiiunit\data\validators\models;

use yii\base\Model;

/**
 * @codeCoverageIgnore
 */
class FakedValidationModel extends Model
{
	private $attr = array();

	public $val_attr_a;
	public $val_attr_b;
	public $val_attr_c;
	public $val_attr_d;


	public function rules()
	{
		return array(
			array('val_attr_a, val_attr_b', 'required', 'on' => 'reqTest'),
			array('val_attr_c', 'integer'),
		);
	}

	public function inlineVal($attribute, $params = array())
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