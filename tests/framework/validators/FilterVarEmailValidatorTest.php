<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;


use validators\FilterVarEmailValidator;
use yii\validators\EmailValidator;

/**
 * @group validators
 */
class FilterVarEmailValidatorTest extends EmailValidatorTest
{
    protected function getEmailValidator(): EmailValidator
    {
        return new FilterVarEmailValidator();
    }

}
