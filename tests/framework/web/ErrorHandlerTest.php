<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use yii\BaseYii;
use yii\base\ErrorException;
use yii\web\Application;
use Yii;
use yii\web\ErrorHandlerRenderEvent;
use yii\web\NotFoundHttpException;
use yii\web\View;
use yiiunit\TestCase;

/**
 * Unit test for {@see \yii\web\ErrorHandler}.
 */
#[Group('web')]
#[Group('error-handler')]
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

    public function testCorrectResponseCodeInErrorView(): void
    {
        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();
        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new NotFoundHttpException('This message is displayed to end user')]);
        ob_get_clean();
        $out = Yii::$app->response->data;
        $this->assertEqualsWithoutLE('Code: 404
Message: This message is displayed to end user
Exception: yii\web\NotFoundHttpException', $out);
    }

    public function testFormatRaw(): void
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_RAW;

        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new Exception('Test Exception')]);
        $out = ob_get_clean();

        $this->assertStringContainsString('Test Exception', $out);

        $this->assertTrue(is_string(Yii::$app->response->data));
        $this->assertStringContainsString(
            "Exception 'Exception' with message 'Test Exception'",
            Yii::$app->response->data,
        );
    }

    public function testFormatXml(): void
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_XML;

        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new Exception('Test Exception')]);
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

    public function testClearAssetFilesInErrorView(): void
    {
        Yii::$app->getView()->registerJsFile('somefile.js');
        /** @var ErrorHandler $handler */
        $handler = Yii::$app->getErrorHandler();
        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [new Exception('Some Exception')]);
        ob_get_clean();
        $out = Yii::$app->response->data;
        $this->assertEquals("Exception View\n", $out);
    }

    public function testClearAssetFilesInErrorActionView(): void
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

    public function testAfterRenderEventCanModifyOutput(): void
    {
        $handler = Yii::$app->getErrorHandler();

        $exception = new Exception('Some Exception');

        $actualException = null;

        $handler->on(
            ErrorHandler::EVENT_AFTER_RENDER,
            static function (ErrorHandlerRenderEvent $event) use (&$actualException): void {
                $actualException = $event->exception;
                $event->output .= "\n<!--after-render-->";
            }
        );

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [$exception]);
        ob_get_clean();

        self::assertSame(
            $exception,
            $actualException,
            "Exception passed to the 'afterRender' event should be the same as the one rendered.",
        );
        self::assertStringContainsString(
            '<!--after-render-->',
            Yii::$app->response->data,
            "Output modified in the 'afterRender' event should be present in the response.",
        );
    }

    public function testAfterRenderEventCanModifyOutputInErrorActionView(): void
    {
        $handler = Yii::$app->getErrorHandler();

        $handler->errorAction = 'test/error';

        $exception = new NotFoundHttpException('Resource not found');

        $actualException = null;

        $handler->on(
            ErrorHandler::EVENT_AFTER_RENDER,
            static function (ErrorHandlerRenderEvent $event) use (&$actualException): void {
                $actualException = $event->exception;
                $event->output .= "\n<!--after-render-error-action-->";
            }
        );

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [$exception]);
        ob_get_clean();

        self::assertSame(
            $exception,
            $actualException,
            "Exception passed to the 'afterRender' event should be the same as the one rendered."
        );
        self::assertStringContainsString(
            '<!--after-render-error-action-->',
            Yii::$app->response->data,
            "Output modified in the 'afterRender' event should be present in the response."
        );
    }

    public function testAfterRenderEventCanModifyOutputForPhpErrors(): void
    {
        $handler = Yii::$app->getErrorHandler();

        $exception = new ErrorException('PHP Warning', E_WARNING, E_WARNING, __FILE__, __LINE__);

        $handler->exception = $exception;

        $handler->on(
            ErrorHandler::EVENT_AFTER_RENDER,
            static function (ErrorHandlerRenderEvent $event): void {
                $event->output .= "\n<!--php-error-after-render-->";
            }
        );

        ob_start(); // suppress response output
        $this->invokeMethod($handler, 'renderException', [$exception]);
        ob_get_clean();

        self::assertStringContainsString(
            '<!--php-error-after-render-->',
            Yii::$app->response->data,
            "Output modified in the 'afterRender' event should be present in the response."
        );
    }

    public function testRenderCallStackItem(): void
    {
        $handler = Yii::$app->getErrorHandler();
        $handler->traceLine = '<a href="netbeans://open?file={file}&line={line}">{html}</a>';
        $file = BaseYii::getAlias('@yii/web/Application.php');

        $out = $handler->renderCallStackItem($file, 63, Application::class, null, null, null);

        $this->assertStringContainsString('<a href="netbeans://open?file=' . $file . '&line=63">', $out);
    }

    public static function dataHtmlEncode(): array
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
                '&apos;hello world&apos;',
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
    public function testHtmlEncode(string $text, string $expected): void
    {
        $handler = Yii::$app->getErrorHandler();

        $this->assertSame($expected, $handler->htmlEncode($text));
    }

    public function testHtmlEncodeWithUnicodeSequence(): void
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
