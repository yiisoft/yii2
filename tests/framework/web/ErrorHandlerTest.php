<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\View;
use yiiunit\TestCase;

class ErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'controllerNamespace' => 'yiiunit\\data\\controllers',
            'components' => [
                'errorHandler' => [
                    'class' => 'yiiunit\framework\web\ErrorHandler',
                    'errorView' => '@yiiunit/data/views/errorHandler.php',
                    'exceptionView' => '@yiiunit/data/views/errorHandlerForAssetFiles.php',
                ],
            ],
        ]);
    }

    public function testCorrectResponseCodeInErrorView()
    {
        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();
        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new NotFoundHttpException('This message is displayed to end user')]);
        ob_get_clean();
        $out = Yii::$app->response->data;
        $this->assertEquals('Code: 404
Message: This message is displayed to end user
Exception: yii\web\NotFoundHttpException', $out);
    }

    public function testFormatRaw()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_RAW;

        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new \Exception('Test Exception')]);
        $out = ob_get_clean();

        $this->assertStringContainsString('Test Exception', $out);

        $this->assertTrue(is_string(Yii::$app->response->data));
        $this->assertStringContainsString(
            "Exception 'Exception' with message 'Test Exception'",
            Yii::$app->response->data
        );
    }

    public function testFormatXml()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_XML;

        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new \Exception('Test Exception')]);
        $out = ob_get_clean();

        $this->assertStringContainsString('Test Exception', $out);

        $outArray = Yii::$app->response->data;

        $this->assertTrue(is_array(Yii::$app->response->data));

        $this->assertEquals('Exception', $outArray['name']);
        $this->assertEquals('Test Exception', $outArray['message']);
        $this->assertArrayHasKey('code', $outArray);
        $this->assertEquals('Exception', $outArray['type']);
        $this->assertStringContainsString('ErrorHandlerTest.php', $outArray['file']);
        $this->assertArrayHasKey('stack-trace', $outArray);
        $this->assertArrayHasKey('line', $outArray);
    }

    public function testClearAssetFilesInErrorView()
    {
        Yii::$app->getView()->registerJsFile('somefile.js');
        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();
        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new \Exception('Some Exception')]);
        ob_get_clean();
        $out = Yii::$app->response->data;
        $this->assertEquals('Exception View
', $out);
    }

    public function testClearAssetFilesInErrorActionView()
    {
        Yii::$app->getErrorHandler()->errorAction = 'test/error';
        Yii::$app->getView()->registerJs("alert('hide me')", View::POS_END);

        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();
        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new NotFoundHttpException()]);
        ob_get_clean();
        $out = Yii::$app->response->data;
        $this->assertStringNotContainsString('<script', $out);
    }

    public function testRenderCallStackItem()
    {
        $handler = Yii::$app->getErrorHandler();
        $handler->traceLine = '<a href="netbeans://open?file={file}&line={line}">{html}</a>';
        $file = \yii\BaseYii::getAlias('@yii/web/Application.php');

        $out = $handler->renderCallStackItem($file, 63, \yii\web\Application::className(), null, null, null);

        $this->assertStringContainsString('<a href="netbeans://open?file=' . $file . '&line=63">', $out);
    }

    public function dataHtmlEncode()
    {
        return [
            [
                "a \t=<>&\"'\x80`\n",
                "a \t=&lt;&gt;&amp;&quot;&apos;�`\n",
            ],
            [
                '<b>test</b>',
                '&lt;b&gt;test&lt;/b&gt;',
            ],
            [
                '"hello"',
                '&quot;hello&quot;',
            ],
            [
                "'hello world'",
                "&apos;hello world&apos;",
            ],
            [
                'Chip&amp;Dale',
                'Chip&amp;amp;Dale',
            ],
            [
                "\t\$x=24;",
                "\t\$x=24;",
            ],
        ];
    }

    /**
     * @dataProvider dataHtmlEncode
     */
    public function testHtmlEncode($text, $expected)
    {
        $handler = Yii::$app->getErrorHandler();

        $this->assertSame($expected, $handler->htmlEncode($text));
    }

    public function testHtmlEncodeWithUnicodeSequence()
    {
        $handler = Yii::$app->getErrorHandler();

        $text = "a \t=<>&\"'\x80\u{20bd}`\u{000a}\u{000c}\u{0000}";
        $expected = "a \t=&lt;&gt;&amp;&quot;&apos;�₽`\n\u{000c}\u{0000}";

        $this->assertSame($expected, $handler->htmlEncode($text));
    }
}

class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * @return bool if simple HTML should be rendered
     */
    protected function shouldRenderSimpleHtml()
    {
        return false;
    }
}
