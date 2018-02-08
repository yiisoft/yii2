<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use yii\i18n\Locale;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class LocaleTest extends TestCase
{
    /**
     * @var Locale
     */
    protected $locale;

    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication([
            'timeZone' => 'UTC',
            'language' => 'ru-RU',
        ]);
        $this->locale = new Locale(['locale' => 'en-US']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->locale = null;
    }

    public function testGetCurrencyCode()
    {
        $this->locale->locale = 'de-DE';
        $this->assertSame('€', $this->locale->getCurrencySymbol('EUR'));
        $this->assertSame('€', $this->locale->getCurrencySymbol());

        $this->locale->locale = 'ru-RU';
        $this->assertIsOneOf($this->locale->getCurrencySymbol('RUR'), ['р.', '₽', 'руб.']);
        $this->assertIsOneOf($this->locale->getCurrencySymbol(), ['р.', '₽', 'руб.']);
    }
}
