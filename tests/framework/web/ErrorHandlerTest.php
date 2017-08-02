<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\NotFoundHttpException;
use yiiunit\TestCase;

class ErrorHandlerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'errorHandler' => [
                    'class' => 'yiiunit\framework\web\ErrorHandler',
                    'errorView' => '@yiiunit/data/views/errorHandler.php',
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
