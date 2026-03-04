<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\caching;

use yii\caching\DummyCache;
use yii\caching\ExpressionDependency;
use yiiunit\TestCase;

class DummyCacheTest extends TestCase
{
    /**
     * @var DummyCache
     */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->cache = new DummyCache();
    }

    public function testGetAlwaysReturnsFalse(): void
    {
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->get('key'));
    }

    public function testSetReturnsTrue(): void
    {
        $this->assertTrue($this->cache->set('key', 'value'));
    }

    public function testAddReturnsTrue(): void
    {
        $this->assertTrue($this->cache->add('key', 'value'));
    }

    public function testDeleteReturnsTrue(): void
    {
        $this->assertTrue($this->cache->delete('key'));
    }

    public function testFlushReturnsTrue(): void
    {
        $this->assertTrue($this->cache->flush());
    }

    public function testSetWithDurationStillReturnsFalseOnGet(): void
    {
        $this->cache->set('key', 'value', 3600);
        $this->assertFalse($this->cache->get('key'));
    }

    public function testMultipleSetsThenGet(): void
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);
        $this->assertFalse($this->cache->get('a'));
        $this->assertFalse($this->cache->get('b'));
    }

    public function testMultiGet(): void
    {
        $this->cache->set('k1', 'v1');
        $this->cache->set('k2', 'v2');

        $result = $this->cache->multiGet(['k1', 'k2', 'k3']);
        $this->assertFalse($result['k1']);
        $this->assertFalse($result['k2']);
        $this->assertFalse($result['k3']);
    }

    public function testMultiSet(): void
    {
        $this->cache->multiSet(['k1' => 'v1', 'k2' => 'v2']);
        $this->assertFalse($this->cache->get('k1'));
        $this->assertFalse($this->cache->get('k2'));
    }

    public function testMultiAdd(): void
    {
        $this->cache->multiAdd(['k1' => 'v1', 'k2' => 'v2']);
        $this->assertFalse($this->cache->get('k1'));
        $this->assertFalse($this->cache->get('k2'));
    }

    public function testExists(): void
    {
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->exists('key'));
    }

    public function testArrayAccess(): void
    {
        $this->cache['key'] = 'value';
        $this->assertFalse(isset($this->cache['key']));
        $this->assertFalse($this->cache['key']);
    }

    public function testGetOrSetAlwaysCallsCallable(): void
    {
        $callCount = 0;
        $callable = function () use (&$callCount) {
            $callCount++;
            return 'generated';
        };

        $result1 = $this->cache->getOrSet('key', $callable);
        $result2 = $this->cache->getOrSet('key', $callable);

        $this->assertSame('generated', $result1);
        $this->assertSame('generated', $result2);
        $this->assertSame(2, $callCount);
    }

    public function testSetWithDependency(): void
    {
        $dependency = new ExpressionDependency(['expression' => '1 + 1']);

        $this->assertTrue($this->cache->set('key', 'value', 0, $dependency));
        $this->assertFalse($this->cache->get('key'));
    }

    public function testKeyPrefix(): void
    {
        $this->cache->keyPrefix = 'test_prefix_';
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->get('key'));
    }

    public function testCanBeUsedAsCacheComponent(): void
    {
        $this->mockApplication([
            'components' => [
                'cache' => [
                    'class' => DummyCache::class,
                ],
            ],
        ]);

        $cache = \Yii::$app->cache;
        $this->assertInstanceOf(DummyCache::class, $cache);
        $this->assertTrue($cache->set('test', 'value'));
        $this->assertFalse($cache->get('test'));
    }

    public function testDefaultDuration(): void
    {
        $this->cache->defaultDuration = 3600;
        $this->assertTrue($this->cache->set('key', 'value'));
        $this->assertFalse($this->cache->get('key'));
    }

    public function testAddAfterSet(): void
    {
        $this->cache->set('key', 'original');
        $this->assertTrue($this->cache->add('key', 'new'));
    }
}
