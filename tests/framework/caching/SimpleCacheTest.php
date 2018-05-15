<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use DateInterval;
use yii\caching\SimpleCache;
use yiiunit\TestCase;

/**
 * @group caching
 */
class SimpleCacheTest extends TestCase
{
    /**
     * @var SimpleCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;


    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cache = $this->getMockBuilder(SimpleCache::class)->getMockForAbstractClass();
    }

    /**
     * Data provider for [[testNormalizeTtl()]]
     * @return array test data.
     */
    public function dataProviderNormalizeTtl()
    {
        return [
            [123, 123],
            ['123', 123],
            [null, 9999],
            [0, 0],
            [new DateInterval('PT6H8M'), (6 * 3600 + 8 * 60)],
            [new DateInterval('P2Y4D'), (2 * 365 * 24 * 3600 + 4 * 24 * 3600)],
        ];
    }

    /**
     * @dataProvider dataProviderNormalizeTtl
     *
     * @covers \yii\caching\SimpleCache::normalizeTtl()
     *
     * @param mixed $ttl
     * @param int $expectedResult
     */
    public function testNormalizeTtl($ttl, $expectedResult)
    {
        $this->cache->defaultTtl = 9999;
        $this->assertEquals($expectedResult, $this->invokeMethod($this->cache, 'normalizeTtl', [$ttl]));
    }
}