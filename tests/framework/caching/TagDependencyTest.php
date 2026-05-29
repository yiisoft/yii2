<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\ArrayCache;
use yii\caching\FileCache;
use yii\caching\TagDependency;
use yiiunit\TestCase;

/**
 * @group caching
 */
class TagDependencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TagDependency::resetReusableData();
    }

    private function createCountingCache()
    {
        return new class extends ArrayCache {
            public $readKeys = [];

            protected function getValue($key)
            {
                $this->readKeys[] = $key;
                return parent::getValue($key);
            }
        };
    }

    public function testInvalidate(): void
    {
        $cache = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);

        // single tag test
        $cache->set('a1', 11, 0, new TagDependency(['tags' => 't1']));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => 't2']));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't1');
        $this->assertFalse($cache->get('a1'));
        $this->assertFalse($cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't2');
        $this->assertFalse($cache->get('a1'));
        $this->assertFalse($cache->get('a2'));
        $this->assertFalse($cache->get('b1'));
        $this->assertFalse($cache->get('b2'));

        // multiple tag test
        $cache->set('a1', 11, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't1');
        $this->assertFalse($cache->get('a1'));
        $this->assertFalse($cache->get('a2'));
        $this->assertFalse($cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't2');
        $this->assertFalse($cache->get('a1'));
        $this->assertFalse($cache->get('a2'));
        $this->assertFalse($cache->get('b1'));
        $this->assertFalse($cache->get('b2'));

        $cache->set('a1', 11, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, ['t1', 't2']);
        $this->assertFalse($cache->get('a1'));
        $this->assertFalse($cache->get('a2'));
        $this->assertFalse($cache->get('b1'));
        $this->assertFalse($cache->get('b2'));
    }

    public function testReusableTagTimestampIsFetchedOncePerRequest(): void
    {
        $cache = $this->createCountingCache();
        $dependency = new TagDependency(['tags' => 't1', 'reusable' => true]);
        $dependency->evaluateDependency($cache);

        TagDependency::resetReusableData();
        $cache->readKeys = [];
        $dependency->isChanged($cache);
        $dependency->isChanged($cache);

        $this->assertCount(1, $cache->readKeys);
    }

    public function testNonReusableTagTimestampIsFetchedEachTime(): void
    {
        $cache = $this->createCountingCache();
        $dependency = new TagDependency(['tags' => 't1']);
        $dependency->evaluateDependency($cache);

        $cache->readKeys = [];
        $dependency->isChanged($cache);
        $dependency->isChanged($cache);

        $this->assertCount(2, $cache->readKeys);
    }

    public function testReusableDependencyDetectsInvalidationWithinRequest(): void
    {
        $cache = new ArrayCache();
        $cache->set('a', 'value', 0, new TagDependency(['tags' => 't1', 'reusable' => true]));

        $this->assertSame('value', $cache->get('a'));

        TagDependency::invalidate($cache, 't1');

        $this->assertFalse($cache->get('a'));
    }
}
