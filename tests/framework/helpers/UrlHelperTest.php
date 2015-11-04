<?php
namespace yiiunit\framework\helpers;

use yii\helpers\Url;
use yiiunit\TestCase;

/**
 * StringHelperTest
 *
 * @group helpers
 */
class UrlHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testCustomHttpBuildQuery()
    {
        $query = [
            'page'     => 1,
            'Message'  => [
                'language' => [
                    'one[1]',
                    'two[]',
                    'three'
                ],
            ],
            'per-page' => '30'
        ];
        $equal = 'page=1&Message%5Blanguage%5D%5B%5D=one%5B1%5D&Message%5Blanguage%5D%5B%5D=two%5B%5D&Message%5Blanguage%5D%5B%5D=three&per-page=30';
        $this->assertEquals($equal, Url::custom_http_build_query($query));
    }

}
