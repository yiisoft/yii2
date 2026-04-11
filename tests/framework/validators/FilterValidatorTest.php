<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\FilterValidator;
use yii\validators\Validator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class FilterValidatorTest extends TestCase
{
    use ClientScriptDispatchTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    protected function createValidatorInstance(array $config = []): Validator
    {
        return new FilterValidator(array_merge(['filter' => 'trim'], $config));
    }

    public function testGetClientOptionsDelegatesToClientScript(): void
    {
        $this->markTestSkipped(
            "'FilterValidator::getClientOptions()' has a native non-delegating implementation (returns " .
            "'skipOnEmpty' based on the validator's configuration, not forwarded from '\$clientScript').",
        );
    }

    public function testAssureExceptionOnInit(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        new FilterValidator();
    }

    public function testValidateAttribute(): void
    {
        $m = FakedValidationModel::createWithAttributes([
            'attr_one' => '  to be trimmed  ',
            'attr_two' => 'set this to null',
            'attr_empty1' => '',
            'attr_empty2' => null,
            'attr_array' => ['Maria', 'Anna', 'Elizabeth'],
            'attr_array_skipped' => ['John', 'Bill'],
        ]);
        $val = new FilterValidator(['filter' => 'trim']);
        $val->validateAttribute($m, 'attr_one');
        $this->assertSame('to be trimmed', $m->attr_one);
        $val->filter = fn($value) => null;
        $val->validateAttribute($m, 'attr_two');
        $this->assertNull($m->attr_two);
        $val->filter = $this->notToBeNull(...);
        $val->validateAttribute($m, 'attr_empty1');
        $this->assertSame($this->notToBeNull(''), $m->attr_empty1);
        $val->skipOnEmpty = true;
        $val->validateAttribute($m, 'attr_empty2');
        $this->assertNotNull($m->attr_empty2);
        $val->filter = fn($value) => implode(',', $value);
        $val->skipOnArray = false;
        $val->validateAttribute($m, 'attr_array');
        $this->assertSame('Maria,Anna,Elizabeth', $m->attr_array);
        $val->skipOnArray = true;
        $val->validateAttribute($m, 'attr_array_skipped');
        $this->assertSame(['John', 'Bill'], $m->attr_array_skipped);
    }

    public function notToBeNull($value)
    {
        return 'not null';
    }

    public function testClientValidateAttributeReturnsNullWithoutClientScript(): void
    {
        $val = new FilterValidator(['filter' => 'trim']);
        $m = FakedValidationModel::createWithAttributes(['attr_one' => 'test']);
        $this->assertNull($val->clientValidateAttribute($m, 'attr_one', new FilterViewStub()));
    }

    public function testGetClientOptionsDefault(): void
    {
        $val = new FilterValidator(['filter' => 'trim']);
        $m = FakedValidationModel::createWithAttributes(['attr_one' => 'test']);
        $options = $val->getClientOptions($m, 'attr_one');
        $this->assertArrayNotHasKey('skipOnEmpty', $options);
    }

    public function testGetClientOptionsWithSkipOnEmpty(): void
    {
        $val = new FilterValidator(['filter' => 'trim', 'skipOnEmpty' => true]);
        $m = FakedValidationModel::createWithAttributes(['attr_one' => 'test']);
        $options = $val->getClientOptions($m, 'attr_one');
        $this->assertSame(1, $options['skipOnEmpty']);
    }
}

class FilterViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
