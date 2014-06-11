<?php

namespace yiiunit\framework\validators;

use DateTime;
use yii\validators\DateValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class DateValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testEnsureMessageIsSet()
    {
        $val = new DateValidator;
        $this->assertTrue($val->message !== null && strlen($val->message) > 1);
    }

    public function testValidateValue()
    {
        $val = new DateValidator;
        $this->assertFalse($val->validate('3232-32-32'));
        $this->assertTrue($val->validate('2013-09-13'));
        $this->assertFalse($val->validate('31.7.2013'));
        $this->assertFalse($val->validate('31-7-2013'));
        $this->assertFalse($val->validate(time()));
        $val->format = 'U';
        $this->assertTrue($val->validate(time()));
        $val->format = 'd.m.Y';
        $this->assertTrue($val->validate('31.7.2013'));
        $val->format = 'Y-m-!d H:i:s';
        $this->assertTrue($val->validate('2009-02-15 15:16:17'));
    }

    public function testValidateAttribute()
    {
        // error-array-add
        $val = new DateValidator;
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $model = new FakedValidationModel;
        $model->attr_date = '1375293913';
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        //// timestamp attribute
        $val = new DateValidator(['timestampAttribute' => 'attr_timestamp']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertEquals(
            DateTime::createFromFormat($val->format, '2013-09-13')->getTimestamp(),
            $model->attr_timestamp
        );
        $val = new DateValidator();
        $model = FakedValidationModel::createWithAttributes(['attr_date' => []]);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));

    }
}
