<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\DynamicModel;
use yii\helpers\Json;
use yii\validators\TrimValidator;
use yii\web\View;
use yiiunit\TestCase;

/**
 * @group validators
 */
class TrimValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->destroyApplication();
    }

    public function testTrimWhitespace(): void
    {
        $model = new DynamicModel(['name' => '  hello  ']);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'name');

        $this->assertSame('hello', $model->name);
    }

    public function testTrimTabs(): void
    {
        $model = new DynamicModel(['name' => "\t\nhello\r\n"]);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'name');

        $this->assertSame('hello', $model->name);
    }

    public function testTrimCustomChars(): void
    {
        $model = new DynamicModel(['path' => '/hello/']);
        $validator = new TrimValidator();
        $validator->chars = '/';

        $validator->validateAttribute($model, 'path');

        $this->assertSame('hello', $model->path);
    }

    public function testTrimEmptyString(): void
    {
        $model = new DynamicModel(['name' => '']);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'name');

        $this->assertSame('', $model->name);
    }

    public function testTrimNullValue(): void
    {
        $model = new DynamicModel(['name' => null]);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'name');

        $this->assertSame('', $model->name);
    }

    public function testTrimIntegerValue(): void
    {
        $model = new DynamicModel(['count' => 42]);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'count');

        $this->assertSame('42', $model->count);
    }

    public function testTrimArray(): void
    {
        $model = new DynamicModel(['tags' => ['  foo  ', '  bar  ']]);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'tags');

        $this->assertSame(['foo', 'bar'], $model->tags);
    }

    public function testSkipOnArrayTrue(): void
    {
        $original = ['  foo  ', '  bar  '];
        $model = new DynamicModel(['tags' => $original]);
        $validator = new TrimValidator();
        $validator->skipOnArray = true;

        $validator->validateAttribute($model, 'tags');

        $this->assertSame($original, $model->tags);
    }

    public function testSkipOnArrayTrueWithScalar(): void
    {
        $model = new DynamicModel(['name' => '  hello  ']);
        $validator = new TrimValidator();
        $validator->skipOnArray = true;

        $validator->validateAttribute($model, 'name');

        $this->assertSame('hello', $model->name);
    }

    public function testSkipOnEmptyIsFalseByDefault(): void
    {
        $validator = new TrimValidator();

        $this->assertFalse($validator->skipOnEmpty);
    }

    public function testClientOptions(): void
    {
        $model = new DynamicModel(['name' => 'test']);
        $model->addRule('name', 'trim');
        $validator = new TrimValidator();

        $options = $validator->getClientOptions($model, 'name');

        $this->assertArrayHasKey('skipOnArray', $options);
        $this->assertArrayHasKey('skipOnEmpty', $options);
        $this->assertArrayHasKey('chars', $options);
        $this->assertSame(false, $options['skipOnArray']);
        $this->assertSame(false, $options['skipOnEmpty']);
        $this->assertSame(false, $options['chars']);
    }

    public function testClientOptionsWithCustomChars(): void
    {
        $model = new DynamicModel(['name' => 'test']);
        $validator = new TrimValidator();
        $validator->chars = '/\\';

        $options = $validator->getClientOptions($model, 'name');

        $this->assertSame('/\\', $options['chars']);
    }

    public function testClientValidateAttribute(): void
    {
        $model = new DynamicModel(['name' => '  hello  ']);
        $validator = new TrimValidator();
        $view = new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]);

        $result = $validator->clientValidateAttribute($model, 'name', $view);

        $this->assertStringStartsWith('value = yii.validation.trim($form, attribute, ', $result);
        $this->assertStringEndsWith(', value);', $result);
        $this->assertStringContainsString(Json::htmlEncode($validator->getClientOptions($model, 'name')), $result);
    }

    public function testClientValidateAttributeDoesNotSkipScalarWithSkipOnArray(): void
    {
        $model = new DynamicModel(['name' => '  hello  ']);
        $validator = new TrimValidator();
        $validator->skipOnArray = true;
        $view = new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]);

        $result = $validator->clientValidateAttribute($model, 'name', $view);

        $this->assertNotNull($result);
        $this->assertStringContainsString('yii.validation.trim', $result);
    }

    public function testClientValidateAttributeSkipsArray(): void
    {
        $model = new DynamicModel(['tags' => ['a', 'b']]);
        $validator = new TrimValidator();
        $validator->skipOnArray = true;
        $view = new View();

        $result = $validator->clientValidateAttribute($model, 'tags', $view);

        $this->assertNull($result);
    }

    public function testTrimBooleanValue(): void
    {
        $model = new DynamicModel(['flag' => true]);
        $validator = new TrimValidator();

        $validator->validateAttribute($model, 'flag');

        $this->assertSame('1', $model->flag);
    }

    public function testTrimWithModelRules(): void
    {
        $model = new DynamicModel(['name' => '  hello  ']);
        $model->addRule('name', 'trim');

        $model->validate();

        $this->assertSame('hello', $model->name);
        $this->assertFalse($model->hasErrors());
    }
}
