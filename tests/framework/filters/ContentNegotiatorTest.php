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
    protected function setUp(): void
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

    public function testWhenFormatGETParamIsArray()
    {
        $this->expectException(yii\web\BadRequestHttpException::class);
        $this->expectExceptionMessageRegExp("|Invalid data received for GET parameter '.+'|");

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

    public function testVaryHeader()
    {
        list($action, $filter) = $this->mockActionAndFilter();
        $filter->formats = [];
        $filter->languages = [];
        $filter->beforeAction($action);
        $this->assertFalse($filter->response->getHeaders()->has('Vary'));

        list($action, $filter) = $this->mockActionAndFilter();
        $filter->formats = ['application/json' => Response::FORMAT_JSON];
        $filter->languages = ['en'];
        $filter->beforeAction($action);
        $this->assertFalse($filter->response->getHeaders()->has('Vary'));  // There is still nothing to vary

        list($action, $filter) = $this->mockActionAndFilter();
        $filter->formats = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_XML,
        ];
        $filter->languages = [];
        $filter->beforeAction($action);
        $this->assertContains('Accept', $filter->response->getHeaders()->get('Vary', [], false));

        list($action, $filter) = $this->mockActionAndFilter();
        $filter->formats = [];
        $filter->languages = ['en', 'de'];
        $filter->beforeAction($action);
        $this->assertContains('Accept-Language', $filter->response->getHeaders()->get('Vary', [], false));

        list($action, $filter) = $this->mockActionAndFilter();
        $filter->formats = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_XML,
        ];
        $filter->languages = ['en', 'de'];
        $filter->beforeAction($action);
        $varyHeader = $filter->response->getHeaders()->get('Vary', [], false);
        $this->assertContains('Accept', $varyHeader);
        $this->assertContains('Accept-Language', $varyHeader);
    }
}
