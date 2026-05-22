<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators\stubs;

use yii\validators\EmailValidator;

class MockEmailValidator extends EmailValidator
{
    protected function idnToAscii($idn)
    {
        return strlen($idn) > 64 ? false : $idn;
    }
}
