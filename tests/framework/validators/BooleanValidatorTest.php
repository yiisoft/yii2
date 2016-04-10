<?php
namespace yiiunit\framework\validators;

use yii\base\View;
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
        $this->assertFalse($val->validate('5'));
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

    public function testErrorMessage()
    {
        $validator = new BooleanValidator([
            'trueValue' => true,
            'falseValue' => false,
            'strict' => true,
        ]);
        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertEquals('the input value must be either "true" or "false".', $errorMessage);

        $obj = new FakedValidationModel;
        $obj->attrA = true;
        $obj->attrB = '1';
        $obj->attrC = '0';
        $obj->attrD = [];

        $this->assertEquals(
            'yii.validation.boolean(value, messages, {"trueValue":true,"falseValue":false,"message":"attrB must be either \"true\" or \"false\".","skipOnEmpty":1,"strict":1});',
            $validator->clientValidateAttribute($obj, 'attrB', new ViewStub)
        );
    }
}

class ViewStub extends \yii\web\View
{
    public function registerAssetBundle($name, $position = null)
    {
        return;
    }
}
