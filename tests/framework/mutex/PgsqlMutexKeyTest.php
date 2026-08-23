<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\db\Connection;
use yii\mutex\PgsqlMutex;
use yiiunit\TestCase;

/**
 * Unit tests for PgsqlMutex lock name to key mapping that do not require a live PostgreSQL server.
 *
 * @group mutex
 * @group db
 * @group pgsql
 */
class PgsqlMutexKeyTest extends TestCase
{
    /**
     * @return PgsqlMutex
     */
    private function createMutex()
    {
        return new PgsqlMutex(['db' => new Connection(['driverName' => 'pgsql'])]);
    }

    /**
     * @param string $name
     * @return array
     */
    private function getKeys($name)
    {
        $mutex = $this->createMutex();
        $method = new \ReflectionMethod(PgsqlMutex::class, 'getKeysFromName');
        $method->setAccessible(true);

        return $method->invoke($mutex, $name);
    }

    public function testKeysAreDeterministic()
    {
        $this->assertSame($this->getKeys('app:job:send-mail'), $this->getKeys('app:job:send-mail'));
    }

    public function testKnownVector()
    {
        $this->assertSame([1132950929, -43075096], $this->getKeys('app:job:send-mail'));
    }

    public function testKeysFitSigned32BitRange()
    {
        for ($i = 0; $i < 500; ++$i) {
            foreach ($this->getKeys('name-' . $i) as $key) {
                $this->assertGreaterThanOrEqual(-2147483648, $key);
                $this->assertLessThanOrEqual(2147483647, $key);
            }
        }
    }

    public function testDistinctNamesProduceDistinctKeyPairs()
    {
        $pairs = [];
        for ($i = 0; $i < 500; ++$i) {
            $pairs[implode(',', $this->getKeys('lock-name-' . $i))] = true;
        }
        $this->assertCount(500, $pairs);
    }
}
