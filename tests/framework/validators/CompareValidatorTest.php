<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use Exception;
use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class CompareValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValueException(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $val = new CompareValidator();
        $val->validate('val');
    }

    public function testValidateValue(): void
    {
        $value = 18449;
        // default config
        $val = new CompareValidator(['compareValue' => $value]);
        $this->assertTrue($val->validate($value));
        $this->assertTrue($val->validate((string) $value));
        $this->assertFalse($val->validate($value + 1));

        // Using a closure for compareValue
        $val = new CompareValidator(['compareValue' => function () use ($value) {
            return $value;
        }]);
        $this->assertTrue($val->validate($value));
        $this->assertTrue($val->validate((string) $value));
        $this->assertFalse($val->validate($value + 1));

        foreach ($this->getOperationTestData($value) as $op => $tests) {
            $val = new CompareValidator(['compareValue' => $value]);
            $val->operator = $op;
            foreach ($tests as $test) {
                $this->assertEquals($test[1], $val->validate($test[0]), "Testing $op");
            }
        }
    }

    protected function getOperationTestData($value)
    {
        return [
            '===' => [
                [$value, true],
                [(string) $value, true],
                [(float) $value, true],
                [$value + 1, false],
            ],
            '!=' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [$value + 0.00001, true],
                [false, true],
            ],
            '!==' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [false, true],
            ],
            '>' => [
                [$value, false],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '>=' => [
                [$value, true],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '<' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, true],
            ],
            '<=' => [
                [$value, true],
                [$value + 1, false],
                [$value - 1, true],
            ],
            /*'non-op' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, false],
            ],*/
        ];
    }

    public function testValidateAttribute(): void
    {
        // invalid-array
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr = ['test_val'];
        $val->validateAttribute($model, 'attr');
        $this->assertTrue($model->hasErrors('attr'));
        $val = new CompareValidator(['compareValue' => 'test-string']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string-false';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        // assume: _repeat
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string2';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        // not existing op
        $val = new CompareValidator();
        $val->operator = '<>';
        $model = FakedValidationModel::createWithAttributes(['attr_o' => 5, 'attr_o_repeat' => 5]);
        $val->validateAttribute($model, 'attr_o');
        $this->assertTrue($model->hasErrors('attr_o'));
        // compareAttribute has validation error
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => false]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 10, 'attr_y' => 10]);
        $model->addError('attr_x', 'invalid value');
        $val->validateAttribute($model, 'attr_y');
        $this->assertTrue($model->hasErrors('attr_x'));
        $this->assertTrue($model->hasErrors('attr_y'));
        // compareAttribute has validation error but rule has skipOnError
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 10, 'attr_y' => 10]);
        $model->addError('attr_x', 'invalid value');
        $val->validateAttribute($model, 'attr_y');
        $this->assertTrue($model->hasErrors('attr_x'));
        $this->assertFalse($model->hasErrors('attr_y'));
    }

    public function testAttributeErrorMessages(): void
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr1' => 1,
            'attr2' => 2,
            'attrN' => 2,
        ]);

        foreach ($this->getTestDataForMessages() as $data) {
            $model->clearErrors($data[0]);
            $model->clearErrors($data[2]);
            $validator = new CompareValidator();
            $validator->operator = $data[1];
            $validator->message = null;
            $validator->init(); // reload messages
            $validator->{$data[4]} = $data[2];
            $validator->validateAttribute($model, $data[0]);
            $error = $model->getErrors($data[0])[0];
            $this->assertEquals($data[3], $error);
        }
    }

    protected function getTestDataForMessages()
    {
        return [
            ['attr1', '==', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attr1', '===', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attrN', '!=', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attrN', '!==', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attr1', '>', 2, 'attr1 must be greater than "2".', 'compareValue'],
            ['attr1', '>=', 2, 'attr1 must be greater than or equal to "2".', 'compareValue'],
            ['attr2', '<', 1, 'attr2 must be less than "1".', 'compareValue'],
            ['attr2', '<=', 1, 'attr2 must be less than or equal to "1".', 'compareValue'],

            ['attr1', '==', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attr1', '===', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!=', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!==', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attr1', '>', 'attr2', 'attr1 must be greater than "attr2".', 'compareAttribute'],
            ['attr1', '>=', 'attr2', 'attr1 must be greater than or equal to "attr2".', 'compareAttribute'],
            ['attr2', '<', 'attr1', 'attr2 must be less than "attr1".', 'compareAttribute'],
            ['attr2', '<=', 'attr1', 'attr2 must be less than or equal to "attr1".', 'compareAttribute'],
        ];
    }

    public function testValidateAttributeOperators(): void
    {
        $value = 55;
        foreach ($this->getOperationTestData($value) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator, 'compareValue' => $value]);
            foreach ($tests as $test) {
                $model = new FakedValidationModel();
                $model->attr_test = $test[0];
                $val->validateAttribute($model, 'attr_test');
                $this->assertEquals($test[1], !$model->hasErrors('attr_test'));
            }
        }
    }

    public function testEnsureMessageSetOnInit(): void
    {
        foreach ($this->getOperationTestData(1337) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator]);
            $this->assertTrue(strlen($val->message) > 1);
        }
        try {
            new CompareValidator(['operator' => '<>']);
        } catch (InvalidConfigException $e) {
            return;
        } catch (Exception $e) {
            $this->fail('InvalidConfigException expected' . get_class($e) . 'received');

            return;
        }
        $this->fail('InvalidConfigException expected none received');
    }

    public function testValidateValueWithTypeNumber(): void
    {
        $val = new CompareValidator(['compareValue' => 10, 'type' => CompareValidator::TYPE_NUMBER]);
        $this->assertTrue($val->validate(10));
        $this->assertTrue($val->validate('10'));
        $this->assertTrue($val->validate(10.0));
        $this->assertFalse($val->validate(11));
    }

    public function testCompareValuesWithTypeNumber(): void
    {
        $val = new CompareValidator(['compareValue' => 100, 'type' => CompareValidator::TYPE_NUMBER, 'operator' => '>']);
        $this->assertTrue($val->validate(101));
        $this->assertFalse($val->validate(100));
        $this->assertFalse($val->validate(99));

        $val->operator = '<';
        $val->message = null;
        $val->init();
        $this->assertTrue($val->validate(99));
        $this->assertFalse($val->validate(100));
        $this->assertFalse($val->validate(101));
    }

    public function testValidateAttributeWithClosure(): void
    {
        $val = new CompareValidator([
            'compareValue' => function ($model, $attribute) {
                return $model->attr_test_val;
            },
        ]);
        $model = FakedValidationModel::createWithAttributes([
            'attr_test' => 'hello',
            'attr_test_val' => 'hello',
        ]);
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));

        $model = FakedValidationModel::createWithAttributes([
            'attr_test' => 'hello',
            'attr_test_val' => 'world',
        ]);
        $val = new CompareValidator([
            'compareValue' => function ($model, $attribute) {
                return $model->attr_test_val;
            },
        ]);
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
    }

    public function testValidateValueWithClosure(): void
    {
        $val = new CompareValidator(['compareValue' => function () {
            return 42;
        }]);
        $this->assertTrue($val->validate(42));
        $this->assertFalse($val->validate(43));
    }

    public function testGetClientOptionsWithCompareValue(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'expected']);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertSame('==', $options['operator']);
        $this->assertSame('string', $options['type']);
        $this->assertSame('expected', $options['compareValue']);
        $this->assertArrayNotHasKey('compareAttribute', $options);
        $this->assertArrayHasKey('message', $options);
    }

    public function testGetClientOptionsWithCompareAttribute(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $model->attr_test_val = 'test';
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertArrayHasKey('compareAttribute', $options);
        $this->assertArrayHasKey('compareAttributeName', $options);
        $this->assertArrayNotHasKey('compareValue', $options);
    }

    public function testGetClientOptionsDefaultCompareAttribute(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $model->attr_test_repeat = 'test';
        $val = new CompareValidator();
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertArrayHasKey('compareAttribute', $options);
        $this->assertStringContainsString('attr_test_repeat', $options['compareAttributeName']);
    }

    public function testGetClientOptionsWithSkipOnEmpty(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'test', 'skipOnEmpty' => true]);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertSame(1, $options['skipOnEmpty']);
    }

    public function testGetClientOptionsWithoutSkipOnEmpty(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'test', 'skipOnEmpty' => false]);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertArrayNotHasKey('skipOnEmpty', $options);
    }

    public function testClientValidateAttribute(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'expected']);
        $js = $val->clientValidateAttribute($model, 'attr_test', new CompareViewStub());

        $this->assertStringStartsWith('yii.validation.compare(', $js);
        $this->assertStringContainsString('$form', $js);
    }

    public function defaultMessagePerOperatorProvider(): array
    {
        return [
            ['==', 'equal to'],
            ['===', 'equal to'],
            ['!=', 'not be equal'],
            ['!==', 'not be equal'],
            ['>', 'greater than'],
            ['>=', 'greater than or equal'],
            ['<', 'less than'],
            ['<=', 'less than or equal'],
        ];
    }

    /**
     * @dataProvider defaultMessagePerOperatorProvider
     */
    public function testDefaultMessagePerOperator(string $operator, string $expectedSubstring): void
    {
        $val = new CompareValidator(['operator' => $operator, 'compareValue' => 1]);
        $this->assertStringContainsString($expectedSubstring, $val->message);
    }

    public function testClientValidateAttributeWithClosure(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => function () {
            return 'resolved';
        }]);
        $js = $val->clientValidateAttribute($model, 'attr_test', new CompareViewStub());

        $this->assertStringContainsString('resolved', $js);
    }

    public function testClientValidateAttributeContainsEncodedOptions(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'check']);
        $js = $val->clientValidateAttribute($model, 'attr_test', new CompareViewStub());

        $this->assertStringContainsString('"operator":"=="', $js);
        $this->assertStringContainsString('"compareValue":"check"', $js);
    }

    public function testValidateAttributeCompareAttributeHasErrorMessage(): void
    {
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => false]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 10, 'attr_y' => 10]);
        $model->addError('attr_x', 'invalid');
        $val->validateAttribute($model, 'attr_y');
        $errors = $model->getErrors('attr_y');
        $this->assertStringContainsString('attr_x', $errors[0]);
        $this->assertStringContainsString('is invalid', $errors[0]);
    }

    public function testValidateValueErrorContainsCompareValue(): void
    {
        $val = new CompareValidator(['compareValue' => 'expected']);
        $val->validate('wrong', $error);
        $this->assertStringContainsString('expected', $error);
    }

    public function testGetClientOptionsMessageContainsCompareValue(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'target']);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertStringContainsString('target', $options['message']);
    }

    public function testTypeNumberCastsToFloat(): void
    {
        $val = new CompareValidator([
            'compareValue' => '10',
            'type' => CompareValidator::TYPE_NUMBER,
            'operator' => '===',
        ]);
        $this->assertTrue($val->validate('10'));
        $this->assertTrue($val->validate(10));
        $this->assertTrue($val->validate(10.0));
    }

    public function testCompareAttributeErrorEarlyReturn(): void
    {
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => false]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 5, 'attr_y' => 99]);
        $model->addError('attr_x', 'bad value');
        $val->validateAttribute($model, 'attr_y');

        $errors = $model->getErrors('attr_y');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is invalid', $errors[0]);
    }

    public function testGetClientOptionsMessageContainsAttributeLabel(): void
    {
        $model = new FakedValidationModel();
        $model->attr_test = 'test';
        $val = new CompareValidator(['compareValue' => 'x']);
        $options = $val->getClientOptions($model, 'attr_test');

        $this->assertStringContainsString('attr_test', $options['message']);
    }

    public function testDefaultOperatorFallsThrough(): void
    {
        $val = new CompareValidator(['compareValue' => 5]);
        $val->operator = '<>';
        $this->assertFalse($val->validate(5));
        $this->assertFalse($val->validate(999));
    }
}

class CompareViewStub extends \yii\web\View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
