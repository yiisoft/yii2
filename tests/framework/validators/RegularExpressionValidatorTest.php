<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\RegularExpressionValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class RegularExpressionValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValue(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $this->assertTrue($val->validate('b.4'));
        $this->assertFalse($val->validate('b./'));
        $this->assertFalse($val->validate(['a', 'b']));
        $val->not = true;
        $this->assertFalse($val->validate('b.4'));
        $this->assertTrue($val->validate('b./'));
        $this->assertFalse($val->validate(['a', 'b']));
    }

    public function testValidateAttribute(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'b.4']);
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertFalse($m->hasErrors('attr_reg1'));
        $m->attr_reg1 = 'b./';
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertTrue($m->hasErrors('attr_reg1'));
    }

    public function testValidateAttributeWithNotFlag(): void
    {
        $val = new RegularExpressionValidator([
            'pattern' => '/^[a-zA-Z0-9]+$/',
            'not' => true,
        ]);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc']);
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertTrue($m->hasErrors('attr_reg1'));

        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc!!!']);
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertFalse($m->hasErrors('attr_reg1'));
    }

    public function testMessageSetOnInit(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $this->assertIsString($val->message);
    }

    public function testInitException(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $val = new RegularExpressionValidator();
        $val->validate('abc');
    }

    public function testClientValidateAttribute(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-z]+$/i']);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc']);
        $js = $val->clientValidateAttribute($m, 'attr_reg1', new RegexViewStub());
        $this->assertStringContainsString('yii.validation.regularExpression', $js);
    }

    public function testGetClientOptions(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-z]+$/i']);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc']);
        $options = $val->getClientOptions($m, 'attr_reg1');
        $this->assertArrayHasKey('pattern', $options);
        $this->assertArrayHasKey('not', $options);
        $this->assertArrayHasKey('message', $options);
        $this->assertFalse($options['not']);
        $this->assertStringContainsString('attr_reg1', $options['message']);
    }

    public function testGetClientOptionsWithNot(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-z]+$/i', 'not' => true]);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc']);
        $options = $val->getClientOptions($m, 'attr_reg1');
        $this->assertTrue($options['not']);
    }

    public function testGetClientOptionsWithSkipOnEmpty(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-z]+$/i', 'skipOnEmpty' => true]);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'abc']);
        $options = $val->getClientOptions($m, 'attr_reg1');
        $this->assertSame(1, $options['skipOnEmpty']);
    }
}

class RegexViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
