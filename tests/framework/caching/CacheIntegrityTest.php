<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\Cache;
use yii\caching\Dependency;
use yii\caching\ExpressionDependency;
use yiiunit\TestCase;
/**
 * In-memory cache backend using the default serializer, for testing [[Cache::$integrityKey]].
 */
class IntegrityTestCache extends Cache
{
    public $store = [];

    protected function getValue($key)
    {
        return $this->store[$key] ?? false;
    }

    protected function setValue($key, $value, $duration)
    {
        $this->store[$key] = $value;

        return true;
    }

    protected function addValue($key, $value, $duration)
    {
        if (isset($this->store[$key])) {
            return false;
        }
        $this->store[$key] = $value;

        return true;
    }

    protected function deleteValue($key)
    {
        unset($this->store[$key]);

        return true;
    }

    protected function flushValues()
    {
        $this->store = [];

        return true;
    }
}

/**
 * Tests for the payload integrity protection of [[yii\caching\Cache]].
 */
class CacheIntegrityTest extends TestCase
{
    /**
     * @return IntegrityTestCache
     */
    private function createCache()
    {
        $cache = new IntegrityTestCache();
        $cache->integrityKey = 'unit-test-integrity-key-0123456789abcdef';

        return $cache;
    }

    public function testGetSetRoundTrip()
    {
        $cache = $this->createCache();
        $cache->set('k', ['hello' => 'world']);
        $this->assertSame(['hello' => 'world'], $cache->get('k'));
        $this->assertSame(['hello' => 'world'], $cache->multiGet(['k'])['k']);
        $this->assertTrue($cache->exists('k'));
    }

    public function testDependencyRoundTrip()
    {
        $cache = $this->createCache();
        $dependency = new MutableDependency();
        $cache->set('k', 'value', 0, $dependency);
        $this->assertSame('value', $cache->get('k'));

        $dependency->evaluateDependency($cache);
        MutableDependency::$sourceValue = 2;
        $this->assertFalse($cache->get('k'));
    }

    public function testTamperedPayloadIsTreatedAsMissing()
    {
        $cache = $this->createCache();
        $cache->set('k', 'original');
        $key = $cache->buildKey('k');
        $raw = $cache->store[$key];

        $cache->store[$key] = substr($raw, 0, 60) . 'X' . substr($raw, 61);
        $this->assertFalse($cache->get('k'));
        $this->assertFalse($cache->multiGet(['k'])['k']);

        $cache->store[$key] = $raw . 'tail';
        $this->assertFalse($cache->get('k'));
    }

    public function testSignedEntryCopiedToAnotherKeyIsRejected()
    {
        $cache = $this->createCache();
        $cache->set('legit', 'value');

        $cache->store[$cache->buildKey('other')] = $cache->store[$cache->buildKey('legit')];
        $this->assertFalse($cache->get('other'));
        $this->assertSame('value', $cache->get('legit'));
    }

    public function testSerializerFalseIsNotSigned()
    {
        $cache = $this->createCache();
        $cache->serializer = false;
        $cache->set('k', 'plain-string');
        $this->assertSame('plain-string', $cache->get('k'));
        $this->assertSame('plain-string', $cache->multiGet(['k'])['k']);
        // foreign values are returned as-is, matching the documented no-integrity behavior
        $cache->store[$cache->buildKey('f')] = 'foreign';
        $this->assertSame('foreign', $cache->get('f'));
    }

    public function testUnsignedPayloadIsRejected()
    {
        $cache = $this->createCache();
        $cache->store[$cache->buildKey('k')] = serialize(['unsigned', null]);
        $this->assertFalse($cache->get('k'));
    }

    public function testPoisonedDependencyPayloadDoesNotEvaluate()
    {
        $cache = $this->createCache();
        $evilDependency = new ExpressionDependency();
        $evilDependency->expression = "throw new \\Exception('payload executed')";
        $cache->store[$cache->buildKey('k')] = serialize(['poisoned', $evilDependency]);

        $this->assertFalse($cache->get('k'));
        $this->assertFalse($cache->multiGet(['k'])['k']);
    }

    public function testCustomSerializerWithIntegrityKey()
    {
        $cache = $this->createCache();
        $cache->serializer = [
            function ($value) {
                return base64_encode(json_encode($value));
            },
            function ($value) {
                return json_decode(base64_decode($value), true);
            },
        ];
        $cache->set('k', ['a' => 1]);
        $this->assertSame(['a' => 1], $cache->get('k'));

        $cache->store[$cache->buildKey('k')] = base64_encode(json_encode(['tampered' => true]));
        $this->assertFalse($cache->get('k'));
    }

    public function testDisabledIntegrityKeyKeepsDefaultBehavior()
    {
        $cache = new IntegrityTestCache();
        $cache->set('k', 'plain');
        $key = $cache->buildKey('k');

        $this->assertStringStartsWith('a:2:', $cache->store[$key]);
        $this->assertSame('plain', $cache->get('k'));

        $cache->store[$key] = serialize(['foreign', null]);
        $this->assertSame('foreign', $cache->get('k'));
    }
}

/**
 * Dependency whose generated data can be changed externally after being attached to a cache entry.
 */
class MutableDependency extends Dependency
{
    /**
     * @var int external data source returned by [[generateDependencyData()]].
     */
    public static $sourceValue = 1;

    protected function generateDependencyData($cache)
    {
        return self::$sourceValue;
    }
}
