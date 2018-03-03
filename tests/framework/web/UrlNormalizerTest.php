<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlNormalizer;
use yii\web\UrlNormalizerRedirectException;
use yiiunit\TestCase;

/**
 * @group web
 */
class UrlNormalizerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testNormalizePathInfo()
    {
        $normalizer = new UrlNormalizer();
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', '/a/'));
        $this->assertEquals('post/123', $normalizer->normalizePathInfo('post//123//', '/a'));
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', '/'));
        $this->assertEquals('post/123', $normalizer->normalizePathInfo('post//123//', ''));

        $normalizer->collapseSlashes = false;
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', '/a/'));
        $this->assertEquals('post//123', $normalizer->normalizePathInfo('post//123//', '/a'));
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', '/'));
        $this->assertEquals('post//123', $normalizer->normalizePathInfo('post//123//', ''));

        $normalizer->normalizeTrailingSlash = false;
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', '/a/'));
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', '/a'));
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', '/'));
        $this->assertEquals('post//123//', $normalizer->normalizePathInfo('post//123//', ''));

        $normalizer->collapseSlashes = true;
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', '/a/'));
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', '/a'));
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', '/'));
        $this->assertEquals('post/123/', $normalizer->normalizePathInfo('post//123//', ''));
    }

    public function testNormalizeRoute()
    {
        $normalizer = new UrlNormalizer();
        $route = ['site/index', ['id' => 1, 'name' => 'test']];

        // 404 error as default action
        $normalizer->action = UrlNormalizer::ACTION_NOT_FOUND;
        $expected = new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        try {
            $result = $normalizer->normalizeRoute($route);
            $this->fail('Expected throwing NotFoundHttpException');
        } catch (NotFoundHttpException $exc) {
            $this->assertEquals($expected, $exc);
        }

        // 301 redirect as default action
        $normalizer->action = UrlNormalizer::ACTION_REDIRECT_PERMANENT;
        $expected = new UrlNormalizerRedirectException([$route[0]] + $route[1], 301);
        try {
            $result = $normalizer->normalizeRoute($route);
            $this->fail('Expected throwing UrlNormalizerRedirectException');
        } catch (UrlNormalizerRedirectException $exc) {
            $this->assertEquals($expected, $exc);
        }

        // 302 redirect as default action
        $normalizer->action = UrlNormalizer::ACTION_REDIRECT_TEMPORARY;
        $expected = new UrlNormalizerRedirectException([$route[0]] + $route[1], 302);
        try {
            $result = $normalizer->normalizeRoute($route);
            $this->fail('Expected throwing UrlNormalizerRedirectException');
        } catch (UrlNormalizerRedirectException $exc) {
            $this->assertEquals($expected, $exc);
        }

        // no action
        $normalizer->action = null;
        $this->assertEquals($route, $normalizer->normalizeRoute($route));

        // custom callback which modifies the route
        $normalizer->action = function ($route, $normalizer) {
            $route[0] = 'site/redirect';
            $route['normalizeTrailingSlash'] = $normalizer->normalizeTrailingSlash;
            return $route;
        };
        $expected = $route;
        $expected[0] = 'site/redirect';
        $expected['normalizeTrailingSlash'] = $normalizer->normalizeTrailingSlash;
        $this->assertEquals($expected, $normalizer->normalizeRoute($route));

        // custom callback which throw custom 404 error
        $normalizer->action = function ($route, $normalizer) {
            throw new NotFoundHttpException('Custom error message.');
        };
        $expected = new NotFoundHttpException('Custom error message.');
        try {
            $result = $normalizer->normalizeRoute($route);
            $this->fail('Expected throwing NotFoundHttpException');
        } catch (NotFoundHttpException $exc) {
            $this->assertEquals($expected, $exc);
        }
    }

    /**
     * Test usage of UrlNormalizer in UrlManager.
     *
     * Trailing slash is insignificant if normalizer is enabled.
     */
    public function testUrlManager()
    {
        $config = [
            'enablePrettyUrl' => true,
            'cache' => null,
            'normalizer' => [
                '__class' => \yii\web\UrlNormalizer::class,
                'action' => null,
            ],
        ];
        $request = new Request();

        // pretty URL without rules
        $config['rules'] = [];
        $manager = new UrlManager($config);
        $request->pathInfo = '/module/site/index/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index', []], $result);

        // pretty URL with rules
        $config['rules'] = [
            [
                'pattern' => 'post/<id>/<title>',
                'route' => 'post/view',
            ],
        ];
        $manager = new UrlManager($config);
        $request->pathInfo = 'post/123/this+is+sample/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
    }
}
