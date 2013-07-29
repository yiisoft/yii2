<?php

namespace yiiunit\framework\validators;

/**
 * @codeCoverageIgnore
 */
class FakedValidationModel
{
	public $errors = array();
	public function getAttributeLabel($attr)
	{
		return 'Attr-Label: '.$attr;
	}
	
	public function addError($attribute, $message)
	{
		$this->errors[$attribute] = $message;
	}
}