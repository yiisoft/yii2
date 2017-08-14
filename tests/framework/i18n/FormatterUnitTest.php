<?php

namespace yiiunit\framework\i18n;

use NumberFormatter;
use yii\i18n\Formatter;
use Yii;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class FormatterUnitTest extends TestCase
{
    /**
     * @var Formatter
     */
    protected $formatter;

    protected function setUp()
    {
        parent::setUp();

        IntlTestHelper::setIntlStatus($this);

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'en-US',
        ]);
        $this->formatter = new Formatter(['locale' => 'pl-PL']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        IntlTestHelper::resetIntlStatus();
        $this->formatter = null;
    }

    public function lengthDataProvider()
    {
        return [
            [-3, "-3.00 metry", '-3.00 m'],
            [0, "0 milimetrów", '0 mm'],
            [0.005, "5 milimetrów", '5 mm'],
            [0.053, "5.30 centymetra", '5.30 cm'],
            [0.1, "10.00 centymetrów", '10.00 cm'],
            [1.123, "1.12 metra", '1.12 m'],
            [1893.12, "1.89 kilometra", '1.89 km'],
            [4561549, "4561.55 kilometra", '4561.55 km'],
        ];
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider lengthDataProvider
     */
    public function testAsLength($value, $expected)
    {
        $this->assertSame($expected, $this->formatter->asLength($value));
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider lengthDataProvider
     */
    public function testAsShortLength($value, $_, $expected)
    {
        $this->assertSame($expected, $this->formatter->asShortLength($value));
    }

    public function weightDataProvider()
    {
        return [
            [-3, '', ''],
            [0, '', ''],
            [0.001, '', ''],
            [0.091, '', ''],
            [0.1, '', ''],
            [1, '', ''],
            [453, '', ''],
            [19913.13, '', ''],
        ];
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider weightDataProvider
     */
    public function testAsWeight($value, $expected)
    {
        $this->assertSame($expected, $this->formatter->asWeight($value));
    }
}
