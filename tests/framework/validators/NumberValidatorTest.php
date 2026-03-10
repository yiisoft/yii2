<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use stdClass;
use yii\validators\NumberValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class NumberValidatorTest extends TestCase
{
    private $commaDecimalLocales = ['fr_FR.UTF-8', 'fr_FR.UTF8', 'fr_FR.utf-8', 'fr_FR.utf8', 'French_France.1252'];
    private $pointDecimalLocales = ['en_US.UTF-8', 'en_US.UTF8', 'en_US.utf-8', 'en_US.utf8', 'English_United States.1252'];
    private $oldLocale;

    private function setCommaDecimalLocale(): void
    {
        if ($this->oldLocale === false) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        if (setlocale(LC_NUMERIC, $this->commaDecimalLocales) === false) {
            $this->markTestSkipped('Could not set any of required locales: ' . implode(', ', $this->commaDecimalLocales));
        }
    }

    private function setPointDecimalLocale(): void
    {
        if ($this->oldLocale === false) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        if (setlocale(LC_NUMERIC, $this->pointDecimalLocales) === false) {
            $this->markTestSkipped('Could not set any of required locales: ' . implode(', ', $this->pointDecimalLocales));
        }
    }

    private function restoreLocale(): void
    {
        setlocale(LC_NUMERIC, $this->oldLocale);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->oldLocale = setlocale(LC_NUMERIC, 0);

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testEnsureMessageOnInit(): void
    {
        $val = new NumberValidator();
        $this->assertSame('{attribute} must be a number.', $val->message);
        $this->assertNull($val->max);
        $this->assertNull($val->min);
        $this->assertNull($val->tooSmall);
        $this->assertNull($val->tooBig);
        $val = new NumberValidator(['min' => -1, 'max' => 20, 'integerOnly' => true]);
        $this->assertSame('{attribute} must be an integer.', $val->message);
        $this->assertSame('{attribute} must be no less than {min}.', $val->tooSmall);
        $this->assertSame('{attribute} must be no greater than {max}.', $val->tooBig);
    }

    public function testValidateValueSimple(): void
    {
        $val = new NumberValidator();
        $this->assertTrue($val->validate(20));
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(-20));
        $this->assertTrue($val->validate('20'));
        $this->assertTrue($val->validate(25.45));
        $this->assertFalse($val->validate(false, $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate(true, $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate('0x14', $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertTrue($val->validate(0x14));
        $this->assertTrue($val->validate('0123'));
        $this->assertTrue($val->validate(0123));
        $this->assertFalse($val->validate('0b111', $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertTrue($val->validate(0b111));

        $this->setPointDecimalLocale();
        $this->assertFalse($val->validate('25,45', $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->setCommaDecimalLocale();
        $this->assertTrue($val->validate('25,45'));
        $this->restoreLocale();

        $this->assertFalse($val->validate('12:45', $error));
        $this->assertSame('the input value must be a number.', $error);

        $val = new NumberValidator(['integerOnly' => true]);
        $this->assertTrue($val->validate(20));
        $this->assertTrue($val->validate(0));
        $this->assertFalse($val->validate(25.45, $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate('20'));
        $this->assertFalse($val->validate('25,45', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate('020'));
        $this->assertFalse($val->validate('0x14', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate(0x14));
        $this->assertTrue($val->validate('0123'));
        $this->assertTrue($val->validate(0123));
        $this->assertFalse($val->validate('0b111', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate(0b111));
        $this->assertFalse($val->validate(false, $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate(true, $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateValueArraySimple(): void
    {
        $val = new NumberValidator();
        $this->assertFalse($val->validate([20], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([0], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([-20], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate(['20'], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([25.45], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([false], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([true], $error));
        $this->assertSame('the input value must be a number.', $error);

        $val = new NumberValidator();
        $val->allowArray = true;
        $this->assertTrue($val->validate([20]));
        $this->assertTrue($val->validate([0]));
        $this->assertTrue($val->validate([-20]));
        $this->assertTrue($val->validate(['20']));
        $this->assertTrue($val->validate([25.45]));
        $this->assertFalse($val->validate([false], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate([true], $error));
        $this->assertSame('the input value must be a number.', $error);

        $this->setPointDecimalLocale();
        $this->assertFalse($val->validate(['25,45'], $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->setCommaDecimalLocale();
        $this->assertTrue($val->validate(['25,45']));
        $this->restoreLocale();

        $this->assertFalse($val->validate(['12:45'], $error));
        $this->assertSame('the input value must be a number.', $error);

        $val = new NumberValidator(['integerOnly' => true]);
        $val->allowArray = true;
        $this->assertTrue($val->validate([20]));
        $this->assertTrue($val->validate([0]));
        $this->assertFalse($val->validate([25.45], $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate(['20']));
        $this->assertFalse($val->validate(['25,45'], $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertTrue($val->validate(['020']));
        $this->assertTrue($val->validate([0x14]));
        $this->assertFalse($val->validate(['0x14'], $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate([false], $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate([true], $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateValueAdvanced(): void
    {
        $val = new NumberValidator();
        $this->assertTrue($val->validate('-1.23')); // signed float
        $this->assertTrue($val->validate('-4.423e-12')); // signed float + exponent
        $this->assertTrue($val->validate('12E3')); // integer + exponent
        $this->assertFalse($val->validate('e12', $error)); // just exponent
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate('-e3', $error));
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate('-4.534-e-12', $error)); // 'signed' exponent
        $this->assertSame('the input value must be a number.', $error);
        $this->assertFalse($val->validate('12.23^4', $error)); // expression instead of value
        $this->assertSame('the input value must be a number.', $error);

        $val = new NumberValidator(['integerOnly' => true]);
        $this->assertFalse($val->validate('-1.23', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('-4.423e-12', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('12E3', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('e12', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('-e3', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('-4.534-e-12', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('12.23^4', $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateValueWithLocaleWhereDecimalPointIsComma(): void
    {
        $val = new NumberValidator();

        $this->setPointDecimalLocale();
        $this->assertTrue($val->validate(.5));

        $this->setCommaDecimalLocale();
        $this->assertTrue($val->validate(.5));

        $this->restoreLocale();
    }

    public function testValidateValueMin(): void
    {
        $val = new NumberValidator(['min' => 1]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(-1, $error));
        $this->assertSame('the input value must be no less than 1.', $error);
        $this->assertFalse($val->validate('22e-12', $error));
        $this->assertSame('the input value must be no less than 1.', $error);
        $this->assertTrue($val->validate(PHP_INT_MAX + 1));

        $val = new NumberValidator(['min' => 1, 'integerOnly' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(-1, $error));
        $this->assertSame('the input value must be no less than 1.', $error);
        $this->assertFalse($val->validate('22e-12', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate(PHP_INT_MAX + 1, $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateValueMax(): void
    {
        $val = new NumberValidator(['max' => 1.25]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(1.5, $error));
        $this->assertSame('the input value must be no greater than 1.25.', $error);
        $this->assertTrue($val->validate('22e-12'));
        $this->assertTrue($val->validate('125e-2'));
        $val = new NumberValidator(['max' => 1.25, 'integerOnly' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(1.5, $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('22e-12', $error));
        $this->assertSame('the input value must be an integer.', $error);
        $this->assertFalse($val->validate('125e-2', $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateValueRange(): void
    {
        $val = new NumberValidator(['min' => -10, 'max' => 20]);
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(-10));
        $this->assertFalse($val->validate(-11, $error));
        $this->assertSame('the input value must be no less than -10.', $error);
        $this->assertFalse($val->validate(21, $error));
        $this->assertSame('the input value must be no greater than 20.', $error);

        $val = new NumberValidator(['min' => -10, 'max' => 20, 'integerOnly' => true]);
        $this->assertTrue($val->validate(0));
        $this->assertFalse($val->validate(-11, $error));
        $this->assertSame('the input value must be no less than -10.', $error);
        $this->assertFalse($val->validate(22, $error));
        $this->assertSame('the input value must be no greater than 20.', $error);
        $this->assertFalse($val->validate('20e-1', $error));
        $this->assertSame('the input value must be an integer.', $error);
    }

    public function testValidateAttribute(): void
    {
        $val = new NumberValidator();
        $model = new FakedValidationModel();
        $model->attr_number = '5.5e1';
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = '43^32'; //expression
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 5;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be no less than 10.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 15;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be no greater than 10.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10, 'integerOnly' => true]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 3.43;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be an integer.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 1]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => [1, 2, 3]]);
        $val->validateAttribute($model, 'attr_num');
        $this->assertTrue($model->hasErrors('attr_num'));
        $this->assertSame('attr_num must be a number.', $model->getFirstError('attr_num'));

        // @see https://github.com/yiisoft/yii2/issues/11672
        $model = new FakedValidationModel();
        $model->attr_number = new stdClass();
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
    }

    public function testValidateAttributeArray(): void
    {
        $val = new NumberValidator();
        $val->allowArray = true;
        $model = new FakedValidationModel();
        $model->attr_number = ['5.5e1'];
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = ['43^32']; //expression
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 10]);
        $val->allowArray = true;
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = [5];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be no less than 10.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10]);
        $val->allowArray = true;
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = [15];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be no greater than 10.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10, 'integerOnly' => true]);
        $val->allowArray = true;
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = [3.43];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be an integer.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 1]);
        $val->allowArray = true;
        $model = FakedValidationModel::createWithAttributes(['attr_num' => [[1], [2], [3]]]);
        $val->validateAttribute($model, 'attr_num');
        $this->assertTrue($model->hasErrors('attr_num'));
        $this->assertSame('attr_num must be a number.', $model->getFirstError('attr_num'));

        // @see https://github.com/yiisoft/yii2/issues/11672
        $model = new FakedValidationModel();
        $model->attr_number = new stdClass();
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));

        $val = new NumberValidator();
        $model = new FakedValidationModel();
        $model->attr_number = ['5.5e1'];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $model->attr_number = ['43^32']; //expression
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $model->attr_number = [5];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $model->attr_number = [15];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['max' => 10, 'integerOnly' => true]);
        $model = new FakedValidationModel();
        $model->attr_number = [10];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be an integer.', $model->getFirstError('attr_number'));
        $model->attr_number = [3.43];
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be an integer.', $model->getFirstError('attr_number'));
        $val = new NumberValidator(['min' => 1]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => [[1], [2], [3]]]);
        $val->validateAttribute($model, 'attr_num');
        $this->assertTrue($model->hasErrors('attr_num'));
        $this->assertSame('attr_num must be a number.', $model->getFirstError('attr_num'));

        // @see https://github.com/yiisoft/yii2/issues/11672
        $model = new FakedValidationModel();
        $model->attr_number = new stdClass();
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertSame('attr_number must be a number.', $model->getFirstError('attr_number'));
    }

    public function testValidateAttributeWithLocaleWhereDecimalPointIsComma(): void
    {
        $val = new NumberValidator();
        $model = new FakedValidationModel();
        $model->attr_number = 0.5;

        $this->setPointDecimalLocale();
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));

        $this->setCommaDecimalLocale();
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));

        $this->restoreLocale();
    }

    public function testEnsureCustomMessageIsSetOnValidateAttributeGeneral(): void
    {
        $val = new NumberValidator(['message' => '{attribute} is not integer.']);
        $model = new FakedValidationModel();
        $model->attr_number = 'as';
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertCount(1, $model->getErrors('attr_number'));
        $msgs = $model->getErrors('attr_number');
        $this->assertSame('attr_number is not integer.', $msgs[0]);
    }

    public function testEnsureCustomMessageIsSetOnValidateAttributeMin(): void
    {
        $val = new NumberValidator([
            'tooSmall' => '{attribute} is too small.',
            'min' => 5,
        ]);
        $model = new FakedValidationModel();
        $model->attr_number = 0;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertCount(1, $model->getErrors('attr_number'));
        $msgs = $model->getErrors('attr_number');
        $this->assertSame('attr_number is too small.', $msgs[0]);
    }

    public function testEnsureCustomMessageIsSetOnValidateAttributeMax(): void
    {
        $val = new NumberValidator([
            'tooBig' => '{attribute} is too big.',
            'max' => 5,
        ]);
        $model = new FakedValidationModel();
        $model->attr_number = 6;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertCount(1, $model->getErrors('attr_number'));
        $msgs = $model->getErrors('attr_number');
        $this->assertSame('attr_number is too big.', $msgs[0]);
    }

    public function testGetClientOptions(): void
    {
        $val = new NumberValidator(['min' => 5, 'max' => 10, 'skipOnEmpty' => false]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => 7]);
        $options = $val->getClientOptions($model, 'attr_num');

        $this->assertStringContainsString('attr_num', $options['message']);
        $this->assertSame(5, $options['min']);
        $this->assertStringContainsString('attr_num', $options['tooSmall']);
        $this->assertStringContainsString('5', $options['tooSmall']);
        $this->assertSame(10, $options['max']);
        $this->assertStringContainsString('attr_num', $options['tooBig']);
        $this->assertStringContainsString('10', $options['tooBig']);
        $this->assertArrayNotHasKey('skipOnEmpty', $options);
    }

    public function testGetClientOptionsSkipOnEmpty(): void
    {
        $val = new NumberValidator(['skipOnEmpty' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => 7]);
        $options = $val->getClientOptions($model, 'attr_num');
        $this->assertSame(1, $options['skipOnEmpty']);
    }

    public function testGetClientOptionsIntegerPattern(): void
    {
        $val = new NumberValidator(['integerOnly' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => 7]);
        $options = $val->getClientOptions($model, 'attr_num');
        $this->assertStringContainsString('integer', $options['message']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3118
     */
    public function testClientValidateComparison(): void
    {
        $val = new NumberValidator([
            'min' => 5,
            'max' => 10,
        ]);
        $model = new FakedValidationModel();
        $js = $val->clientValidateAttribute($model, 'attr_number', new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]));
        $this->assertStringContainsString('"min":5', $js);
        $this->assertStringContainsString('"max":10', $js);

        $val = new NumberValidator([
            'min' => '5',
            'max' => '10',
        ]);
        $model = new FakedValidationModel();
        $js = $val->clientValidateAttribute($model, 'attr_number', new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]));
        $this->assertStringContainsString('"min":5', $js);
        $this->assertStringContainsString('"max":10', $js);

        $val = new NumberValidator([
            'min' => 5.65,
            'max' => 13.37,
        ]);
        $model = new FakedValidationModel();
        $js = $val->clientValidateAttribute($model, 'attr_number', new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]));
        $this->assertStringContainsString('"min":5.65', $js);
        $this->assertStringContainsString('"max":13.37', $js);

        $val = new NumberValidator([
            'min' => '5.65',
            'max' => '13.37',
        ]);
        $model = new FakedValidationModel();
        $js = $val->clientValidateAttribute($model, 'attr_number', new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]));
        $this->assertStringContainsString('"min":5.65', $js);
        $this->assertStringContainsString('"max":13.37', $js);
    }

    public function testValidateObject(): void
    {
        $val = new NumberValidator();
        $value = new stdClass();
        $this->assertFalse($val->validate($value));
    }

    public function testValidateResource(): void
    {
        $val = new NumberValidator();
        $fp = fopen('php://stdin', 'r');
        $this->assertFalse($val->validate($fp));

        $model = new FakedValidationModel();
        $model->attr_number = $fp;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));

        // the check is here for HHVM that
        // was losing handler for unknown reason
        if (is_resource($fp)) {
            fclose($fp);
        }
    }

    public function testValidateToString(): void
    {
        $val = new NumberValidator();
        $object = new TestClass('10');
        $this->assertTrue($val->validate($object));

        $model = new FakedValidationModel();
        $model->attr_number = $object;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18544
     */
    public function testNotTrimmedStrings(): void
    {
        $val = new NumberValidator(['integerOnly' => true]);
        $this->assertFalse($val->validate(' 1 '));
        $this->assertFalse($val->validate(' 1'));
        $this->assertFalse($val->validate('1 '));
        $this->assertFalse($val->validate("\t1\t"));
        $this->assertFalse($val->validate("\t1"));
        $this->assertFalse($val->validate("1\t"));

        $val = new NumberValidator();
        $this->assertFalse($val->validate(' 1.1 '));
        $this->assertFalse($val->validate(' 1.1'));
        $this->assertFalse($val->validate('1.1 '));
        $this->assertFalse($val->validate("\t1.1\t"));
        $this->assertFalse($val->validate("\t1.1"));
        $this->assertFalse($val->validate("1.1\t"));
    }

    public function testValidateValueRejectsArrayWhenAllowArrayIsFalse(): void
    {
        $val = new NumberValidator();
        $this->assertFalse($val->allowArray);
        $this->assertFalse($val->validate([1, 2, 3]));
    }
}

class TestClass
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function __toString()
    {
        return $this->foo;
    }
}
