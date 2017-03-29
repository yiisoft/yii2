<?php
namespace yiiunit\framework\helpers;

use yii\helpers\SecurityHelper;
use yiiunit\TestCase;

/**
 * SecurityHelperTest
 * @group helpers
 */
class SecurityHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * @dataProvider maskProvider
     */
    public function testMasking($unmaskedToken)
    {
        $this->assertEquals($unmaskedToken, SecurityHelper::unmaskToken(SecurityHelper::maskToken($unmaskedToken)));
    }

    public function maskProvider() {
        return [
            ['SimpleToken'],
            ['Token with special characters: %d1    5"'],
            ["Token with UTF8 character: †"]
        ];
    }
}
