<?php

namespace yiiunit\framework\validators;

use DateTime;
use yii\validators\DateValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\i18n\IntlTestHelper;
use yiiunit\TestCase;
use IntlDateFormatter;

/**
 * @group validators
 */
class DateValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
    }

    public function testEnsureMessageIsSet()
    {
        $val = new DateValidator;
        $this->assertTrue($val->message !== null && strlen($val->message) > 1);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateValue($timezone)
    {
        date_default_timezone_set($timezone);
        $this->testValidateValue($timezone);

        $this->mockApplication([
            'language' => 'en-GB',
            'components' => [
                'formatter' => [
                    'dateFormat' => 'short',
                ]
            ]
        ]);
        $val = new DateValidator();
        $this->assertTrue($val->validate('31/5/2017'));
        $this->assertFalse($val->validate('5/31/2017'));
        $val = new DateValidator(['format' => 'short', 'locale' => 'en-GB']);
        $this->assertTrue($val->validate('31/5/2017'));
        $this->assertFalse($val->validate('5/31/2017'));

        $this->mockApplication([
            'language' => 'de-DE',
            'components' => [
                'formatter' => [
                    'dateFormat' => 'short',
                ]
            ]
        ]);
        $val = new DateValidator();
        $this->assertTrue($val->validate('31.5.2017'));
        $this->assertFalse($val->validate('5.31.2017'));
        $val = new DateValidator(['format' => 'short', 'locale' => 'de-DE']);
        $this->assertTrue($val->validate('31.5.2017'));
        $this->assertFalse($val->validate('5.31.2017'));
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateValue($timezone)
    {
        date_default_timezone_set($timezone);

        // test PHP format
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $this->assertFalse($val->validate('3232-32-32'));
        $this->assertTrue($val->validate('2013-09-13'));
        $this->assertFalse($val->validate('31.7.2013'));
        $this->assertFalse($val->validate('31-7-2013'));
        $this->assertFalse($val->validate('20121212'));
        $this->assertFalse($val->validate('asdasdfasfd'));
        $this->assertFalse($val->validate('2012-12-12foo'));
        $this->assertFalse($val->validate(''));
        $this->assertFalse($val->validate(time()));
        $val->format = 'php:U';
        $this->assertTrue($val->validate(time()));
        $val->format = 'php:d.m.Y';
        $this->assertTrue($val->validate('31.7.2013'));
        $val->format = 'php:Y-m-!d H:i:s';
        $this->assertTrue($val->validate('2009-02-15 15:16:17'));

        // test ICU format
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $this->assertFalse($val->validate('3232-32-32'));
        $this->assertTrue($val->validate('2013-09-13'));
        $this->assertFalse($val->validate('31.7.2013'));
        $this->assertFalse($val->validate('31-7-2013'));
        $this->assertFalse($val->validate('20121212'));
        $this->assertFalse($val->validate('asdasdfasfd'));
        $this->assertFalse($val->validate('2012-12-12foo'));
        $this->assertFalse($val->validate(''));
        $this->assertFalse($val->validate(time()));
        $val->format = 'dd.MM.yyyy';
        $this->assertTrue($val->validate('31.7.2013'));
        $val->format = 'yyyy-MM-dd HH:mm:ss';
        $this->assertTrue($val->validate('2009-02-15 15:16:17'));
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateAttributePHPFormat($timezone)
    {
        $this->testValidateAttributePHPFormat($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateAttributePHPFormat($timezone)
    {
        date_default_timezone_set($timezone);

        // error-array-add
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $model = new FakedValidationModel;
        $model->attr_date = '1375293913';
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        //// timestamp attribute
        $val = new DateValidator(['format' => 'php:Y-m-d', 'timestampAttribute' => 'attr_timestamp']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertEquals(
            1379030400, // 2013-09-13 00:00:00
            $model->attr_timestamp
        );
        $val = new DateValidator(['format' => 'php:Y-m-d']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => []]);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));

    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidateAttributeICUFormat($timezone)
    {
        $this->testValidateAttributeICUFormat($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidateAttributeICUFormat($timezone)
    {
        date_default_timezone_set($timezone);

        // error-array-add
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $model = new FakedValidationModel;
        $model->attr_date = '1375293913';
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        //// timestamp attribute
        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'timestampAttribute' => 'attr_timestamp']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(
            1379030400, // 2013-09-13 00:00:00
            $model->attr_timestamp
        );
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => []]);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
        $val = new DateValidator(['format' => 'yyyy-MM-dd']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '2012-12-12foo']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertTrue($model->hasErrors('attr_date'));
    }

    public function testIntlMultibyteString()
    {
        $val = new DateValidator(['format' => 'dd MMM yyyy', 'locale' => 'de_DE']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '12 Mai 2014']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));

        $val = new DateValidator(['format' => 'dd MMM yyyy', 'locale' => 'ru_RU']);
        $model = FakedValidationModel::createWithAttributes(['attr_date' => '12 мая 2014']);
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
    }

    public function provideTimezones()
    {
        return [
            ['UTC'],
            ['Europe/Berlin'],
            ['America/Jamaica'],
        ];
    }

    public function timestampFormatProvider()
    {
        $return = [];
        foreach($this->provideTimezones() as $appTz) {
            foreach ($this->provideTimezones() as $tz) {
                $return[] = ['yyyy-MM-dd', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                // regardless of timezone, a simple date input should always result in 00:00:00 time
                $return[] = ['yyyy-MM-dd HH:mm:ss', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d', '2013-09-13', '2013-09-13', $tz[0], $appTz[0]];
                $return[] = ['php:Y-m-d H:i:s', '2013-09-13', '2013-09-13 00:00:00', $tz[0], $appTz[0]];
                $return[] = ['php:U', '2013-09-13', "1379030400", $tz[0], $appTz[0]];
                $return[] = [null, '2013-09-13', 1379030400, $tz[0], $appTz[0]];
            }
        }
        return $return;
    }

    /**
     * @dataProvider timestampFormatProvider
     */
    public function testIntlTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone)
    {
        $this->testTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone);
    }

    /**
     * @dataProvider timestampFormatProvider
     */
    public function testTimestampAttributeFormat($format, $date, $expectedDate, $timezone, $appTimezone)
    {
        date_default_timezone_set($timezone);

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => $format, 'timeZone' => $appTimezone]);
        $model = new FakedValidationModel;
        $model->attr_date = $date;
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame($expectedDate, $model->attr_timestamp);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidationWithTime($timezone)
    {
        $this->testValidationWithTime($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidationWithTime($timezone)
    {
        date_default_timezone_set($timezone);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(1379082195, $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame(1379082195, $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 14:23:15', $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 14:23:15', $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'php:Y-m-d H:i:s', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 14:23:15', $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'php:Y-m-d H:i:s', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 14:23:15', $model->attr_timestamp);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testIntlValidationWithTimeAndOutputTimeZone($timezone)
    {
        $this->testValidationWithTime($timezone);
    }

    /**
     * @dataProvider provideTimezones
     */
    public function testValidationWithTimeAndOutputTimeZone($timezone)
    {
        date_default_timezone_set($timezone);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'Europe/Berlin', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 16:23:15', $model->attr_timestamp);
        $val = new DateValidator(['format' => 'php:Y-m-d H:i:s', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'Europe/Berlin', 'timeZone' => 'UTC']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 14:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 16:23:15', $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'Europe/Berlin', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 16:23:15', $model->attr_timestamp);
        $val = new DateValidator(['format' => 'php:Y-m-d H:i:s', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'Europe/Berlin', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 16:23:15', $model->attr_timestamp);

        $val = new DateValidator(['format' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'America/New_York', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 10:23:15', $model->attr_timestamp);
        $val = new DateValidator(['format' => 'php:Y-m-d H:i:s', 'timestampAttribute' => 'attr_timestamp', 'timestampAttributeFormat' => 'yyyy-MM-dd HH:mm:ss', 'timestampAttributeTimeZone' => 'America/New_York', 'timeZone' => 'Europe/Berlin']);
        $model = new FakedValidationModel;
        $model->attr_date = '2013-09-13 16:23:15';
        $model->attr_timestamp = true;
        $val->validateAttribute($model, 'attr_date');
        $this->assertFalse($model->hasErrors('attr_date'));
        $this->assertFalse($model->hasErrors('attr_timestamp'));
        $this->assertSame('2013-09-13 10:23:15', $model->attr_timestamp);
    }

    public function testIntlValidateRange()
    {
        $this->testValidateValueRange();
    }

    public function testValidateValueRange()
    {
        if (PHP_INT_SIZE == 8) { // this passes only on 64bit systems
            // intl parser allows 14 for yyyy pattern, see the following for more details:
            // https://github.com/yiisoft/yii2/blob/a003a8fb487dfa60c0f88ecfacf18a7407ced18b/framework/validators/DateValidator.php#L51-L57
            $date = '14-09-13';
            $val = new DateValidator(['format' => 'yyyy-MM-dd']);
            $this->assertTrue($val->validate($date), "$date is valid");

            $min = '1900-01-01';
            $beforeMin = '1899-12-31';
        } else {
            $min = '1920-01-01';
            $beforeMin = '1919-12-31';
        }

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => $min]);
        $date = "1958-01-12";
        $this->assertTrue($val->validate($date), "$date is valid");

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'max' => '2000-01-01']);
        $date = '2014-09-13';
        $this->assertFalse($val->validate($date), "$date is too big");
        $date = "1958-01-12";
        $this->assertTrue($val->validate($date), "$date is valid");

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => $min, 'max' => '2000-01-01']);
        $this->assertTrue($val->validate('1999-12-31'), "max -1 day is valid");
        $this->assertTrue($val->validate('2000-01-01'), "max is inside range");
        $this->assertTrue($val->validate($min), "min is inside range");
        $this->assertFalse($val->validate($beforeMin), "min -1 day is invalid");
        $this->assertFalse($val->validate('2000-01-02'), "max +1 day is invalid");
    }

    private function validateModelAttribute($validator, $date, $expected, $message = '')
    {
        $model = new FakedValidationModel;
        $model->attr_date = $date;
        $validator->validateAttribute($model, 'attr_date');
        if (!$expected) {
            $this->assertTrue($model->hasErrors('attr_date'), $message);
        } else {
            $this->assertFalse($model->hasErrors('attr_date'), $message);
        }
    }

    public function testIntlValidateAttributeRange() {
        $this->testValidateAttributeRange();
    }

    public function testValidateAttributeRange()
    {
        if (PHP_INT_SIZE == 8) { // this passes only on 64bit systems
            // intl parser allows 14 for yyyy pattern, see the following for more details:
            // https://github.com/yiisoft/yii2/blob/a003a8fb487dfa60c0f88ecfacf18a7407ced18b/framework/validators/DateValidator.php#L51-L57
            $val = new DateValidator(['format' => 'yyyy-MM-dd']);
            $date = '14-09-13';
            $this->validateModelAttribute($val, $date, true, "$date is valid");

            $min = '1900-01-01';
            $beforeMin = '1899-12-31';
        } else {
            $min = '1920-01-01';
            $beforeMin = '1919-12-31';
        }

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => $min]);
        $date = '1958-01-12';
        $this->validateModelAttribute($val, $date, true, "$date is valid");

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'max' => '2000-01-01']);
        $date = '2014-09-13';
        $this->validateModelAttribute($val, $date, false, "$date is too big");
        $date = '1958-01-12';
        $this->validateModelAttribute($val, $date, true, "$date is valid");

        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => $min, 'max' => '2000-01-01']);
        $this->validateModelAttribute($val, '1999-12-31', true, "max -1 day is valid");
        $this->validateModelAttribute($val, '2000-01-01', true, "max is inside range");
        $this->validateModelAttribute($val, $min, true, "min is inside range");
        $this->validateModelAttribute($val, $beforeMin, false, "min -1 day is invalid");
        $this->validateModelAttribute($val, '2000-01-02', false, "max +1 day is invalid");
    }

    public function testIntlValidateValueRangeOld()
    {
        if ($this->checkOldIcuBug()) {
            $this->markTestSkipped("ICU is too old.");
        }
        $date = '14-09-13';
        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => '1920-01-01']);
        $this->assertFalse($val->validate($date), "$date is too small");
    }

    public function testIntlValidateAttributeRangeOld()
    {
        if ($this->checkOldIcuBug()) {
            $this->markTestSkipped("ICU is too old.");
        }
        $date = '14-09-13';
        $val = new DateValidator(['format' => 'yyyy-MM-dd', 'min' => '1920-01-01']);
        $this->validateModelAttribute($val, $date, false, "$date is too small");
    }

    /**
     * returns true if the version of ICU is old and has a bug that makes it
     * impossible to parse two digit years properly.
     * see http://bugs.icu-project.org/trac/ticket/9836
     * @return boolean
     */
    private function checkOldIcuBug()
    {
        $date = '14';
        $formatter = new IntlDateFormatter('en-US', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'yyyy');
        $parsePos = 0;
        $parsedDate = @$formatter->parse($date, $parsePos);

        if (is_int($parsedDate) && $parsedDate > 0) {
            return true;
        }

        return false;
    }
}
