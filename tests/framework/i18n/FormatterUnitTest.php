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

    public function testAsLength()
    {
        $this->assertSame("53 milimetry", $this->formatter->asLength(0.053));
        $this->assertSame("0,99 centrymetra", $this->formatter->asLength(0.099));
        $this->assertSame("0,12 metra", $this->formatter->asLength(0.123));
    }
}
