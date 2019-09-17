<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\DefaultValueValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class DefaultValueValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateAttribute()
    {
        $val = new DefaultValueValidator();
        $val->value = 'test_value';
        $obj = new \stdclass();
        $obj->attrA = 'attrA';
        $obj->attrB = null;
        $obj->attrC = '';
        // original values to chek which attritubes where modified
        $objB = clone $obj;
        $val->validateAttribute($obj, 'attrB');
        $this->assertEquals($val->value, $obj->attrB);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->value = 'new_test_value';
        $obj = clone $objB; // get clean object
        $val->validateAttribute($obj, 'attrC');
        $this->assertEquals('new_test_value', $obj->attrC);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->validateAttribute($obj, 'attrA');
        $this->assertEquals($objB->attrA, $obj->attrA);
    }
    
    public function testValidateAttributeClosure()
    {
        $val = new DefaultValueValidator();
        $val->value = function($model, $attribute) {
            return 'test_value';
        };
        $obj = new \stdclass();
        $obj->attrA = 'attrA';
        $obj->attrB = null;
        $obj->attrC = '';
        // original values to chek which attritubes where modified
        $objB = clone $obj;
        $val->validateAttribute($obj, 'attrB');
        $this->assertEquals('test_value', $obj->attrB);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->value = function($model, $attribute) {
            return 'new_test_value';
        };
        $obj = clone $objB; // get clean object
        $val->validateAttribute($obj, 'attrC');
        $this->assertEquals('new_test_value', $obj->attrC);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->validateAttribute($obj, 'attrA');
        $this->assertEquals($objB->attrA, $obj->attrA);
    }
    
    public function testValidateAttributeCallable()
    {
        $val = new DefaultValueValidator();
        $val->value = [__CLASS__, 'callableValue'];
        $obj = new \stdclass();
        $obj->attrA = 'attrA';
        $obj->attrB = null;
        $obj->attrC = '';
        // original values to chek which attritubes where modified
        $objB = clone $obj;
        $val->validateAttribute($obj, 'attrB');
        $this->assertEquals('test_value', $obj->attrB);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->value = [__CLASS__, 'newCallableValue'];
        $obj = clone $objB; // get clean object
        $val->validateAttribute($obj, 'attrC');
        $this->assertEquals('new_test_value', $obj->attrC);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->validateAttribute($obj, 'attrA');
        $this->assertEquals($objB->attrA, $obj->attrA);
    }
    
    public static function callableValue($model, $attribute)
    {
        return 'test_value';
    }
    
    public static function newCallableValue($model, $attribute)
    {
        return 'new_test_value';
    }
    
}
