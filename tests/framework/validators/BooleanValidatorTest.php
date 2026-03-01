<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\Model;
use yii\validators\BooleanValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class BooleanValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->destroyApplication();
    }

    public function testInit(): void
    {
        $val = new BooleanValidator();
        $this->assertStringContainsString('must be either "{true}" or "{false}"', $val->message);
    }

    public function testValidateValue(): void
    {
        $val = new BooleanValidator();
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

    public function testValidateAttributeAndError(): void
    {
        $obj = new FakedValidationModel();
        $obj->attrA = true;
        $obj->attrB = '1';
        $obj->attrC = '0';
        $obj->attrD = [];
        $val = new BooleanValidator();
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

    public function testErrorMessage(): void
    {
        $this->mockWebApplication();
        $validator = new BooleanValidator([
            'trueValue' => true,
            'falseValue' => false,
            'strict' => true,
        ]);
        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertStringContainsString('must be either "true" or "false"', $errorMessage);
    }

    public function testGetClientOptions(): void
    {
        $this->mockWebApplication();
        $model = new ModelForBooleanValidator();
        $val = new BooleanValidator([
            'trueValue' => 'YES',
            'falseValue' => 'NO',
            'strict' => true,
            'skipOnEmpty' => true,
        ]);
        $options = $val->getClientOptions($model, 'attr');

        $this->assertEquals('YES', $options['trueValue']);
        $this->assertEquals('NO', $options['falseValue']);
        $this->assertEquals(1, $options['strict']);
        $this->assertEquals(1, $options['skipOnEmpty']);
        $this->assertStringContainsString('attr must be either "YES" or "NO"', $options['message']);
    }

    public function testClientValidateAttribute(): void
    {
        $val = new BooleanValidator();
        $model = new ModelForBooleanValidator();
        $view = new BooleanViewStub();

        $js = $val->clientValidateAttribute($model, 'attr', $view);
        $this->assertStringContainsString('yii.validation.boolean', $js);
    }
}

class ModelForBooleanValidator extends Model
{
    public $attr;

    public function attributeLabels()
    {
        return ['attr' => 'attr'];
    }
}

class BooleanViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
