<?php
namespace yiiunit\framework\validators;

use yiiunit\data\validators\models\FakedValidationModel;
use yii\validators\BooleanValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class BooleanValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateValue()
    {
        $val = new BooleanValidator;
        $this->assertTrue($val->validate(true));
        $this->assertTrue($val->validate(false));
        $this->assertTrue($val->validate('0'));
        $this->assertTrue($val->validate('1'));
        $this->assertFalse($val->validate(null));
        $this->assertFalse($val->validate([]));
        $val->strict = true;
        $this->assertTrue($val->validate('0'));
        $this->assertTrue($val->validate('1'));
        $this->assertFalse($val->validate(true));
        $this->assertFalse($val->validate(false));
        $val->trueValue = true;
        $val->falseValue = false;
        $this->assertFalse($val->validate('0'));
        $this->assertFalse($val->validate([]));
        $this->assertTrue($val->validate(true));
        $this->assertTrue($val->validate(false));
    }

    public function testValidateAttributeAndError()
    {
        $obj = new FakedValidationModel;
        $obj->attrA = true;
        $obj->attrB = '1';
        $obj->attrC = '0';
        $obj->attrD = [];
        $val = new BooleanValidator;
        $val->validateAttribute($obj, 'attrA');
        $this->assertFalse($obj->hasErrors('attrA'));
        $val->validateAttribute($obj, 'attrC');
        $this->assertFalse($obj->hasErrors('attrC'));
        $val->strict = true;
        $val->validateAttribute($obj, 'attrB');
        $this->assertFalse($obj->hasErrors('attrB'));
        $val->validateAttribute($obj, 'attrD');
        $this->assertTrue($obj->hasErrors('attrD'));
    }
}
