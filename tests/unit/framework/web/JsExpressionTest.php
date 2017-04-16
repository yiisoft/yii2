<?php
/**
 * @author Mushkin V.
 */

namespace yiiunit\framework\web;

use yiiunit\TestCase;
use yii\web\JsExpression;

class JsExpressionTest extends TestCase
{    
    public function test__toString()
    {
        $connObj = new JsExpression('Test11');
        $this->assertEquals('Test1',$connObj->__toString());
    }
}
