<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use NumberFormatter;
use Yii;
use yii\i18n\Formatter;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class FormatterNumberTest extends TestCase
{
    /**
     * @var Formatter
     */
    protected $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
        $this->formatter = new Formatter(['locale' => 'en-US']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
        $this->formatter = null;
    }

    /**
     * Provides some configuration that should not affect Integer formatter.
     */
    public function differentConfigProvider()
    {
        // make this test not break when intl is not installed
        if (!extension_loaded('intl')) {
            return [];
        }

        return [
            [[
                'numberFormatterOptions' => [
                    NumberFormatter::MIN_FRACTION_DIGITS => 2,
                ],
            ]],
            [[
                'numberFormatterOptions' => [
                    NumberFormatter::MAX_FRACTION_DIGITS => 2,
                ],
            ]],
            [[
                'numberFormatterOptions' => [
                    NumberFormatter::FRACTION_DIGITS => 2,
                ],
            ]],
            [[
                'numberFormatterOptions' => [
                    NumberFormatter::MIN_FRACTION_DIGITS => 2,
                    NumberFormatter::MAX_FRACTION_DIGITS => 4,
                ],
            ]],
        ];
    }


    /**
     * @dataProvider differentConfigProvider
     * @param array $config
     */
    public function testIntlAsInteger($config)
    {
        // configure formatter with different configs that should not affect integer format
        Yii::configure($this->formatter, $config);
        $this->testAsInteger();
    }

    public function testAsInteger()
    {
        $this->assertSame('123', $this->formatter->asInteger(123));
        $this->assertSame('123', $this->formatter->asInteger(123.00));
        $this->assertSame('123', $this->formatter->asInteger(123.23));
        $this->assertSame('123', $this->formatter->asInteger(123.53));
        $this->assertSame('0', $this->formatter->asInteger(0));
        $this->assertSame('-123', $this->formatter->asInteger(-123.23));
        $this->assertSame('-123', $this->formatter->asInteger(-123.53));

        $this->assertSame('123,456', $this->formatter->asInteger(123456));
        $this->assertSame('123,456', $this->formatter->asInteger(123456.789));

        // empty input
        $this->assertSame('0', $this->formatter->asInteger(false));
        $this->assertSame('0', $this->formatter->asInteger(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asInteger(null));

        // string fallback
        $this->assertSame('87,654,321,098,765,436', $this->formatter->asInteger('87654321098765436'));
        $this->assertSame('95,836,208,451,783,051', $this->formatter->asInteger('95836208451783051.864'));

        $this->formatter->thousandSeparator = '';
        $this->assertSame('95836208451783051', $this->formatter->asInteger('95836208451783051.864'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/16900
     */
    public function testIntlAsIntegerOptions()
    {
        $this->formatter->numberFormatterTextOptions = [
            \NumberFormatter::POSITIVE_PREFIX => '+',
        ];
        $this->assertSame('+2', $this->formatter->asInteger(2));
        $this->assertSame('+10', $this->formatter->asInteger(10));
        $this->assertSame('+12', $this->formatter->asInteger(12));
        $this->assertSame('+123', $this->formatter->asInteger(123));
        $this->assertSame('+1,230', $this->formatter->asInteger(1230));
        $this->assertSame('+123', $this->formatter->asInteger(123.23));
        $this->assertSame('+123', $this->formatter->asInteger(123.53));
        $this->assertSame('+0', $this->formatter->asInteger(0));
        $this->assertSame('-123', $this->formatter->asInteger(-123.23));
        $this->assertSame('-123', $this->formatter->asInteger(-123.53));

        $this->assertSame('+123,456', $this->formatter->asInteger(123456));
        $this->assertSame('+123,456', $this->formatter->asInteger(123456.789));
    }

    public function testAsIntegerException()
    {
        $this->expectException('\yii\base\InvalidParamException');
        $this->formatter->asInteger('a');
    }

    public function testAsIntegerException2()
    {
        $this->expectException('\yii\base\InvalidParamException');
        $this->formatter->asInteger('-123abc');
    }

    public function testIntlAsDecimal()
    {
        $value = 123.12;
        $this->assertSame('123.12', $this->formatter->asDecimal($value, 2));
        $this->assertSame('123.1', $this->formatter->asDecimal($value, 1));
        $this->assertSame('123', $this->formatter->asDecimal($value, 0));

        // power values
        $this->assertSame('2,000', $this->formatter->asDecimal(2e3));
        $this->assertSame('0', $this->formatter->asDecimal(1E-10));

        $value = 123;
        $this->assertSame('123', $this->formatter->asDecimal($value));
        $this->assertSame('123.00', $this->formatter->asDecimal($value, 2));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = '.';
        $value = 123.12;
        $this->assertSame('123,12', $this->formatter->asDecimal($value));
        $this->assertSame('123,1', $this->formatter->asDecimal($value, 1));
        $this->assertSame('123', $this->formatter->asDecimal($value, 0));
        $value = 123123.123;
        $this->assertSame('123.123', $this->formatter->asDecimal($value, 0));
        $this->assertSame('123.123,12', $this->formatter->asDecimal($value, 2));
        $this->formatter->thousandSeparator = '';
        $this->assertSame('123123,1', $this->formatter->asDecimal($value, 1));
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame('12 31 23,1', $this->formatter->asDecimal($value, 1, [\NumberFormatter::GROUPING_SIZE => 2]));

        $value = 123123.123;
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame('123 123', $this->formatter->asDecimal($value, 0));
        $this->assertSame('123 123,12', $this->formatter->asDecimal($value, 2));

        $this->formatter->decimalSeparator = null;
        $this->formatter->thousandSeparator = null;
        $value = '-123456.123';
        $this->assertSame('-123,456.123', $this->formatter->asDecimal($value));

        // empty input
        $this->assertSame('0', $this->formatter->asDecimal(false));
        $this->assertSame('0', $this->formatter->asDecimal(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));

        // string fallback
        $this->assertSame('87,654,321,098,765,436.00', $this->formatter->asDecimal('87654321098765436'));
        $this->assertSame('95,836,208,451,783,051.86', $this->formatter->asDecimal('95836208451783051.864'));
        $this->assertSame('95,836,208,451,783,052', $this->formatter->asDecimal('95836208451783051.864', 0));

        $this->formatter->thousandSeparator = ' ';
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('95 836 208 451 783 051,86', $this->formatter->asDecimal('95836208451783051.864'));
    }

    public function testAsDecimal()
    {
        $value = 123.12;
        $this->assertSame('123.12', $this->formatter->asDecimal($value));
        $this->assertSame('123.1', $this->formatter->asDecimal($value, 1));
        $this->assertSame('123', $this->formatter->asDecimal($value, 0));
        $value = 123;
        $this->assertSame('123.00', $this->formatter->asDecimal($value));

        $this->assertSame('123.00', $this->formatter->asDecimal('123.00'));
        $this->assertSame('-123.00', $this->formatter->asDecimal('-123.00'));
        $this->assertSame('123.00', $this->formatter->asDecimal('+123.00'));
        $this->assertSame('10.00', $this->formatter->asDecimal('10.00'));
        $this->assertSame('0.01', $this->formatter->asDecimal(0.01));
        $this->assertSame('0.00', $this->formatter->asDecimal('0.00'));
        $this->assertSame('0.01', $this->formatter->asDecimal('00000000000000.0100000000000'));

        // power values
        $this->assertSame('2,000.00', $this->formatter->asDecimal(2e3));
        $this->assertSame('0.00', $this->formatter->asDecimal(1E-10));

        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = '.';
        $value = 123.12;
        $this->assertSame('123,12', $this->formatter->asDecimal($value));
        $this->assertSame('123,1', $this->formatter->asDecimal($value, 1));
        $this->assertSame('123', $this->formatter->asDecimal($value, 0));
        $value = 123123.123;
        $this->assertSame('123.123,12', $this->formatter->asDecimal($value));

        $value = 123123.123;
        $this->assertSame('123.123,12', $this->formatter->asDecimal($value));
        $this->assertSame('123.123,12', $this->formatter->asDecimal($value, 2));
        $this->formatter->decimalSeparator = ',';
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame('123 123,12', $this->formatter->asDecimal($value));
        $this->assertSame('123 123,12', $this->formatter->asDecimal($value, 2));
        $this->formatter->thousandSeparator = '';
        $this->assertSame('123123,12', $this->formatter->asDecimal($value));
        $this->assertSame('123123,12', $this->formatter->asDecimal($value, 2));

        $this->formatter->decimalSeparator = null;
        $this->formatter->thousandSeparator = null;
        $value = '-123456.123';
        $this->assertSame('-123,456.123', $this->formatter->asDecimal($value, 3));

        // empty input
        $this->assertSame('0.00', $this->formatter->asDecimal(false));
        $this->assertSame('0.00', $this->formatter->asDecimal(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));

        // string fallback
        $this->assertSame('87,654,321,098,765,436.00', $this->formatter->asDecimal('87654321098765436'));
        $this->assertSame('95,836,208,451,783,051.86', $this->formatter->asDecimal('95836208451783051.864'));
        $this->assertSame('95,836,208,451,783,052', $this->formatter->asDecimal('95836208451783051.864', 0));
        $this->assertSame('95,836,208,451,783,051.9', $this->formatter->asDecimal('95836208451783051.864', 1));

        $this->formatter->thousandSeparator = ' ';
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('95 836 208 451 783 051,86', $this->formatter->asDecimal('95836208451783051.864'));
    }

    public function testIntlAsPercent()
    {
        $this->testAsPercent();
    }

    public function testAsPercent()
    {
        $this->assertSame('12,300%', $this->formatter->asPercent(123));
        $this->assertSame('12,300%', $this->formatter->asPercent('123'));
        $this->assertSame('12,300%', $this->formatter->asPercent('123.0'));
        $this->assertSame('12%', $this->formatter->asPercent(0.1234));
        $this->assertSame('12%', $this->formatter->asPercent('0.1234'));
        $this->assertSame('-1%', $this->formatter->asPercent(-0.009343));
        $this->assertSame('-1%', $this->formatter->asPercent('-0.009343'));

        // empty input
        $this->assertSame('0%', $this->formatter->asPercent(false));
        $this->assertSame('0%', $this->formatter->asPercent(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asPercent(null));

        // string fallback
        $this->assertSame('8,765,432,109,876,543,600%', $this->formatter->asPercent('87654321098765436'));
        $this->assertSame('9,583,620,845,178,305,186%', $this->formatter->asPercent('95836208451783051.864'));
        $this->assertSame('9,583,620,845,178,305,133%', $this->formatter->asPercent('95836208451783051.328'));

        $this->formatter->thousandSeparator = ' ';
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('9 583 620 845 178 305 186,40%', $this->formatter->asPercent('95836208451783051.864', 2));
    }

    public function testIntlAsCurrency()
    {
        $this->formatter->locale = 'en-US';
        $this->assertSame('$123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('$123.00', $this->formatter->asCurrency('123.00'));
        $this->assertSame('$123.20', $this->formatter->asCurrency('123.20'));
        $this->assertSame('$123,456.00', $this->formatter->asCurrency('123456'));
        $this->assertSame('$0.00', $this->formatter->asCurrency('0'));

        // power values
        $this->assertSame('$0.00', $this->formatter->asCurrency(1E-10));

        $this->formatter->locale = 'en-US';
        $this->formatter->currencyCode = 'USD';
        $this->assertSame('$123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('$123,456.00', $this->formatter->asCurrency('123456'));
        $this->assertSame('$0.00', $this->formatter->asCurrency('0'));
        // Starting from ICU 52.1, negative currency value will be formatted as -$123,456.12
        // see: http://source.icu-project.org/repos/icu/icu/tags/release-52-1/source/data/locales/en.txt
        //$value = '-123456.123';
        //$this->assertSame("($123,456.12)", $this->formatter->asCurrency($value));

        // "\xc2\xa0" is used as non-breaking space explicitly
        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123'));
        $this->formatter->currencyCode = 'USD';
        $this->assertSame("123,00\xc2\xa0$", $this->formatter->asCurrency('123'));
        $this->formatter->currencyCode = 'EUR';
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123'));

        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        $this->assertSame("123,00\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));
        $this->formatter->currencyCode = 'USD';
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        $this->assertSame("123,00\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));
        $this->formatter->currencyCode = 'EUR';
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        $this->assertSame("123,00\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));

        // default russian currency symbol
        $this->formatter->locale = 'ru-RU';
        $this->formatter->currencyCode = null;
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123,00\xc2\xa0₽", "123,00\xc2\xa0руб."]);
        $this->formatter->currencyCode = 'RUB';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123,00\xc2\xa0₽", "123,00\xc2\xa0руб."]);

        // custom currency symbol
        $this->formatter->currencyCode = null;
        $this->formatter->numberFormatterSymbols = [
            NumberFormatter::CURRENCY_SYMBOL => '₽',
        ];
        $this->assertSame("123,00\xc2\xa0₽", $this->formatter->asCurrency('123'));
        $this->formatter->numberFormatterSymbols = [
            NumberFormatter::CURRENCY_SYMBOL => 'RUR',
        ];
        $this->assertSame("123,00\xc2\xa0RUR", $this->formatter->asCurrency('123'));

        /* See https://github.com/yiisoft/yii2/issues/13629
        // setting the currency code overrides the symbol
        $this->formatter->currencyCode = 'RUB';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123,00\xc2\xa0₽", "123,00\xc2\xa0руб."]);
        $this->formatter->numberFormatterSymbols = [NumberFormatter::CURRENCY_SYMBOL => '₽'];
        $this->assertSame("123,00\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));
        $this->formatter->numberFormatterSymbols = [NumberFormatter::CURRENCY_SYMBOL => '₽'];
        $this->assertSame("123,00\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        */

        // custom separators
        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->formatter->numberFormatterSymbols = [];
        $this->formatter->thousandSeparator = ' ';
        $this->assertSame("123 456,00\xc2\xa0€", $this->formatter->asCurrency('123456', 'EUR'));

        // empty input
        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->formatter->numberFormatterSymbols = [];
        $this->formatter->thousandSeparator = null;
        $this->assertSame("0,00\xc2\xa0€", $this->formatter->asCurrency(false));
        $this->assertSame("0,00\xc2\xa0€", $this->formatter->asCurrency(''));

        // decimal formatting
        $this->formatter->locale = 'de-DE';
        $this->assertSame("100\xc2\xa0$", \Yii::$app->formatter->asCurrency(100, 'USD', [
            NumberFormatter::MAX_FRACTION_DIGITS => 0,
        ]));
        $this->assertSame("100,00\xc2\xa0$", $this->formatter->asCurrency(100, 'USD', [
            NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ]));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));

        // string fallback
        $this->assertSame('USD 87,654,321,098,765,436.00', $this->formatter->asCurrency('87654321098765436', 'USD'));
        $this->assertSame('USD 95,836,208,451,783,051.86', $this->formatter->asCurrency('95836208451783051.864', 'USD'));

        $this->formatter->thousandSeparator = ' ';
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('USD 95 836 208 451 783 051,86', $this->formatter->asCurrency('95836208451783051.864', 'USD'));

        // different currency decimal separator
        $this->formatter->locale = 'ru-RU';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123,00\xc2\xa0₽", "123,00\xc2\xa0руб."]);
        $this->formatter->currencyDecimalSeparator = ',';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123,00\xc2\xa0₽", "123,00\xc2\xa0руб."]);
        $this->formatter->currencyDecimalSeparator = '.';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123.00\xc2\xa0₽", "123.00\xc2\xa0руб."]);
    }

    public function testAsCurrencyStringFallbackException()
    {
        $this->expectException('\yii\base\InvalidConfigException');
        $this->formatter->asCurrency('87654321098765436');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12345
     */
    public function testIntlCurrencyFraction()
    {
        $this->formatter->numberFormatterOptions = [
            NumberFormatter::MIN_FRACTION_DIGITS => 0,
            NumberFormatter::MAX_FRACTION_DIGITS => 0,
        ];
        $this->formatter->locale = 'de-DE';
        $this->formatter->currencyCode = null;
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123'));
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123.00'));
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        $this->formatter->currencyCode = 'USD';
        $this->assertSame("123\xc2\xa0$", $this->formatter->asCurrency('123'));
        $this->assertSame("123\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));
        $this->formatter->currencyCode = 'EUR';
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123'));
        $this->assertSame("123\xc2\xa0$", $this->formatter->asCurrency('123', 'USD'));
        $this->assertSame("123\xc2\xa0€", $this->formatter->asCurrency('123', 'EUR'));

        $this->formatter->locale = 'ru-RU';
        $this->formatter->currencyCode = null;
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123\xc2\xa0₽", "123\xc2\xa0руб."]);

        $this->formatter->numberFormatterSymbols = [
            NumberFormatter::CURRENCY_SYMBOL => '&#8381;',
        ];
        $this->assertSame("123\xc2\xa0&#8381;", $this->formatter->asCurrency('123'));

        $this->formatter->numberFormatterSymbols = [];
        $this->formatter->currencyCode = 'RUB';
        $this->assertIsOneOf($this->formatter->asCurrency('123'), ["123\xc2\xa0₽", "123\xc2\xa0руб."]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/5261
     */
    public function testIntlIssue5261()
    {
        $this->formatter->locale = 'en-US';
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::FRACTION_DIGITS => 0,
        ];
        $this->formatter->numberFormatterTextOptions = [
            \NumberFormatter::CURRENCY_CODE => 'EUR',
        ];
        $this->assertSame('€100', $this->formatter->asCurrency(100, 'EUR'));
    }

    public function testAsCurrency()
    {
        $this->formatter->currencyCode = 'USD';
        $this->assertSame('USD 123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('USD 123.00', $this->formatter->asCurrency('123.00'));
        $this->assertSame('USD 0.00', $this->formatter->asCurrency('0'));
        $this->assertSame('USD -123.45', $this->formatter->asCurrency('-123.45'));
        $this->assertSame('USD -123.45', $this->formatter->asCurrency(-123.45));

        // power values
        $this->assertSame('USD 0.00', $this->formatter->asCurrency(1E-10));

        $this->formatter->currencyCode = 'EUR';
        $this->assertSame('EUR 123.00', $this->formatter->asCurrency('123'));
        $this->assertSame('EUR 0.00', $this->formatter->asCurrency('0'));
        $this->assertSame('EUR -123.45', $this->formatter->asCurrency('-123.45'));
        $this->assertSame('EUR -123.45', $this->formatter->asCurrency(-123.45));

        // empty input
        $this->formatter->currencyCode = 'USD';
        $this->formatter->numberFormatterSymbols = [];
        $this->assertSame('USD 0.00', $this->formatter->asCurrency(false));
        $this->assertSame('USD 0.00', $this->formatter->asCurrency(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));

        $this->assertSame('USD 876,543,210,987,654,367.00', $this->formatter->asCurrency('876543210987654367'));

        // string fallback
        $this->assertSame('USD 87,654,321,098,765,436.00', $this->formatter->asCurrency('87654321098765436', 'USD'));
        $this->assertSame('USD 95,836,208,451,783,051.86', $this->formatter->asCurrency('95836208451783051.864', 'USD'));

        $this->formatter->thousandSeparator = ' ';
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('USD 95 836 208 451 783 051,86', $this->formatter->asCurrency('95836208451783051.864', 'USD'));
    }

    public function testIntlAsScientific()
    {
        // see https://github.com/yiisoft/yii2/issues/17708
        $this->markTestSkipped('The test is unreliable since output depends on ICU version');

        $this->assertSame('1.23E2', $this->formatter->asScientific('123'));
        $this->assertSame('1.23456E5', $this->formatter->asScientific('123456'));
        $this->assertSame('-1.23456123E5', $this->formatter->asScientific('-123456.123'));

        // empty input
        $this->assertSame('0E0', $this->formatter->asScientific(false));
        $this->assertSame('0E0', $this->formatter->asScientific(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));

        $this->assertSame('8.76543210987654E16', $this->formatter->asScientific('87654321098765436'));
    }

    public function testAsScientific()
    {
        $this->assertSame('1.23E+2', $this->formatter->asScientific('123', 2));
        $this->assertSame('1.234560E+5', $this->formatter->asScientific('123456'));
        $this->assertSame('-1.234561E+5', $this->formatter->asScientific('-123456.123'));

        // empty input
        $this->assertSame('0.000000E+0', $this->formatter->asScientific(false));
        $this->assertSame('0.000000E+0', $this->formatter->asScientific(''));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));

        $this->assertSame('8.765432E+16', $this->formatter->asScientific('87654321098765436'));
    }

    public function testAsSpellout()
    {
        $this->expectException('\yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Format as Spellout is only supported when PHP intl extension is installed.');
        $this->formatter->asSpellout(123);
    }

    public function testIntlAsSpellout()
    {
        $this->assertSame('one hundred twenty-three', $this->formatter->asSpellout(123));

        $this->formatter->locale = 'de_DE';
        $this->assertSame('ein­hundert­drei­und­zwanzig', $this->formatter->asSpellout(123));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSpellout(null));
    }

    public function testIntlAsOrdinal()
    {
        $this->assertSame('0th', $this->formatter->asOrdinal(0));
        $this->assertSame('1st', $this->formatter->asOrdinal(1));
        $this->assertSame('2nd', $this->formatter->asOrdinal(2));
        $this->assertSame('3rd', $this->formatter->asOrdinal(3));
        $this->assertSame('5th', $this->formatter->asOrdinal(5));

        $this->formatter->locale = 'de_DE';
        $this->assertSame('0.', $this->formatter->asOrdinal(0));
        $this->assertSame('1.', $this->formatter->asOrdinal(1));
        $this->assertSame('2.', $this->formatter->asOrdinal(2));
        $this->assertSame('3.', $this->formatter->asOrdinal(3));
        $this->assertSame('5.', $this->formatter->asOrdinal(5));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asOrdinal(null));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14278
     */
    public function testIntlAsOrdinalDate()
    {
        $f = $this->formatter;
        $this->assertSame('15th', $f->asOrdinal($f->asDate('2017-05-15', 'php:j')));
        $this->assertSame('1st', $f->asOrdinal($f->asDate('2017-05-01', 'php:j')));

        $f->locale = 'de_DE';
        $this->assertSame('15.', $f->asOrdinal($f->asDate('2017-05-15', 'php:j')));
        $this->assertSame('1.', $f->asOrdinal($f->asDate('2017-05-01', 'php:j')));
    }

    public function testIntlAsShortSize()
    {
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::MIN_FRACTION_DIGITS => 0,
            \NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ];

        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('999 B', $this->formatter->asShortSize(999));
        $this->assertSame('999 B', $this->formatter->asShortSize('999'));
        $this->assertSame('1.05 MB', $this->formatter->asShortSize(1024 * 1024));
        $this->assertSame('1.07 GB', $this->formatter->asShortSize(1024 * 1024 * 1024));
        $this->assertSame('1.1 TB', $this->formatter->asShortSize(1024 * 1024 * 1024 * 1024));
        $this->assertSame('1 kB', $this->formatter->asShortSize(1000));
        $this->assertSame('1.02 kB', $this->formatter->asShortSize(1023));
        $this->assertNotEquals('3 PB', $this->formatter->asShortSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // string values
        $this->assertSame('28.41 GB', $this->formatter->asShortSize(28406984038));
        $this->assertSame('28.41 GB', $this->formatter->asShortSize((string) 28406984038));
        $this->assertSame('56.81 GB', $this->formatter->asShortSize(28406984038 + 28406984038));
        $this->assertSame('56.81 GB', $this->formatter->asShortSize((string) (28406984038 + 28406984038)));

        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('1 KiB', $this->formatter->asShortSize(1024));
        $this->assertSame('1 MiB', $this->formatter->asShortSize(1024 * 1024));
        // https://github.com/yiisoft/yii2/issues/4960
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
        $this->assertSame('5 GiB', $this->formatter->asShortSize(5 * 1024 * 1024 * 1024));
        $this->assertSame('6 TiB', $this->formatter->asShortSize(6 * 1024 * 1024 * 1024 * 1024));
        $this->assertNotEquals('5 PiB', $this->formatter->asShortSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        //$this->assertSame("1 YiB", $this->formatter->asShortSize(pow(2, 80)));
        $this->assertSame('2 GiB', $this->formatter->asShortSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame('1,001 KiB', $this->formatter->asShortSize(1025, 3));

        // empty values
        $this->assertSame('0 B', $this->formatter->asShortSize(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asShortSize(null));
    }

    public function testAsShortSize()
    {
        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('999 B', $this->formatter->asShortSize(999));
        $this->assertSame('999 B', $this->formatter->asShortSize('999'));
        $this->assertSame('1.05 MB', $this->formatter->asShortSize(1024 * 1024));
        $this->assertSame('1.07 GB', $this->formatter->asShortSize(1024 * 1024 * 1024));
        $this->assertSame('1.10 TB', $this->formatter->asShortSize(1024 * 1024 * 1024 * 1024));
        $this->assertSame('1.0486 MB', $this->formatter->asShortSize(1024 * 1024, 4));
        $this->assertSame('1.00 kB', $this->formatter->asShortSize(1000));
        $this->assertSame('1.02 kB', $this->formatter->asShortSize(1023));
        $this->assertNotEquals('3 PB', $this->formatter->asShortSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // string values
        $this->assertSame('28.41 GB', $this->formatter->asShortSize(28406984038));
        $this->assertSame('28.41 GB', $this->formatter->asShortSize((string) 28406984038));
        $this->assertSame('56.81 GB', $this->formatter->asShortSize(28406984038 + 28406984038));
        $this->assertSame('56.81 GB', $this->formatter->asShortSize((string) (28406984038 + 28406984038)));

        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('1.00 KiB', $this->formatter->asShortSize(1024));
        $this->assertSame('1.00 MiB', $this->formatter->asShortSize(1024 * 1024));
        // https://github.com/yiisoft/yii2/issues/4960
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
        $this->assertSame('5.00 GiB', $this->formatter->asShortSize(5 * 1024 * 1024 * 1024));
        $this->assertSame('6.00 TiB', $this->formatter->asShortSize(6 * 1024 * 1024 * 1024 * 1024));
        $this->assertNotEquals('5.00 PiB', $this->formatter->asShortSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        //$this->assertSame("1 YiB", $this->formatter->asShortSize(pow(2, 80)));
        $this->assertSame('2.00 GiB', $this->formatter->asShortSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->assertSame('1,001 KiB', $this->formatter->asShortSize(1025, 3));

        // empty values
        $this->assertSame('0 B', $this->formatter->asShortSize(0));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asShortSize(null));
    }

    public function testIntlAsSize()
    {
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::MIN_FRACTION_DIGITS => 0,
            \NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ];

        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('999 bytes', $this->formatter->asSize(999));
        $this->assertSame('999 bytes', $this->formatter->asSize('999'));
        $this->assertSame('1.05 megabytes', $this->formatter->asSize(1024 * 1024));
        $this->assertSame('1 kilobyte', $this->formatter->asSize(1000));
        $this->assertSame('1.02 kilobytes', $this->formatter->asSize(1023));
        $this->assertSame('3 gigabytes', $this->formatter->asSize(3 * 1000 * 1000 * 1000));
        $this->assertSame('4 terabytes', $this->formatter->asSize(4 * 1000 * 1000 * 1000 * 1000));
        $this->assertNotEquals('3 PB', $this->formatter->asSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('1 kibibyte', $this->formatter->asSize(1024));
        $this->assertSame('1 mebibyte', $this->formatter->asSize(1024 * 1024));
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('5 gibibytes', $this->formatter->asSize(5 * 1024 * 1024 * 1024));
        $this->assertSame('6 tebibytes', $this->formatter->asSize(6 * 1024 * 1024 * 1024 * 1024));
        $this->assertNotEquals('5 pibibytes', $this->formatter->asSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame('2 gibibytes', $this->formatter->asSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame('1,001 kibibytes', $this->formatter->asSize(1025, 3));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSize(null));
    }

    public function testIntlAsSizeNegative()
    {
        $this->formatter->numberFormatterOptions = [
            \NumberFormatter::MIN_FRACTION_DIGITS => 0,
            \NumberFormatter::MAX_FRACTION_DIGITS => 2,
        ];

        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('-999 bytes', $this->formatter->asSize(-999));
        $this->assertSame('-999 bytes', $this->formatter->asSize('-999'));
        $this->assertSame('-1.05 megabytes', $this->formatter->asSize(-1024 * 1024));
        $this->assertSame('-1 kilobyte', $this->formatter->asSize(-1000));
        $this->assertSame('-1.02 kilobytes', $this->formatter->asSize(-1023));
        $this->assertSame('-3 gigabytes', $this->formatter->asSize(-3 * 1000 * 1000 * 1000));
        $this->assertNotEquals('3 PB', $this->formatter->asSize(-3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('-1 kibibyte', $this->formatter->asSize(-1024));
        $this->assertSame('-1 mebibyte', $this->formatter->asSize(-1024 * 1024));
        $this->assertSame('-1023 bytes', $this->formatter->asSize(-1023));
        $this->assertSame('-5 gibibytes', $this->formatter->asSize(-5 * 1024 * 1024 * 1024));
        $this->assertNotEquals('-5 pibibytes', $this->formatter->asSize(-5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame('-2 gibibytes', $this->formatter->asSize(-2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame('-1,001 kibibytes', $this->formatter->asSize(-1025, 3));
    }

    public function testAsSize()
    {
        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('999 bytes', $this->formatter->asSize(999));
        $this->assertSame('999 bytes', $this->formatter->asSize('999'));
        $this->assertSame('1.05 megabytes', $this->formatter->asSize(1024 * 1024));
        $this->assertSame('1.0486 megabytes', $this->formatter->asSize(1024 * 1024, 4));
        $this->assertSame('1.00 kilobyte', $this->formatter->asSize(1000));
        $this->assertSame('1.02 kilobytes', $this->formatter->asSize(1023));
        $this->assertSame('3.00 gigabytes', $this->formatter->asSize(3 * 1000 * 1000 * 1000));
        $this->assertSame('4.00 terabytes', $this->formatter->asSize(4 * 1000 * 1000 * 1000 * 1000));
        $this->assertNotEquals('3 PB', $this->formatter->asSize(3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('1.00 kibibyte', $this->formatter->asSize(1024));
        $this->assertSame('1.00 mebibyte', $this->formatter->asSize(1024 * 1024));
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('5.00 gibibytes', $this->formatter->asSize(5 * 1024 * 1024 * 1024));
        $this->assertSame('6.00 tebibytes', $this->formatter->asSize(6 * 1024 * 1024 * 1024 * 1024));
        $this->assertNotEquals('5.00 pibibytes', $this->formatter->asSize(5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame('2.00 gibibytes', $this->formatter->asSize(2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame('1,001 kibibytes', $this->formatter->asSize(1025, 3));

        // null display
        $this->assertSame($this->formatter->nullDisplay, $this->formatter->asSize(null));
    }

    public function testAsSizeNegative()
    {
        // tests for base 1000
        $this->formatter->sizeFormatBase = 1000;
        $this->assertSame('-999 bytes', $this->formatter->asSize(-999));
        $this->assertSame('-999 bytes', $this->formatter->asSize('-999'));
        $this->assertSame('-1.05 megabytes', $this->formatter->asSize(-1024 * 1024));
        $this->assertSame('-1.0486 megabytes', $this->formatter->asSize(-1024 * 1024, 4));
        $this->assertSame('-1.00 kilobyte', $this->formatter->asSize(-1000));
        $this->assertSame('-1.02 kilobytes', $this->formatter->asSize(-1023));
        $this->assertSame('-3.00 gigabytes', $this->formatter->asSize(-3 * 1000 * 1000 * 1000));
        $this->assertNotEquals('3 PB', $this->formatter->asSize(-3 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000)); // this is 3 EB not 3 PB
        // tests for base 1024
        $this->formatter->sizeFormatBase = 1024;
        $this->assertSame('-1.00 kibibyte', $this->formatter->asSize(-1024));
        $this->assertSame('-1.00 mebibyte', $this->formatter->asSize(-1024 * 1024));
        $this->assertSame('-1023 bytes', $this->formatter->asSize(-1023));
        $this->assertSame('-5.00 gibibytes', $this->formatter->asSize(-5 * 1024 * 1024 * 1024));
        $this->assertNotEquals('-5.00 pibibytes', $this->formatter->asSize(-5 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)); // this is 5 EiB not 5 PiB
        $this->assertSame('-2.00 gibibytes', $this->formatter->asSize(-2147483647)); // round 1.999 up to 2
        $this->formatter->decimalSeparator = ',';
        $this->formatter->numberFormatterOptions = [];
        $this->assertSame('-1,001 kibibytes', $this->formatter->asSize(-1025, 3));
    }

    public function testIntlAsSizeConfiguration()
    {
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
        $this->formatter->thousandSeparator = '.';
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/4960
     */
    public function testAsSizeConfiguration()
    {
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
        $this->formatter->thousandSeparator = '.';
        $this->assertSame('1023 bytes', $this->formatter->asSize(1023));
        $this->assertSame('1023 B', $this->formatter->asShortSize(1023));
    }

    public function providerForDirectWrongTypeAttributes()
    {
        return [
            'not-int key for int options' => [
                ['a' => 1],
                [],
                'The $options array keys must be integers recognizable by NumberFormatter::setAttribute(). "string" provided instead.'
            ],
            'string value for int options' => [
                [1 => 'a'],
                [],
                'The $options array values must be integers. Did you mean to use $textOptions?'
            ],
            'non-string-int value for int options' => [
                [1 => 1.1],
                [],
                'The $options array values must be integers. "double" provided instead.'
            ],
            'not-int key for text options' => [
                [],
                ['a' => 1],
                'The $textOptions array keys must be integers recognizable by NumberFormatter::setTextAttribute(). "string" provided instead.'
            ],
            'int value for text options' => [
                [],
                [1 => 1],
                'The $textOptions array values must be strings. Did you mean to use $options?'
            ],
            'non-string-int value for text options' => [
                [],
                [1 => 1.1],
                'The $textOptions array values must be strings. "double" provided instead.'
            ],
        ];
    }

    /**
     * @dataProvider providerForDirectWrongTypeAttributes
     */
    public function testIntlAsIntegerDirectWrongTypeAttributes($intOptions, $textOptions, $message)
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage($message);
        $this->formatter->asInteger(1, $intOptions, $textOptions);
    }

    public function providerForConfiguredWrongTypeAttributes()
    {
        return [
            'not-int key for int options' => [
                ['a' => 1],
                [],
                [],
                'The numberFormatterOptions array keys must be integers recognizable by NumberFormatter::setAttribute(). "string" provided instead.'
            ],
            'string value for int options' => [
                [1 => 'a'],
                [],
                [],
                'The numberFormatterOptions array values must be integers. Did you mean to use numberFormatterTextOptions?'
            ],
            'non-string-int value for int options' => [
                [1 => 1.1],
                [],
                [],
                'The numberFormatterOptions array values must be integers. "double" provided instead.'
            ],
            'not-int key for text options' => [
                [],
                ['a' => 1],
                [],
                'The numberFormatterTextOptions array keys must be integers recognizable by NumberFormatter::setTextAttribute(). "string" provided instead.'
            ],
            'int value for text options' => [
                [],
                [1 => 1],
                [],
                'The numberFormatterTextOptions array values must be strings. Did you mean to use numberFormatterOptions?'
            ],
            'non-string-int value for text options' => [
                [],
                [1 => 1.1],
                [],
                'The numberFormatterTextOptions array values must be strings. "double" provided instead.'
            ],
            'non-int key for symbol' => [
                [],
                [],
                ['a' => 2],
                'The numberFormatterSymbols array keys must be integers recognizable by NumberFormatter::setSymbol(). "string" provided instead.'
            ],
            'non-string value for symbol' => [
                [],
                [],
                [1 => 3],
                'The numberFormatterSymbols array values must be strings. "integer" provided instead.'
            ],
        ];
    }

    /**
     * @dataProvider providerForConfiguredWrongTypeAttributes
     */
    public function testIntlAsIntegerConfiguredWrongTypeAttributes($intOptions, $textOptions, $symbols, $message)
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage($message);
        $this->formatter->numberFormatterTextOptions = $textOptions;
        $this->formatter->numberFormatterOptions = $intOptions;
        $this->formatter->numberFormatterSymbols = $symbols;
        $this->formatter->asInteger(1);
    }
}
