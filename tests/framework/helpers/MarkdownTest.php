<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\Markdown;
use yiiunit\TestCase;

/**
 * Description of MarkdownTest.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @group helpers
 */
class MarkdownTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testOriginalFlavor()
    {
        $text = <<<'TEXT'
html
new line 1

new line 2
TEXT;

        Markdown::$defaultFlavor = 'original';
        $this->assertEquals(Markdown::process($text), Markdown::process($text, 'original'));

        Markdown::$defaultFlavor = 'gfm-comment';
        $this->assertNotEquals(Markdown::process($text), Markdown::process($text, 'original'));
        $this->assertEquals(Markdown::process($text), Markdown::process($text, 'gfm-comment'));
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     * @expectedExceptionMessage Markdown flavor 'undefined' is not defined.
     */
    public function testProcessInvalidParamException()
    {
        Markdown::process('foo', 'undefined');
    }

    public function testProcessParagraph()
    {
        $actual = Markdown::processParagraph('foo');
        $expected = 'foo';
        $this->assertEquals($expected, $actual);
    }
}
