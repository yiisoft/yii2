<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use yii\base\BaseObject;
use yii\http\ServerRequestAttributeTrait;
use yiiunit\TestCase;

class ServerRequestAttributeTraitTest extends TestCase
{
    public function testSetupAttributes()
    {
        $storage = new TestServerRequestAttributeStorage();

        $storage->setAttributes(['some' => 'foo']);
        $this->assertSame(['some' => 'foo'], $storage->getAttributes());
    }

    /**
     * @depends testSetupAttributes
     */
    public function testGetAttribute()
    {
        $storage = new TestServerRequestAttributeStorage();

        $storage->setAttributes(['some' => 'foo']);

        $this->assertSame('foo', $storage->getAttribute('some'));
        $this->assertSame(null, $storage->getAttribute('un-existing'));
        $this->assertSame('default', $storage->getAttribute('un-existing', 'default'));
    }

    /**
     * @depends testSetupAttributes
     */
    public function testModifyAttributes()
    {
        $storage = new TestServerRequestAttributeStorage();

        $storage->setAttributes(['attr1' => '1']);

        $newStorage = $storage->withAttribute('attr2', '2');
        $this->assertNotSame($newStorage, $storage);
        $this->assertSame(['attr1' => '1', 'attr2' => '2'], $newStorage->getAttributes());

        $storage = $newStorage;
        $newStorage = $storage->withoutAttribute('attr1');
        $this->assertNotSame($newStorage, $storage);
        $this->assertSame(['attr2' => '2'], $newStorage->getAttributes());
    }
}

class TestServerRequestAttributeStorage extends BaseObject
{
    use ServerRequestAttributeTrait;
}