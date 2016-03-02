<?php

namespace yiiunit\data\validators;

use yii\validators\Validator;

class TestValidator extends Validator
{
    private $_validatedAttributes = [];
    private $_setErrorOnValidateAttribute = false;

    public function validateAttribute($object, $attribute)
    {
        $this->markAttributeValidated($attribute);
        if ($this->_setErrorOnValidateAttribute == true) {
            $this->addError($object, $attribute, sprintf('%s##%s', $attribute, get_class($object)));
        }
    }

    protected function markAttributeValidated($attr, $increaseBy = 1)
    {
        if (!isset($this->_validatedAttributes[$attr])) {
            $this->_validatedAttributes[$attr] = 1;
        } else {
            $this->_validatedAttributes[$attr] = $this->_validatedAttributes[$attr] + $increaseBy;
        }
    }

    public function countAttributeValidations($attr)
    {
        return isset($this->_validatedAttributes[$attr]) ? $this->_validatedAttributes[$attr] : 0;
    }

    public function isAttributeValidated($attr)
    {
        return isset($this->_validatedAttributes[$attr]);
    }

    public function enableErrorOnValidateAttribute()
    {
        $this->_setErrorOnValidateAttribute = true;
    }
}
