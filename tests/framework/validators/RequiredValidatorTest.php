<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\Model;
use yii\validators\RequiredValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class RequiredValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValueWithDefaults(): void
    {
        $val = new RequiredValidator();
        $this->assertFalse($val->validate(null));
        $this->assertFalse($val->validate([]));
        $this->assertTrue($val->validate('not empty'));
        $this->assertTrue($val->validate(['with', 'elements']));
    }

    public function testValidateValueWithValue(): void
    {
        $val = new RequiredValidator(['requiredValue' => 55]);
        $this->assertTrue($val->validate(55));
        $this->assertTrue($val->validate('55'));
        $this->assertFalse($val->validate('should fail'));
        $this->assertTrue($val->validate(true));
        $val->strict = true;
        $this->assertTrue($val->validate(55));
        $this->assertFalse($val->validate('55'));
        $this->assertFalse($val->validate('0x37'));
        $this->assertFalse($val->validate('should fail'));
        $this->assertFalse($val->validate(true));
    }

    public function testValidateAttribute(): void
    {
        // empty req-value
        $val = new RequiredValidator();
        $m = FakedValidationModel::createWithAttributes(['attr_val' => null]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_val')), 'blank'));
        $val = new RequiredValidator(['requiredValue' => 55]);
        $m = FakedValidationModel::createWithAttributes(['attr_val' => 56]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_val')), 'must be'));
        $val = new RequiredValidator(['requiredValue' => 55]);
        $m = FakedValidationModel::createWithAttributes(['attr_val' => 55]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertFalse($m->hasErrors('attr_val'));
    }

    public function testErrorClientMessage(): void
    {
        $validator = new RequiredValidator(['message' => '<strong>error</strong> for {attribute}']);

        $obj = new ModelForReqValidator();

        $this->assertEquals(
            'yii.validation.required(value, messages, {"message":"\u003Cstrong\u003Eerror\u003C\/strong\u003E for \u003Cb\u003EAttr\u003C\/b\u003E"});',
            $validator->clientValidateAttribute($obj, 'attr', new RequiredViewStub())
        );
    }

    public function testValidateStrictWithNull(): void
    {
        $val = new RequiredValidator(['strict' => true]);
        $this->assertFalse($val->validate(null));
        $this->assertTrue($val->validate(''));
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(false));
    }

    public function testValidateWhitespaceOnly(): void
    {
        $val = new RequiredValidator();
        $this->assertFalse($val->validate('   '));
        $this->assertFalse($val->validate("\t"));
        $this->assertFalse($val->validate("\n"));
        $this->assertFalse($val->validate(" \t\n "));
    }

    public function testValidateEdgeValues(): void
    {
        $val = new RequiredValidator();
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate('0'));
        $this->assertTrue($val->validate(false));
        $this->assertTrue($val->validate(0.0));
    }

    public function testDefaultMessageWithoutRequiredValue(): void
    {
        $val = new RequiredValidator();
        $this->assertStringContainsString('blank', $val->message);
    }

    public function testDefaultMessageWithRequiredValue(): void
    {
        $val = new RequiredValidator(['requiredValue' => 'yes']);
        $this->assertStringContainsString('must be', $val->message);
    }

    public function testSkipOnEmptyDefaultIsFalse(): void
    {
        $val = new RequiredValidator();
        $this->assertFalse($val->skipOnEmpty);
    }

    public function testGetClientOptionsWithoutRequiredValue(): void
    {
        $model = new ModelForReqValidator();
        $val = new RequiredValidator();
        $options = $val->getClientOptions($model, 'attr');

        $this->assertArrayHasKey('message', $options);
        $this->assertArrayNotHasKey('requiredValue', $options);
        $this->assertArrayNotHasKey('strict', $options);
    }

    public function testGetClientOptionsWithRequiredValue(): void
    {
        $model = new ModelForReqValidator();
        $val = new RequiredValidator(['requiredValue' => 'yes']);
        $options = $val->getClientOptions($model, 'attr');

        $this->assertArrayHasKey('message', $options);
        $this->assertArrayHasKey('requiredValue', $options);
        $this->assertSame('yes', $options['requiredValue']);
    }

    public function testGetClientOptionsWithStrict(): void
    {
        $model = new ModelForReqValidator();
        $val = new RequiredValidator(['strict' => true]);
        $options = $val->getClientOptions($model, 'attr');

        $this->assertArrayHasKey('strict', $options);
        $this->assertSame(1, $options['strict']);
    }

    public function testValidateAttributeWithWhitespace(): void
    {
        $val = new RequiredValidator();
        $m = FakedValidationModel::createWithAttributes(['attr_val' => '   ']);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
    }

    public function testErrorMessageContainsRequiredValue(): void
    {
        $val = new RequiredValidator(['requiredValue' => 'agree']);
        $m = FakedValidationModel::createWithAttributes(['attr_val' => 'disagree']);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $errors = $m->getErrors('attr_val');
        $this->assertStringContainsString('agree', $errors[0]);
    }

    public function testErrorMessageWithoutRequiredValueShowsBlank(): void
    {
        $val = new RequiredValidator();
        $m = FakedValidationModel::createWithAttributes(['attr_val' => null]);
        $val->validateAttribute($m, 'attr_val');
        $this->assertTrue($m->hasErrors('attr_val'));
        $errors = $m->getErrors('attr_val');
        $this->assertStringContainsString('blank', $errors[0]);
        $this->assertStringNotContainsString('{requiredValue}', $errors[0]);
    }

    public function testGetClientOptionsMessageContainsRequiredValue(): void
    {
        $model = new ModelForReqValidator();
        $val = new RequiredValidator(['requiredValue' => 'confirm']);
        $options = $val->getClientOptions($model, 'attr');
        $this->assertStringContainsString('confirm', $options['message']);
    }
}

class ModelForReqValidator extends Model
{
    public $attr;

    public function rules()
    {
        return [
            [['attr'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return ['attr' => '<b>Attr</b>'];
    }
}

class RequiredViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
