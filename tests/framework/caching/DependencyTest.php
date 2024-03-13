<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\Cache;
use yii\caching\Dependency;
use yiiunit\data\cache\MockDependency;
use yiiunit\TestCase;

/**
 * Dependency (abstract) tests.
 * @group caching
 * @author Boudewijn Vahrmeijer <vahrmeijer@gmail.com>
 * @since 2.0.11
 */
class DependencyTest extends TestCase
{
    public function testResetReusableData()
    {
        $value = ['dummy'];
        $dependency = new MockDependency();
        $this->setInaccessibleProperty($dependency, '_reusableData', $value, false);
        $this->assertEquals($value, $this->getInaccessibleProperty($dependency, '_reusableData'));

        $dependency->resetReusableData();

        $this->assertEquals([], $this->getInaccessibleProperty($dependency, '_reusableData'));
    }

    public function testGenerateReusableHash()
    {
        $dependency = $this->getMockForAbstractClass(Dependency::className());
        $dependency->data = 'dummy';

        $result = $this->invokeMethod($dependency, 'generateReusableHash');
        $this->assertEquals(5, strlen($dependency->data));
        $this->assertEquals(40, strlen($result));
    }

    public function testIsChanged()
    {
        $dependency = $this->getMockForAbstractClass(Dependency::className());
        $cache = $this->getMockForAbstractClass(Cache::className());

        $result = $dependency->isChanged($cache);
        $this->assertFalse($result);

        $dependency->data = 'changed';
        $result = $dependency->isChanged($cache);
        $this->assertTrue($result);
    }
}
