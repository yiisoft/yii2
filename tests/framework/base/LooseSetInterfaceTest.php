<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\BaseObject;
use yii\base\LooseSetInterface;
use yiiunit\TestCase;

/**
 * @group base
 */
class LooseSetInterfaceTest extends TestCase
{
    /**
     * @var NewLooseObject
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->object = new NewLooseObject();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->object = null;
    }

    public function testCanSetNewProperty()
    {
        $this->assertFalse(isset($this->object->NewMember));
        $this->object->NewMember = 'new value';
        $this->assertTrue(isset($this->object->NewMember));
        $this->assertSame('new value', $this->object->NewMember);
    }

    public function testUnsetNewProperty()
    {
        $this->object->NewMember = 'new value';
        unset($this->object->NewMember);
        $this->assertFalse(isset($this->object->NewMember));
        $isEmpty = empty($this->object->NewMember);
        $this->assertTrue($isEmpty);
    }

    public function testHasProperty()
    {
        $this->assertTrue($this->object->hasProperty('Text'));
        $this->assertTrue($this->object->hasProperty('text'));
        $this->assertFalse($this->object->hasProperty('Caption'));
        $this->assertTrue($this->object->hasProperty('content'));
        $this->assertFalse($this->object->hasProperty('content', false));
        $this->assertFalse($this->object->hasProperty('Content'));
    }

    public function testCanGetProperty()
    {
        $this->assertTrue($this->object->canGetProperty('Text'));
        $this->assertTrue($this->object->canGetProperty('text'));
        $this->assertFalse($this->object->canGetProperty('Caption'));
        $this->assertTrue($this->object->canGetProperty('content'));
        $this->assertFalse($this->object->canGetProperty('content', false));
        $this->assertFalse($this->object->canGetProperty('Content'));
    }

    public function testCanSetProperty()
    {
        $this->assertTrue($this->object->canSetProperty('Text'));
        $this->assertTrue($this->object->canSetProperty('text'));
        $this->assertFalse($this->object->canSetProperty('Object'));
        $this->assertFalse($this->object->canSetProperty('Caption'));
        $this->assertTrue($this->object->canSetProperty('content'));
        $this->assertFalse($this->object->canSetProperty('content', false));
        $this->assertFalse($this->object->canSetProperty('Content'));
    }

    public function testGetProperty()
    {
        $this->assertSame('default', $this->object->Text);
        $this->expectException('yii\base\UnknownPropertyException');
        $value2 = $this->object->Caption;
    }

    public function testSetProperty()
    {
        $value = 'new value';
        $this->object->Text = $value;
        $this->assertEquals($value, $this->object->Text);
    }

    public function testSetReadOnlyProperty()
    {
        $this->expectException('yii\base\InvalidCallException');
        $this->object->object = 'test';
    }

    public function testIsset()
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

    public function testUnset()
    {
        unset($this->object->Text);
        $this->assertFalse(isset($this->object->Text));
        $this->assertEmpty($this->object->Text);
    }

    public function testUnsetReadOnlyProperty()
    {
        $this->expectException('yii\base\InvalidCallException');
        unset($this->object->object);
    }

    public function testArrayProperty()
    {
        $this->assertEquals([], $this->object->items);
        // the following won't work
        /*
        $this->object->items[] = 1;
        $this->assertEquals([1], $this->object->items);
        */
    }

    public function testObjectProperty()
    {
        $this->assertInstanceOf(NewLooseObject::class, $this->object->object);
        $this->assertEquals('object text', $this->object->object->text);
        $this->object->object->text = 'new text';
        $this->assertEquals('new text', $this->object->object->text);
    }

    public function testReadingWriteOnlyProperty()
    {
        $this->expectException('yii\base\InvalidCallException');
        $this->expectExceptionMessage('Getting write-only property: yiiunit\framework\base\NewLooseObject::writeOnly');
        $this->object->writeOnly;
    }
}


class NewLooseObject extends BaseObject implements LooseSetInterface
{
    private $_object = null;
    private $_text = 'default';
    private $_items = [];
    public $content;

    public function getText()
    {
        return $this->_text;
    }

    public function setText($value)
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

