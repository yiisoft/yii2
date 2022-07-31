<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\HeaderCollection;
use yiiunit\TestCase;

/**
 * @group web
 */
class HeaderCollectionTest extends TestCase
{
    public function testFromArray()
    {
        $headerCollection = new HeaderCollection();
        $location = 'my-test-location';
        $headerCollection->fromArray([
            'Location' => [$location],
        ]);
        $this->assertEquals($location, $headerCollection->get('Location'));
    }

    public function testSetter()
    {
        $headerCollection = new HeaderCollection();

        $this->assertSame('default', $headerCollection->get('X-Header', 'default'));
        $this->assertFalse($headerCollection->has('X-Header'));

        $headerCollection->set('X-Header', '1');
        $this->assertTrue($headerCollection->has('X-Header'));
        $this->assertTrue($headerCollection->offsetExists('X-Header'));
        $this->assertSame('1', $headerCollection->get('X-Header'));
        $this->assertSame(['1'], $headerCollection->get('X-Header', null, false));
        $this->assertTrue($headerCollection->has('x-header'));
        $this->assertSame('1', $headerCollection->get('x-header'));
        $this->assertSame(['1'], $headerCollection->get('x-header', null, false));
        $this->assertSame('1', $headerCollection->get('x-hEadER'));
        $this->assertSame(['1'], $headerCollection->get('x-hEadER', null, false));
        $this->assertSame(['x-header' => ['1']], $headerCollection->toArray());
        $this->assertSame(['X-Header' => ['1']], $headerCollection->toOriginalArray());

        $headerCollection->set('X-HEADER', '2');
        $this->assertSame('2', $headerCollection->get('X-Header'));
        $this->assertSame('2', $headerCollection->get('x-header'));
        $this->assertSame('2', $headerCollection->get('x-hEadER'));
        $this->assertSame(['x-header' => ['2']], $headerCollection->toArray());
        $this->assertSame(['X-HEADER' => ['2']], $headerCollection->toOriginalArray());

        $headerCollection->offsetSet('X-HEADER', '3');
        $this->assertSame('3', $headerCollection->get('X-Header'));
    }

    public function testSetterDefault()
    {
        $headerCollection = new HeaderCollection();
        $headerCollection->setDefault('X-Header', '1');
        $this->assertSame(['1'], $headerCollection->get('X-Header', null, false));

        $headerCollection->setDefault('X-Header', '2');
        $this->assertSame(['1'], $headerCollection->get('X-Header', null, false));
    }

    public function testAdder()
    {
        $headerCollection = new HeaderCollection();
        $headerCollection->add('X-Header', '1');
        $this->assertSame('1', $headerCollection->get('X-Header'));
        $this->assertSame(['1'], $headerCollection->get('X-Header', null, false));
        $this->assertSame('1', $headerCollection->get('x-header'));
        $this->assertSame(['1'], $headerCollection->get('x-header', null, false));
        $this->assertSame('1', $headerCollection->get('x-hEadER'));
        $this->assertSame(['1'], $headerCollection->get('x-hEadER', null, false));
        $this->assertSame(['x-header' => ['1']], $headerCollection->toArray());
        $this->assertSame(['X-Header' => ['1']], $headerCollection->toOriginalArray());

        $headerCollection->add('X-HEADER', '2');
        $this->assertSame('1', $headerCollection->get('X-Header'));
        $this->assertSame('1', $headerCollection->get('x-header'));
        $this->assertSame('1', $headerCollection->get('x-hEadER'));
        $this->assertSame(['1', '2'], $headerCollection->get('x-header', null, false));
        $this->assertSame(['x-header' => ['1', '2']], $headerCollection->toArray());
        $this->assertSame(['X-Header' => ['1', '2']], $headerCollection->toOriginalArray());
    }

    public function testRemover()
    {
        $headerCollection = new HeaderCollection();
        $headerCollection->add('X-Header', '1');
        $this->assertSame(1, $headerCollection->count());
        $this->assertSame(1, $headerCollection->getCount());
        $headerCollection->remove('X-Header');
        $this->assertSame(0, $headerCollection->count());

        $headerCollection->add('X-Header', '1');
        $this->assertSame(1, $headerCollection->count());
        $headerCollection->remove('x-header');
        $this->assertSame(0, $headerCollection->count());

        $headerCollection->add('X-Header', '1');
        $headerCollection->offsetUnset('X-HEADER');
        $this->assertSame(0, $headerCollection->count());

        $headerCollection->add('X-Header-1', '1');
        $headerCollection->add('X-Header-2', '1');
        $this->assertSame(2, $headerCollection->count());
        $headerCollection->removeAll();
        $this->assertSame(0, $headerCollection->count());
    }
}
