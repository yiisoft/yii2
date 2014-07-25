<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yiiunit\framework\helpers;

use yii\helpers\BaseHtml;
use yii\base\Model;
use yiiunit\data\base\Singer;
use yii\base\InvalidParamException;
use yiiunit\TestCase;

/**
 * TestCase for BaseHtml
 */
class BaseHtmlTest extends TestCase {
    
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
    
    public function testGetInputName() {
        $model = new Singer();
        $this->assertSame("Singer[0][a]", BaseHtml::getInputName($model, "[0]a"));
        $this->assertSame("Singer[0][a][0]", BaseHtml::getInputName($model, "[0]a[0]"));
        // TODO add test for $model->formName === ''
    }
    
    public function testGetAttributeValue() {
        // TODO implement tests
    }
    
    public function testGetAttributeName() {
        $this->assertSame("asdf.asdfa", BaseHtml::getAttributeName("asd]asdf.asdfa[asdfa"));
        $this->assertSame("a", BaseHtml::getAttributeName("a"));
        $this->assertSame("a", BaseHtml::getAttributeName("[0]a"));
        $this->assertSame("a", BaseHtml::getAttributeName("a[0]"));
        $this->assertSame("a", BaseHtml::getAttributeName("[0]a[0]"));
        $this->assertSame("a.", BaseHtml::getAttributeName("[0]a.[0]"));
        // unicode checks only work if PCRE is compiled with --enable-unicode-properties
        $this->assertSame("ä", BaseHtml::getAttributeName("ä"));
        $this->assertSame("öáöio..,", BaseHtml::getAttributeName("asdf]öáöio..,[asdfasdf"));
        $this->assertSame("test.ööößß.d", BaseHtml::getAttributeName("[0]test.ööößß.d"));
        $this->assertSame("ИІК", BaseHtml::getAttributeName("ИІК"));
        $this->assertSame("ИІК", BaseHtml::getAttributeName("]ИІК["));
        $this->assertSame("ИІК", BaseHtml::getAttributeName("[0]ИІК[0]"));
        // test non word characters and expect exception
        $this->setExpectedException('yii\base\InvalidParamException');
        BaseHtml::getAttributeName("....");
    }
}
