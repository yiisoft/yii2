<?php
/**
 * @author Mushkin V.
 */

namespace yiiunit\framework\web;

use yiiunit\TestCase;
use yii\web\JsExpression;

/**
 * @group web
 */
class JsExpressionTest extends TestCase
{    
    public function test__toString()
    {
        $connObj = new JsExpression('Test1');
        $this->assertEquals('Test1',$connObj->__toString());
    }
}
