<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\NotAcceptableHttpException;
use yii\web\Response;
use yiiunit\TestCase;

class ContentNegotiatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->mockWebApplication();
    }

    public function testNegotiateContentType()
    {
        $filter = new ContentNegotiator([
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ]);

        Yii::$app->request->setAcceptableContentTypes(['application/json' => ['q' => 1, 'version' => '1.0']]);
        $filter->negotiate();
        $this->assertSame('json', Yii::$app->response->format);

        $this->expectException(NotAcceptableHttpException::class);
        Yii::$app->request->setAcceptableContentTypes(['application/xml' => ['q' => 1, 'version' => '2.0']]);
        $filter->negotiate();
    }
}