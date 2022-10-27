<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\filters\HttpCache;

/**
 * @group filters
 */
class HttpCacheTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->mockWebApplication();
    }

    public function testDisabled()
    {
        $httpCache = new HttpCache();
        $this->assertTrue($httpCache->beforeAction(null));
        $httpCache->enabled = false;
        $this->assertTrue($httpCache->beforeAction(null));
    }

    public function testEmptyPragma()
    {
        $httpCache = new HttpCache();
        $httpCache->etagSeed = function ($action, $params) {
            return '';
        };
        $httpCache->beforeAction(null);
        $response = Yii::$app->getResponse();
        $this->assertFalse($response->getHeaders()->offsetExists('Pragma'));
        $this->assertNotSame($response->getHeaders()->get('Pragma'), '');
    }

    /**
     * @covers \yii\filters\HttpCache::validateCache
     */
    public function testValidateCache()
    {
        $httpCache = new HttpCache();
        $request = Yii::$app->getRequest();

        $method = new \ReflectionMethod($httpCache, 'validateCache');
        $method->setAccessible(true);

        $request->headers->remove('If-Modified-Since');
        $request->headers->remove('If-None-Match');
        $this->assertFalse($method->invoke($httpCache, null, null));
        $this->assertFalse($method->invoke($httpCache, 0, null));
        $this->assertFalse($method->invoke($httpCache, 0, '"foo"'));

        $request->headers->set('If-Modified-Since', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $this->assertTrue($method->invoke($httpCache, 0, null));
        $this->assertFalse($method->invoke($httpCache, 1, null));

        $request->headers->set('If-None-Match', '"foo"');
        $this->assertTrue($method->invoke($httpCache, 0, '"foo"'));
        $this->assertFalse($method->invoke($httpCache, 0, '"foos"'));
        $this->assertTrue($method->invoke($httpCache, 1, '"foo"'));
        $this->assertFalse($method->invoke($httpCache, 1, '"foos"'));
        $this->assertFalse($method->invoke($httpCache, null, null));

        $request->headers->set('If-None-Match', '*');
        $this->assertFalse($method->invoke($httpCache, 0, '"foo"'));
        $this->assertFalse($method->invoke($httpCache, 0, null));
    }

    /**
     * @covers \yii\filters\HttpCache::generateEtag
     */
    public function testGenerateEtag()
    {
        $httpCache = new HttpCache();
        $httpCache->weakEtag = false;

        $httpCache->etagSeed = function ($action, $params) {
            return null;
        };
        $httpCache->beforeAction(null);
        $response = Yii::$app->getResponse();
        $this->assertFalse($response->getHeaders()->offsetExists('ETag'));

        $httpCache->etagSeed = function ($action, $params) {
            return '';
        };
        $httpCache->beforeAction(null);
        $response = Yii::$app->getResponse();

        $this->assertTrue($response->getHeaders()->offsetExists('ETag'));

        $etag = $response->getHeaders()->get('ETag');
        $this->assertStringStartsWith('"', $etag);
        $this->assertStringEndsWith('"', $etag);


        $httpCache->weakEtag = true;
        $httpCache->beforeAction(null);
        $response = Yii::$app->getResponse();

        $this->assertTrue($response->getHeaders()->offsetExists('ETag'));

        $etag = $response->getHeaders()->get('ETag');
        $this->assertStringStartsWith('W/"', $etag);
        $this->assertStringEndsWith('"', $etag);
    }
}
