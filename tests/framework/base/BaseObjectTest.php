<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\base;

use yii\base\BaseObject;
use yiiunit\TestCase;

/**
 * @group base
 */
class BaseObjectTest extends TestCase
{
    /**
     * @var NewObject
     */
    protected $object;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->object = new NewObject();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->object = null;
    }

    public function testHasProperty(): void
    {
        $this->assertTrue($this->object->hasProperty('Text'));
        $this->assertTrue($this->object->hasProperty('text'));
        $this->assertFalse($this->object->hasProperty('Caption'));
        $this->assertTrue($this->object->hasProperty('content'));
        $this->assertFalse($this->object->hasProperty('content', false));
        $this->assertFalse($this->object->hasProperty('Content'));
    }

    public function testCanGetProperty(): void
    {
        $this->assertTrue($this->object->canGetProperty('Text'));
        $this->assertTrue($this->object->canGetProperty('text'));
        $this->assertFalse($this->object->canGetProperty('Caption'));
        $this->assertTrue($this->object->canGetProperty('content'));
        $this->assertFalse($this->object->canGetProperty('content', false));
        $this->assertFalse($this->object->canGetProperty('Content'));
    }

    public function testCanSetProperty(): void
    {
        $this->assertTrue($this->object->canSetProperty('Text'));
        $this->assertTrue($this->object->canSetProperty('text'));
        $this->assertFalse($this->object->canSetProperty('Object'));
        $this->assertFalse($this->object->canSetProperty('Caption'));
        $this->assertTrue($this->object->canSetProperty('content'));
        $this->assertFalse($this->object->canSetProperty('content', false));
        $this->assertFalse($this->object->canSetProperty('Content'));
    }

    public function testGetProperty(): void
    {
        $this->assertSame('default', $this->object->Text);
        $this->expectException('yii\base\UnknownPropertyException');
        $value2 = $this->object->Caption;
    }

    public function testSetProperty(): void
    {
        $value = 'new value';
        $this->object->Text = $value;
        $this->assertEquals($value, $this->object->Text);
        $this->expectException('yii\base\UnknownPropertyException');
        $this->object->NewMember = $value;
    }

    public function testSetReadOnlyProperty(): void
    {
        $this->expectException('yii\base\InvalidCallException');
        $this->object->object = 'test';
    }

    public function testIsset(): void
    {
        $this->assertTrue(isset($this->object->Text));
        $this->assertNotEmpty($this->object->Text);

        $this->object->Text = '';
        $this->assertTrue(isset($this->object->Text));
        $this->assertEmpty($this->object->Text);

        $this->object->Text = null;
        $this->assertFalse(isset($this->object->Text));
        $this->assertEmpty($this->object->Text);

        $this->assertFalse(isset($this->object->unknownProperty));
        $isEmpty = empty($this->object->unknownProperty);
        $this->assertTrue($isEmpty);
    }

    public function testUnset(): void
    {
        unset($this->object->Text);
        $this->assertFalse(isset($this->object->Text));
        $this->assertEmpty($this->object->Text);
    }

    public function testUnsetReadOnlyProperty(): void
    {
        $this->expectException('yii\base\InvalidCallException');
        unset($this->object->object);
    }

    public function testCallUnknownMethod(): void
    {
        $this->expectException('yii\base\UnknownMethodException');
        $this->object->unknownMethod();
    }

    public function testArrayProperty(): void
    {
        $this->assertEquals([], $this->object->items);
        // the following won't work
        /*
        $this->object->items[] = 1;
        $this->assertEquals([1], $this->object->items);
        */
    }

    public function testObjectProperty(): void
    {
        $this->assertInstanceOf(NewObject::class, $this->object->object);
        $this->assertEquals('object text', $this->object->object->text);
        $this->object->object->text = 'new text';
        $this->assertEquals('new text', $this->object->object->text);
    }

    public function testConstruct(): void
    {
        $object = new NewObject(['text' => 'test text']);
        $this->assertEquals('test text', $object->getText());
    }

    public function testGetClassName(): void
    {
        $object = $this->object;
        $this->assertSame(get_class($object), $object::className());
    }

    public function testReadingWriteOnlyProperty(): void
    {
        $this->expectException('yii\base\InvalidCallException');
        $this->expectExceptionMessage('Getting write-only property: yiiunit\framework\base\NewObject::writeOnly');
        $this->object->writeOnly;
    }
}


class NewObject extends BaseObject
{
    private $_object = null;
    private $_text = 'default';
    private $_items = [];
    public $content;

    public function getText()
    {
        return $this->_text;
    }

    public function setText($value): void
    {
        $this->_text = $value;
    }

    public function getObject()
    {
        if (!$this->_object) {
            $this->_object = new self();
            $this->_object->_text = 'object text';
        }

        return $this->_object;
    }

    public function getExecute()
    {
        return function ($param) {
            return $param * 2;
        };
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function setWriteOnly()
    {
    }
}
