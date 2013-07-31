<?php

namespace yiiunit\framework\validators;

use yii\base\Model;

/**
 * @codeCoverageIgnore
 */
class FakedValidationModel extends Model
{
	private $attr = array();

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
}