<?php

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

        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";

        $this->mockWebApplication();
    }

    public function testDisabled()
    {
        $httpCache = new HttpCache;
        $this->assertTrue($httpCache->beforeAction(null));
        $httpCache->enabled=false;
        $this->assertTrue($httpCache->beforeAction(null));
    }

    /**
     * @covers yii\filters\HttpCache::validateCache
     */
    public function testValidateCache()
    {
        $httpCache = new HttpCache;
        $method = new \ReflectionMethod($httpCache, 'validateCache');
        $method->setAccessible(true);

        unset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['HTTP_IF_NONE_MATCH']);
        $this->assertTrue($method->invoke($httpCache, null, null));
        $this->assertFalse($method->invoke($httpCache, 0, null));
        $this->assertFalse($method->invoke($httpCache, 0, '"foo"'));

        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Thu, 01 Jan 1970 00:00:00 GMT';
        $this->assertTrue($method->invoke($httpCache, 0, null));
        $this->assertFalse($method->invoke($httpCache, 1, null));

        $_SERVER['HTTP_IF_NONE_MATCH'] = '"foo"';
        $this->assertTrue($method->invoke($httpCache, 0, '"foo"'));
        $this->assertFalse($method->invoke($httpCache, 0, '"foos"'));
        $this->assertTrue($method->invoke($httpCache, 1, '"foo"'));
        $this->assertFalse($method->invoke($httpCache, 1, '"foos"'));
        $this->assertFalse($method->invoke($httpCache, null, null));
    }

    /**
     * @covers yii\filters\HttpCache::generateEtag
     */
    public function testGenerateEtag()
    {
        $httpCache = new HttpCache;
        $httpCache->etagSeed = function($action, $params) {
            return '';
        };
        $httpCache->beforeAction(null);
        $response = Yii::$app->getResponse();

        $this->assertTrue($response->getHeaders()->offsetExists('ETag'));

        $etag = $response->getHeaders()->get('ETag');
        $this->assertStringStartsWith('"', $etag);
        $this->assertStringEndsWith('"', $etag);
    }
}
