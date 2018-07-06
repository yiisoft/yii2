<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;
use yiiunit\TestCase;

/**
 *  @group filters
 */
class ContentNegotiatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function mockActionAndFilter()
    {
        $action = new Action('test', new Controller('id', Yii::$app));
        $filter = new ContentNegotiator([
            'request' => new Request(),
            'response' => new Response(),
        ]);

        return [$action, $filter];
    }

    public function testWhenLanguageGETParamIsArray()
    {
        list($action, $filter) = $this->mockActionAndFilter();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET[$filter->languageParam] = [
            'foo',
            'string-index' => 'bar',
        ];

        $targetLanguage = 'de';
        $filter->languages = [$targetLanguage, 'ru', 'en'];

        $filter->beforeAction($action);
        $this->assertEquals($targetLanguage, Yii::$app->language);
    }

    /**
     * @expectedException yii\web\BadRequestHttpException
     * @expectedExceptionMessageRegExp |Invalid data received for GET parameter '.+'|
     */
    public function testWhenFormatGETParamIsArray()
    {
        list($action, $filter) = $this->mockActionAndFilter();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET[$filter->formatParam] = [
            'format-A',
            'string-index' => 'format-B',
        ];

        $filter->formats = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_XML,
        ];

        $filter->beforeAction($action);
    }
}
