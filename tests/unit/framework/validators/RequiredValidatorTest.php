<?php
namespace yiiunit\framework\validators;

use yii\validators\RequiredValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class RequiredValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateValueWithDefaults()
    {
        $val = new RequiredValidator();
        $this->assertFalse($val->validate(null));
        $this->assertFalse($val->validate([]));
        $this->assertTrue($val->validate('not empty'));
        $this->assertTrue($val->validate(['with', 'elements']));
    }

    public function testValidateValueWithValue()
    {
        $val = new RequiredValidator(['requiredValue' => 55]);
        $this->assertTrue($val->validate(55));
        $this->assertTrue($val->validate("55"));
        $this->assertTrue($val->validate("0x37"));
        $this->assertFalse($val->validate("should fail"));
        $this->assertTrue($val->validate(true));
        $val->strict = true;
        $this->assertTrue($val->validate(55));
        $this->assertFalse($val->validate("55"));
        $this->assertFalse($val->validate("0x37"));
        $this->assertFalse($val->validate("should fail"));
        $this->assertFalse($val->validate(true));
    }

    public function testValidateAttribute()
    {
        // empty req-value
        $val = new RequiredValidator();
        $m = FakedValidationModel::createWithAttributes(['attr_val' => null]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $this->assertTrue(stripos(current($m->getErrors('attr_val')), 'blank') !== false);
        $val = new RequiredValidator(['requiredValue' => 55]);
        $m = FakedValidationModel::createWithAttributes(['attr_val' => 56]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $this->assertTrue(stripos(current($m->getErrors('attr_val')), 'must be') !== false);
        $val = new RequiredValidator(['requiredValue' => 55]);
        $m = FakedValidationModel::createWithAttributes(['attr_val' => 55]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertFalse($m->hasErrors('attr_val'));
    }
}
