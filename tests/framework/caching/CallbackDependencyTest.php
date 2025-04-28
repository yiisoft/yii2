<?php

namespace yiiunit\framework\caching;

use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\caching\CallbackDependency;

class CallbackDependencyTest extends TestCase
{
    public function testDependencyChange()
    {
        $cache = new ArrayCache();
        $dependencyValue = true;

        $dependency = new CallbackDependency();
        $dependency->callback = function () use (&$dependencyValue) {
            return $dependencyValue === true;
        };

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));

        $dependencyValue = false;
        $this->assertTrue($dependency->isChanged($cache));
    }

    public function testDependencyNotChanged()
    {
        $cache = new ArrayCache();

        $dependency = new CallbackDependency();
        $dependency->callback = function () {
            return 2 + 2;
        };

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));
        $this->assertFalse($dependency->isChanged($cache));
    }
}
