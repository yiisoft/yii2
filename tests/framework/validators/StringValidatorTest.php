<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use stdClass;
use yii\validators\StringValidator;
use yii\validators\Validator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stubs\ViewStub;
use yiiunit\TestCase;

/**
 * @group validators
 */
class StringValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    protected function createValidatorInstance(array $config = []): Validator
    {
        return new StringValidator($config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testValidateValue(): void
    {
        $val = new StringValidator();
        $this->assertFalse($val->validate(['not a string']));
        $this->assertTrue($val->validate('Just some string'));
        $this->assertFalse($val->validate(true));
        $this->assertFalse($val->validate(false));
    }

    public function testValidateValueLength(): void
    {
        $val = new StringValidator(['length' => 25]);
        $this->assertTrue($val->validate(str_repeat('x', 25)));
        $this->assertTrue($val->validate(str_repeat('€', 25)));
        $this->assertFalse($val->validate(str_repeat('x', 125)));
        $this->assertFalse($val->validate(''));
        $val = new StringValidator(['length' => [25]]);
        $this->assertTrue($val->validate(str_repeat('x', 25)));
        $this->assertTrue($val->validate(str_repeat('x', 1250)));
        $this->assertFalse($val->validate(str_repeat('Ä', 24)));
        $this->assertFalse($val->validate(''));
        $val = new StringValidator(['length' => [10, 20]]);
        $this->assertTrue($val->validate(str_repeat('x', 15)));
        $this->assertTrue($val->validate(str_repeat('x', 10)));
        $this->assertTrue($val->validate(str_repeat('x', 20)));
        $this->assertFalse($val->validate(str_repeat('x', 5)));
        $this->assertFalse($val->validate(str_repeat('x', 25)));
        $this->assertFalse($val->validate(''));
        // make sure min/max are overridden
        $val = new StringValidator(['length' => [10, 20], 'min' => 25, 'max' => 35]);
        $this->assertTrue($val->validate(str_repeat('x', 15)));
        $this->assertFalse($val->validate(str_repeat('x', 30)));
    }

    public function testValidateValueMinMax(): void
    {
        $val = new StringValidator(['min' => 10]);
        $this->assertTrue($val->validate(str_repeat('x', 10)));
        $this->assertFalse($val->validate('xxxx'));
        $val = new StringValidator(['max' => 10]);
        $this->assertTrue($val->validate('xxxx'));
        $this->assertFalse($val->validate(str_repeat('y', 20)));
        $val = new StringValidator(['min' => 10, 'max' => 20]);
        $this->assertTrue($val->validate(str_repeat('y', 15)));
        $this->assertFalse($val->validate('abc'));
        $this->assertFalse($val->validate(str_repeat('b', 25)));
    }

    public function testValidateAttribute(): void
    {
        $val = new StringValidator();
        $model = new FakedValidationModel();
        $model->attr_string = 'a tet string';
        $val->validateAttribute($model, 'attr_string');
        $this->assertFalse($model->hasErrors());
        $model->attr_string = true;
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors());
        $model->attr_string = false;
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors());
        $val = new StringValidator(['length' => 20]);
        $model = new FakedValidationModel();
        $model->attr_string = str_repeat('x', 20);
        $val->validateAttribute($model, 'attr_string');
        $this->assertFalse($model->hasErrors());
        $model = new FakedValidationModel();
        $model->attr_string = 'abc';
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors('attr_string'));
        $val = new StringValidator(['max' => 2]);
        $model = new FakedValidationModel();
        $model->attr_string = 'a';
        $val->validateAttribute($model, 'attr_string');
        $this->assertFalse($model->hasErrors());
        $model = new FakedValidationModel();
        $model->attr_string = 'abc';
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors('attr_string'));
        $val = new StringValidator(['max' => 1]);
        $model = FakedValidationModel::createWithAttributes(['attr_str' => ['abc']]);
        $val->validateAttribute($model, 'attr_str');
        $this->assertTrue($model->hasErrors('attr_str'));
    }

    public function testEnsureMessagesOnInit(): void
    {
        $val = new StringValidator(['min' => 1, 'max' => 2]);
        $this->assertIsString($val->message);
        $this->assertIsString($val->tooLong);
        $this->assertIsString($val->tooShort);
    }

    public function testCustomErrorMessageInValidateAttribute(): void
    {
        $val = new StringValidator([
            'min' => 5,
            'tooShort' => '{attribute} to short. Min is {min}',
        ]);
        $model = new FakedValidationModel();
        $model->attr_string = 'abc';
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors('attr_string'));
        $errorMsg = $model->getErrors('attr_string');
        $this->assertEquals('attr_string to short. Min is 5', $errorMsg[0]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13327
     */
    public function testValidateValueInNonStrictMode(): void
    {
        $val = new StringValidator();
        $val->strict = false;

        // string
        $this->assertTrue($val->validate('Just some string'));

        // non-scalar
        $this->assertFalse($val->validate(['array']));
        $this->assertFalse($val->validate(new stdClass()));
        $this->assertFalse($val->validate(null));

        // bool
        $this->assertTrue($val->validate(true));
        $this->assertTrue($val->validate(false));

        // number
        $this->assertTrue($val->validate(42));
        $this->assertTrue($val->validate(36.6));
    }

    public function testValidateAttributeAddsNotEqualError(): void
    {
        $this->mockApplication();

        $val = new StringValidator(['length' => 5]);
        $model = new FakedValidationModel();

        $model->attr_string = 'abc';

        $val->validateAttribute($model, 'attr_string');

        $this->assertSame(
            'attr_string should contain 5 characters.',
            $model->getFirstError('attr_string'),
            'Error must come from the exact length template.',
        );

        $model = new FakedValidationModel();

        $model->attr_string = 'abcde';

        $val->validateAttribute($model, 'attr_string');

        $this->assertFalse(
            $model->hasErrors('attr_string'),
            'The exact length must be accepted.',
        );
    }

    public function testValidateAttributeAcceptsScalarWithoutMutationWhenNotStrict(): void
    {
        $val = new StringValidator(['strict' => false]);

        $model = new FakedValidationModel();

        $model->attr_string = 12345;

        $val->validateAttribute($model, 'attr_string');

        $this->assertFalse(
            $model->hasErrors('attr_string'),
            'A scalar must pass in non-strict mode.',
        );
        $this->assertSame(
            12345,
            $model->attr_string,
            'The attribute must keep its original type.',
        );
    }

    public function testInitTakesEncodingFromApplicationCharset(): void
    {
        $this->mockApplication(['charset' => 'ISO-8859-1']);

        $validator = new StringValidator();

        $this->assertSame(
            'ISO-8859-1',
            $validator->encoding,
            'The application charset must win.',
        );
    }

    public function testInitDefaultsEncodingToUtf8WithoutApplication(): void
    {
        $validator = new StringValidator();

        $this->assertSame(
            'UTF-8',
            $validator->encoding,
            'The fallback charset must be used.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testGetClientOptions(): void
    {
        $this->mockApplication();

        $val = new StringValidator(['min' => 5, 'max' => 10, 'length' => 7, 'skipOnEmpty' => true]);
        $model = new FakedValidationModel();

        $expected = [
            'message' => 'attr_string must be a string.',
            'min' => 5,
            'tooShort' => 'attr_string should contain at least 5 characters.',
            'max' => 10,
            'tooLong' => 'attr_string should contain at most 10 characters.',
            'is' => 7,
            'notEqual' => 'attr_string should contain 7 characters.',
            'skipOnEmpty' => 1,
        ];

        $this->assertSame(
            $expected,
            $val->getClientOptions($model, 'attr_string'),
            'Client options must pin every message and limit.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testClientValidateAttribute(): void
    {
        $this->mockApplication();

        $val = new StringValidator(['min' => 5]);
        $model = new FakedValidationModel();

        $this->assertSame(
            'yii.validation.string(value, messages, {"message":"attr_string must be a string.","min":5,"tooShort":"attr_string should contain at least 5 characters.","skipOnEmpty":1});',
            $val->clientValidateAttribute($model, 'attr_string', new ViewStub()),
            'Client script must pin the whole option set.',
        );
    }
}
