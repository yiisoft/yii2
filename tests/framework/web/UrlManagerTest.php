<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\Request;
use yii\web\UrlManager;
use yiiunit\TestCase;

/**
 * This tests verifies all features provided by UrlManager according to the documentation.
 *
 * UrlManager has two main operation modes:
 *
 * - "default" url format, which is the simple case. Tests in this class cover this case.
 *   Things to be covered in this mode are the following:
 *    - route only createUrl(['post/index']);
 *    - with params createUrl(['post/view', 'id' => 100]);
 *    - with anchor createUrl(['post/view', 'id' => 100, '#' => 'content']);
 *   Variations here are createUrl and createAbsoluteUrl, where absolute Urls also vary by schema.
 *
 * - "pretty" url format. This is the complex case, which involves UrlRules and url parsing.
 *   Url creation for "pretty" url format is covered by [[UrlManagerCreateUrlTest]].
 *   Url parsing for "pretty" url format is covered by [[UrlManagerParseUrlTest]].
 *
 * @group web
 */
class UrlManagerTest extends TestCase
{
    protected function getUrlManager($config = [], $showScriptName = true, $enableStrictParsing = false)
    {
        // in this test class, all tests have enablePrettyUrl disabled.
        $config['enablePrettyUrl'] = false;
        $config['cache'] = null;

        // baseUrl should not be used when prettyUrl is disabled
        // trigger an exception here in case it gets called
        $config['baseUrl'] = null;
        $this->mockApplication();
        Yii::$app->set('request', function () {
            $this->fail('Request component should not be accessed by UrlManager with current settings.');
        });

        // set default values if they are not set
        $config = array_merge([
            'scriptUrl' => '/index.php',
            'hostInfo' => 'http://www.example.com',
            'showScriptName' => $showScriptName,
            'enableStrictParsing' => $enableStrictParsing,
        ], $config);

        return new UrlManager($config);
    }

    /**
     * $showScriptName and $enableStrictParsing should have no effect in default format.
     * Passing these options ensures that.
     */
    public function ignoredOptionsProvider()
    {
        return [
            [false, false],
            [true,  false],
            [false, true],
            [true,  true],
        ];
    }

    /**
     * @dataProvider ignoredOptionsProvider
     */
    public function testCreateUrlSimple($showScriptName, $enableStrictParsing)
    {
        // default setting with '/' as base url
        $manager = $this->getUrlManager([], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl('post/view');
        $this->assertEquals('/index.php?r=post%2Fview', $url);
        $url = $manager->createUrl(['post/view']);
        $this->assertEquals('/index.php?r=post%2Fview', $url);

        // default setting with '/test/' as base url
        $manager = $this->getUrlManager([
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
        ], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl('post/view');
        $this->assertEquals('/test?r=post%2Fview', $url);
        $url = $manager->createUrl(['post/view']);
        $this->assertEquals('/test?r=post%2Fview', $url);
    }

    /**
     * @dataProvider ignoredOptionsProvider
     */
    public function testCreateUrlWithParams($showScriptName, $enableStrictParsing)
    {
        // default setting with '/' as base url
        $manager = $this->getUrlManager([], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/index.php?r=post%2Fview&id=1&title=sample+post', $url);

        // default setting with '/test/' as base url
        $manager = $this->getUrlManager([
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
        ], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test?r=post%2Fview&id=1&title=sample+post', $url);
    }

    /**
     * @dataProvider ignoredOptionsProvider
     *
     * @see https://github.com/yiisoft/yii2/pull/9596
     */
    public function testCreateUrlWithAnchor($showScriptName, $enableStrictParsing)
    {
        // default setting with '/' as base url
        $manager = $this->getUrlManager([], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl(['post/view', '#' => 'anchor']);
        $this->assertEquals('/index.php?r=post%2Fview#anchor', $url);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post', '#' => 'anchor']);
        $this->assertEquals('/index.php?r=post%2Fview&id=1&title=sample+post#anchor', $url);

        // default setting with '/test/' as base url
        $manager = $this->getUrlManager([
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
        ], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl(['post/view', '#' => 'anchor']);
        $this->assertEquals('/test?r=post%2Fview#anchor', $url);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post', '#' => 'anchor']);
        $this->assertEquals('/test?r=post%2Fview&id=1&title=sample+post#anchor', $url);
    }

    /**
     * @dataProvider ignoredOptionsProvider
     */
    public function testCreateAbsoluteUrl($showScriptName, $enableStrictParsing)
    {
        $manager = $this->getUrlManager([], $showScriptName, $enableStrictParsing);
        $url = $manager->createAbsoluteUrl('post/view');
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);
        $url = $manager->createAbsoluteUrl(['post/view']);
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);

        $url = $manager->createAbsoluteUrl('post/view', true);
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);
        $url = $manager->createAbsoluteUrl(['post/view'], true);
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);

        $url = $manager->createAbsoluteUrl('post/view', 'http');
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);
        $url = $manager->createAbsoluteUrl(['post/view'], 'http');
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview', $url);

        $url = $manager->createAbsoluteUrl('post/view', 'https');
        $this->assertEquals('https://www.example.com/index.php?r=post%2Fview', $url);
        $url = $manager->createAbsoluteUrl(['post/view'], 'https');
        $this->assertEquals('https://www.example.com/index.php?r=post%2Fview', $url);

        $url = $manager->createAbsoluteUrl('post/view', '');
        $this->assertEquals('//www.example.com/index.php?r=post%2Fview', $url);
        $url = $manager->createAbsoluteUrl(['post/view'], '');
        $this->assertEquals('//www.example.com/index.php?r=post%2Fview', $url);

        $manager->hostInfo = 'https://www.example.com';
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('https://www.example.com/index.php?r=post%2Fview&id=1&title=sample+post', $url);

        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'https');
        $this->assertEquals('https://www.example.com/index.php?r=post%2Fview&id=1&title=sample+post', $url);

        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'http');
        $this->assertEquals('http://www.example.com/index.php?r=post%2Fview&id=1&title=sample+post', $url);

        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], '');
        $this->assertEquals('//www.example.com/index.php?r=post%2Fview&id=1&title=sample+post', $url);
    }

    /**
     * Test normalisation of different routes.
     * @dataProvider ignoredOptionsProvider
     */
    public function testCreateUrlRouteVariants($showScriptName, $enableStrictParsing)
    {
        // default setting with '/' as base url
        $manager = $this->getUrlManager([], $showScriptName, $enableStrictParsing);
        $url = $manager->createUrl(['/post/view']);
        $this->assertEquals('/index.php?r=post%2Fview', $url);
        $url = $manager->createUrl(['/post/view/']);
        $this->assertEquals('/index.php?r=post%2Fview', $url);
        $url = $manager->createUrl(['/module/post/view']);
        $this->assertEquals('/index.php?r=module%2Fpost%2Fview', $url);
        $url = $manager->createUrl(['/post/view/']);
        $this->assertEquals('/index.php?r=post%2Fview', $url);
    }


    /**
     * @return array provides different names for UrlManager::$routeParam
     */
    public function routeParamProvider()
    {
        return [
            ['r'], // default value
            ['route'],
            ['_'],
        ];
    }

    /**
     * @dataProvider routeParamProvider
     */
    public function testParseRequest($routeParam)
    {
        $manager = $this->getUrlManager(['routeParam' => $routeParam]);
        $request = new Request();

        // default setting without 'r' param
        $request->setQueryParams([]);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);

        // default setting with 'r' param
        $request->setQueryParams([$routeParam => 'site/index']);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);

        // default setting with 'r' param as an array
        $request->setQueryParams([$routeParam => ['site/index']]);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);

        // other parameters are not returned here
        $request->setQueryParams([$routeParam => 'site/index', 'id' => 5]);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        $this->assertEquals(5, $request->getQueryParam('id'));
    }
}
