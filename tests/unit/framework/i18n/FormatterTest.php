<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use yii\i18n\Formatter;
use yiiunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @group i18n
 */
class FormatterTest extends TestCase
{
	/**
	 * @var Formatter
	 */
	protected $formatter;

	protected function setUp()
	{
		parent::setUp();
		if (!extension_loaded('intl')) {
			$this->markTestSkipped('intl extension is required.');
		}
		$this->mockApplication([
			'timeZone' => 'UTC',
		]);
		$this->formatter = new Formatter(['locale' => 'en-US']);
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->formatter = null;
	}

	public function testAsDecimal()
	{
		$value = '123';
		$this->assertSame($value, $this->formatter->asDecimal($value));
		$value = '123456';
		$this->assertSame("123,456", $this->formatter->asDecimal($value));
		$value = '-123456.123';
		$this->assertSame("-123,456.123", $this->formatter->asDecimal($value));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asDecimal(null));
	}

	public function testAsPercent()
	{
		$value = '123';
		$this->assertSame('12,300%', $this->formatter->asPercent($value));
		$value = '0.1234';
		$this->assertSame("12%", $this->formatter->asPercent($value));
		$value = '-0.009343';
		$this->assertSame("-1%", $this->formatter->asPercent($value));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asPercent(null));
	}

	public function testAsScientific()
	{
		$value = '123';
		$this->assertSame('1.23E2', $this->formatter->asScientific($value));
		$value = '123456';
		$this->assertSame("1.23456E5", $this->formatter->asScientific($value));
		$value = '-123456.123';
		$this->assertSame("-1.23456123E5", $this->formatter->asScientific($value));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asScientific(null));
	}

	public function testAsCurrency()
	{
		$value = '123';
		$this->assertSame('$123.00', $this->formatter->asCurrency($value));
		$value = '123.456';
		$this->assertSame("$123.46", $this->formatter->asCurrency($value));
		// Starting from ICU 52.1, negative currency value will be formatted as -$123,456.12
		// see: http://source.icu-project.org/repos/icu/icu/tags/release-52-1/source/data/locales/en.txt
//		$value = '-123456.123';
//		$this->assertSame("($123,456.12)", $this->formatter->asCurrency($value));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asCurrency(null));
	}

	public function testDate()
	{
		$time = time();
		$this->assertSame(date('n/j/y', $time), $this->formatter->asDate($time));
		$this->assertSame(date('F j, Y', $time), $this->formatter->asDate($time, 'long'));
		$this->assertSame($this->formatter->nullDisplay, $this->formatter->asDate(null));
	}
}
