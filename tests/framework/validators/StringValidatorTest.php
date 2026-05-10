<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use stdClass;
use yii\base\Model;
use yii\validators\StringValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class StringValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testInit(): void
    {
        $val = new StringValidator(['length' => [8, 128]]);
        $this->assertEquals(8, $val->min);
        $this->assertEquals(128, $val->max);
        $this->assertNull($val->length);

        $val = new StringValidator(['length' => [10]]);
        $this->assertEquals(10, $val->min);
        $this->assertNull($val->max);
        $this->assertNull($val->length);

        $val = new StringValidator(['encoding' => 'UTF-16']);
        $this->assertEquals('UTF-16', $val->encoding);
    }

    public function testInitWithOverwrite(): void
    {
        $val = new StringValidator([
            'min' => 20,
            'max' => 30,
            'length' => [5, 10],
        ]);
        $this->assertEquals(5, $val->min);
        $this->assertEquals(10, $val->max);
        $this->assertNull($val->length);
    }

    public function testValidateValue(): void
    {
        $val = new StringValidator();
        $this->assertFalse($val->validate(['not a string']));
        $this->assertTrue($val->validate('Just some string'));
        $this->assertFalse($val->validate(true));
        $this->assertFalse($val->validate(false));

        $val->strict = false;
        $this->assertTrue($val->validate(123));
        $this->assertTrue($val->validate(123.45));
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

    public function testValidateValueNotEqual(): void
    {
        $val = new StringValidator(['length' => 5]);
        $this->assertFalse($val->validate('abc'));
        $this->assertTrue($val->validate('abcde'));
        $this->assertFalse($val->validate('abcdef'));
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

    public function testValidateAttributeLength(): void
    {
        $this->mockWebApplication();
        $val = new StringValidator(['length' => 5]);
        $model = new FakedValidationModel();
        $model->attr_string = 'abc';
        $val->validateAttribute($model, 'attr_string');
        $this->assertTrue($model->hasErrors('attr_string'));
        $this->assertStringContainsString('should contain 5 characters', $model->getFirstError('attr_string'));

        $model = new FakedValidationModel();
        $model->attr_string = 'abcde';
        $val->validateAttribute($model, 'attr_string');
        $this->assertFalse($model->hasErrors('attr_string'));
    }

    public function testValidateAttributeStrict(): void
    {
        $val = new StringValidator(['strict' => false]);
        $model = new FakedValidationModel();
        $model->attr_string = 12345;
        $val->validateAttribute($model, 'attr_string');
        $this->assertFalse($model->hasErrors('attr_string'));
        $this->assertSame(12345, $model->attr_string);
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

    public function testGetClientOptions(): void
    {
        $this->mockWebApplication(['charset' => 'ISO-8859-1']);
        $model = new ModelForStringValidator();
        $val = new StringValidator(['min' => 5, 'max' => 10, 'length' => 7, 'skipOnEmpty' => true]);
        $options = $val->getClientOptions($model, 'attr');

        $this->assertEquals('ISO-8859-1', $val->encoding);
        $this->assertEquals(5, $options['min']);
        $this->assertEquals(10, $options['max']);
        $this->assertEquals(7, $options['is']);
        $this->assertEquals(1, $options['skipOnEmpty']);
        $this->assertArrayHasKey('message', $options);
        $this->assertStringContainsString('Test Attribute must be a string', $options['message']);
        $this->assertStringContainsString('should contain at least 5 characters', $options['tooShort']);
        $this->assertStringContainsString('should contain at most 10 characters', $options['tooLong']);
        $this->assertStringContainsString('should contain 7 characters', $options['notEqual']);
    }

    public function testClientValidateAttribute(): void
    {
        $this->mockWebApplication();
        $val = new StringValidator(['min' => 5]);
        $model = new ModelForStringValidator();
        $view = new StringViewStub();

        $js = $val->clientValidateAttribute($model, 'attr', $view);
        $this->assertStringContainsString('yii.validation.string', $js);
        $this->assertStringContainsString('"min":5', $js);
    }
}

class ModelForStringValidator extends Model
{
    public $attr;

    public function attributeLabels()
    {
        return ['attr' => 'Test Attribute'];
    }
}

class StringViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}
