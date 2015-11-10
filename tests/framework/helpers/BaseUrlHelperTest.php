<?php
namespace yiiunit\framework\helpers;

use yii\helpers\BaseUrl;
use yiiunit\TestCase;

/**
 * StringHelperTest
 *
 * @group helpers
 */
class BaseUrlHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
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
        $this->assertEquals($equal, BaseUrl::customHttpBuildQuery($query));
    }
}
