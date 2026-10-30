<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\caching\FileCache;
use yii\web\View;
use yiiunit\TestCase;

/**
 * @group web
 */
class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRegisterJsVar(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $view = new View();
        $view->registerJsVar('username', 'samdark');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<script>var username = "samdark";</script></head>', $html);

        $view = new View();
        $view->registerJsVar(
            'objectTest',
            [
                'number' => 42,
                'question' => 'Unknown',
            ]
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script>var objectTest = {"number":42,"question":"Unknown"};</script></head>',
            $html
        );
    }

    public function testRegisterNoscriptTag(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        // default position is POS_HEAD and `$options` is optional
        $view = new View();

        $view->registerNoscriptTag('<link rel="stylesheet" href="/css/no-js.css">');

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringContainsString(
            '<noscript><link rel="stylesheet" href="/css/no-js.css"></noscript></head>',
            $html,
            'The noscript tag should be rendered at the end of the head section by default, with `$options` omitted.'
        );

        // the content is rendered as is, HTML attributes are encoded
        $view = new View();

        $view->registerNoscriptTag(
            '<img src="/pixel.gif" alt="">',
            ['class' => 'pixel', 'data-id' => 'a&b'],
            View::POS_BEGIN,
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertContainsWithoutLE(
            '<body>' . PHP_EOL . '<noscript class="pixel" data-id="a&amp;b"><img src="/pixel.gif" alt=""></noscript>',
            $html,
            'The noscript tag should be rendered right after the opening body tag with its content as is and its attributes encoded.'
        );

        $view = new View();

        $view->registerNoscriptTag(
            '<img src="/pixel.gif" alt="">',
            [],
            View::POS_END,
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringContainsString(
            '<noscript><img src="/pixel.gif" alt=""></noscript></body>',
            $html,
            'The noscript tag should be rendered at the end of the body section when registered with POS_END.'
        );

        // without a key tags are appended, with the same key the latter overwrites the former
        $view = new View();

        $view->registerNoscriptTag(
            '<img src="/first.gif" alt="">',
            [],
            View::POS_BEGIN,
        );
        $view->registerNoscriptTag(
            '<img src="/second.gif" alt="">',
            [],
            View::POS_BEGIN,
        );
        $view->registerNoscriptTag(
            '<img src="/old.gif" alt="">',
            [],
            View::POS_BEGIN,
            'pixel',
        );
        $view->registerNoscriptTag(
            '<img src="/new.gif" alt="">',
            [],
            View::POS_BEGIN,
            'pixel',
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertContainsWithoutLE(
            <<<HTML
            <noscript><img src="/first.gif" alt=""></noscript>
            <noscript><img src="/second.gif" alt=""></noscript>
            <noscript><img src="/new.gif" alt=""></noscript>
            HTML,
            $html,
            'Noscript tags registered without a key should be appended in registration order, and the latter tag registered with the same key should overwrite the former.',
        );
        $this->assertStringNotContainsString(
            '/old.gif',
            $html,
            'The noscript tag overwritten by a later registration with the same key should not be rendered.'
        );

        // the same key at different positions does not overwrite
        $view = new View();

        $view->registerNoscriptTag(
            '<img src="/head.gif" alt="">',
            [],
            View::POS_HEAD,
            'pixel',
        );
        $view->registerNoscriptTag(
            '<img src="/end.gif" alt="">',
            [],
            View::POS_END,
            'pixel',
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringContainsString(
            '<noscript><img src="/head.gif" alt=""></noscript></head>',
            $html,
            'The noscript tag registered with a key at POS_HEAD should not be overwritten by the same key at another position.'
        );
        $this->assertStringContainsString(
            '<noscript><img src="/end.gif" alt=""></noscript></body>',
            $html,
            'The noscript tag registered with a key at POS_END should not be overwritten by the same key at another position.'
        );
        $this->assertSame(
            [],
            $view->noscriptTags,
            'Registered noscript tags should be cleared after the page has been rendered.',
        );
    }

    public function testRegisterLdJson(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $ldJson = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => 'Yii tutorial',
            'url' => 'https://example.com/videos/yii-tutorial',
        ];
        $expected = '<script type="application/ld+json">{"@context":"https:\/\/schema.org","@type":"VideoObject","name":"Yii tutorial","url":"https:\/\/example.com\/videos\/yii-tutorial"}</script>';

        // default position is POS_HEAD
        $view = new View();

        $view->registerLdJson($ldJson);

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringContainsString(
            $expected . '</head>',
            $html,
            'The JSON-LD script tag should be rendered at the end of the head section by default.'
        );

        $view = new View();

        $view->registerLdJson(
            $ldJson,
            View::POS_BEGIN,
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertContainsWithoutLE(
            '<body>' . PHP_EOL . $expected,
            $html,
            'The JSON-LD script tag should be rendered right after the opening body tag when registered with POS_BEGIN.'
        );

        $view = new View();

        $view->registerLdJson(
            $ldJson,
            View::POS_END,
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringContainsString(
            $expected . '</body>',
            $html,
            'The JSON-LD script tag should be rendered at the end of the body section when registered with POS_END.'
        );

        // without a key tags are appended, with the same key the latter overwrites the former
        $view = new View();

        $view->registerLdJson(['@type' => 'First']);
        $view->registerLdJson(['@type' => 'Second']);
        $view->registerLdJson(
            ['@type' => 'Old'],
            View::POS_HEAD,
            'organization',
        );
        $view->registerLdJson(
            ['@type' => 'New'],
            View::POS_HEAD,
            'organization',
        );

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertContainsWithoutLE(
            <<<HTML
            <script type="application/ld+json">{"@type":"First"}</script>
            <script type="application/ld+json">{"@type":"Second"}</script>
            <script type="application/ld+json">{"@type":"New"}</script></head>
            HTML,
            $html,
            'Each JSON-LD registration without a key should render its own script tag in registration order, and the latter registered with the same key should overwrite the former.',
        );
        $this->assertStringNotContainsString(
            '"Old"',
            $html,
            'The JSON-LD script tag overwritten by a later registration with the same key should not be rendered.'
        );
        $this->assertSame(
            [],
            $view->ldJson,
            'Registered JSON-LD script tags should be cleared after the page has been rendered.',
        );
    }

    public function testRegisterLdJsonIsHtmlSafe(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $view = new View();

        $view->registerLdJson([
            '@type' => 'Product',
            'description' => '</script><script>alert(1)</script>',
            'name' => 'Tom & "Jerry"',
        ]);

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertStringNotContainsString(
            '</script><script>alert(1)</script>',
            $html,
            'A closing script tag inside the JSON-LD data should not break out of the script tag.'
        );
        $this->assertStringContainsString(
            '<script type="application/ld+json">{"@type":"Product","description":"\u003C\/script\u003E\u003Cscript\u003Ealert(1)\u003C\/script\u003E","name":"Tom \u0026 \u0022Jerry\u0022"}</script>',
            $html,
            'The JSON-LD data should be encoded with `Json::htmlEncode()`, escaping `<`, `>`, `&` and `"` as unicode sequences.'
        );
    }

    public function testRegisterNoscriptTagAndLdJsonRenderingOrder(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $view = new View();

        $view->registerMetaTag(['name' => 'robots', 'content' => 'noindex']);
        $view->registerLinkTag(['rel' => 'icon', 'href' => '/favicon.ico']);
        $view->registerCssFile('/css/site.css');

        foreach ([View::POS_HEAD, View::POS_BEGIN, View::POS_END] as $position) {
            $view->registerJsFile("/js/$position.js", ['position' => $position]);
            $view->registerLdJson(['@type' => 'Thing', 'position' => $position], $position);
            $view->registerNoscriptTag("<img src=\"/$position.gif\" alt=\"\">", [], $position);
        }

        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);

        $this->assertContainsWithoutLE(
            <<<HTML
            <meta name="robots" content="noindex">
            <link href="/favicon.ico" rel="icon">
            <noscript><img src="/1.gif" alt=""></noscript>
            <script type="application/ld+json">{"@type":"Thing","position":1}</script>
            <link href="/css/site.css" rel="stylesheet">
            <script src="/js/1.js"></script></head>
            HTML,
            $html,
            'In the head section noscript and JSON-LD script tags should be rendered after the meta and link tags and before the CSS and JS files.',
        );
        $this->assertContainsWithoutLE(
            <<<HTML
            <body>
            <noscript><img src="/2.gif" alt=""></noscript>
            <script type="application/ld+json">{"@type":"Thing","position":2}</script>
            <script src="/js/2.js"></script>
            HTML,
            $html,
            'At the beginning of the body section noscript and JSON-LD script tags should be rendered before the JS files.',
        );
        $this->assertContainsWithoutLE(
            <<<HTML
            <noscript><img src="/3.gif" alt=""></noscript>
            <script type="application/ld+json">{"@type":"Thing","position":3}</script>
            <script src="/js/3.js"></script></body>
            HTML,
            $html,
            'At the end of the body section noscript and JSON-LD script tags should be rendered before the JS files.',
        );
    }

    public function testRegisterJsFileWithAlias(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_HEAD]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<script src="/baseUrl/js/somefile.js"></script></head>', $html);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_BEGIN]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContainsWithoutLE('<body>' . PHP_EOL . '<script src="/baseUrl/js/somefile.js"></script>', $html);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_END]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<script src="/baseUrl/js/somefile.js"></script></body>', $html);

        // alias with depends
        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_END, 'depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<script src="/baseUrl/js/somefile.js"></script></body>', $html);
    }

    public function testRegisterCssFileWithAlias(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $view = new View();
        $view->registerCssFile('@web/css/somefile.css');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<link href="/baseUrl/css/somefile.css" rel="stylesheet"></head>', $html);

        // with depends
        $view = new View();
        $view->registerCssFile(
            '@web/css/somefile.css',
            ['position' => View::POS_END, 'depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<link href="/baseUrl/css/somefile.css" rel="stylesheet"></head>', $html);
    }

    public function testRegisterregisterCsrfMetaTags(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
                'cache' => [
                    'class' => FileCache::class,
                ],
            ],
        ]);

        $view = new View();

        $view->registerCsrfMetaTags();
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<meta name="csrf-param" content="_csrf">', $html);
        $this->assertStringContainsString('<meta name="csrf-token" content="', $html);
        $csrfToken1 = $this->getCSRFTokenValue($html);

        // regenerate token
        Yii::$app->request->getCsrfToken(true);
        $view->registerCsrfMetaTags();
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString('<meta name="csrf-param" content="_csrf">', $html);
        $this->assertStringContainsString('<meta name="csrf-token" content="', $html);
        $csrfToken2 = $this->getCSRFTokenValue($html);

        $this->assertNotSame($csrfToken1, $csrfToken2);
    }

    /**
     * Parses CSRF token from page HTML.
     *
     * @param string $html
     * @return string CSRF token
     */
    private function getCSRFTokenValue($html)
    {
        if (!preg_match('~<meta name="csrf-token" content="([^"]+)">~', $html, $matches)) {
            $this->fail("No CSRF-token meta tag found. HTML was:\n$html");
        }

        return $matches[1];
    }

    private function setUpAliases(): void
    {
        Yii::setAlias('@web', '/');
        Yii::setAlias('@webroot', '@yiiunit/data/web');
        Yii::setAlias('@testAssetsPath', '@webroot/assets');
        Yii::setAlias('@testAssetsUrl', '@web/assets');
        Yii::setAlias('@testSourcePath', '@webroot/assetSources');
    }

    public function testAppendTimestampForRegisterJsFile(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $this->setUpAliases();

        $pattern = '/assetSources\/js\/jquery\.js\?v\=\d+"/';

        Yii::$app->assetManager->appendTimestamp = true;

        // will be used AssetManager and timestamp
        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['depends' => 'yii\web\AssetBundle']
        ); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // test append timestamp when @web is prefixed in url
        Yii::setAlias('@web', '/test-app');
        $view = new View();
        $view->registerJsFile(
            Yii::getAlias('@web/assetSources/js/jquery.js'),
            ['depends' => 'yii\web\AssetBundle']
        ); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // test append timestamp when @web has the same name as the asset-source folder
        Yii::setAlias('@web', '/assetSources/');
        $view = new View();
        $view->registerJsFile(
            Yii::getAlias('@web/assetSources/js/jquery.js'),
            ['depends' => 'yii\web\AssetBundle']
        ); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);
        // reset aliases
        $this->setUpAliases();

        // won't be used AssetManager but the timestamp will be
        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['appendTimestamp' => true]
        ); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['appendTimestamp' => false]
        ); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // with alias
        $view = new View();
        $view->registerJsFile('@web/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // with alias but wo timestamp
        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile(
            '@web/assetSources/js/jquery.js',
            [
                'appendTimestamp' => false,
                'depends' => 'yii\web\AssetBundle',
            ]
        ); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // wo depends == wo AssetManager
        $view = new View();
        $view->registerJsFile(
            '@web/assetSources/js/jquery.js',
            ['appendTimestamp' => false]
        ); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );

        $view = new View();
        $view->registerJsFile(
            '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );

        $view = new View();
        $view->registerJsFile(
            'http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );

        Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['depends' => 'yii\web\AssetBundle']
        ); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['appendTimestamp' => true]
        ); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            [
                'appendTimestamp' => true,
                'depends' => 'yii\web\AssetBundle',
            ]
        ); // <script src="/assetSources/js/jquery.js?v=1602294572"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        $view = new View();
        $view->registerJsFile(
            '/assetSources/js/jquery.js',
            ['appendTimestamp' => false]
        ); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );

        $view = new View();
        $view->registerJsFile(
            '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );

        $view = new View();
        $view->registerJsFile(
            'http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html
        );
    }

    public function testAppendTimestampForRegisterCssFile(): void
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
            ],
        ]);

        $this->setUpAliases();

        $pattern = '/assetSources\/css\/stub\.css\?v\=\d+"/';

        Yii::$app->assetManager->appendTimestamp = true;

        // will be used AssetManager and timestamp
        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['depends' => 'yii\web\AssetBundle']
        ); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // test append timestamp when @web is prefixed in url
        Yii::setAlias('@web', '/test-app');
        $view = new View();
        $view->registerCssFile(
            Yii::getAlias('@web/assetSources/css/stub.css'),
            ['depends' => 'yii\web\AssetBundle']
        ); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // test append timestamp when @web has the same name as the asset-source folder
        Yii::setAlias('@web', '/assetSources/');
        $view = new View();
        $view->registerCssFile(
            Yii::getAlias('@web/assetSources/css/stub.css'),
            ['depends' => 'yii\web\AssetBundle']
        ); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);
        // reset aliases
        $this->setUpAliases();

        // won't be used AssetManager but the timestamp will be
        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['appendTimestamp' => true]
        ); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['appendTimestamp' => false]
        ); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // with alias
        $view = new View();
        $view->registerCssFile('@web/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // with alias but wo timestamp
        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile(
            '@web/assetSources/css/stub.css',
            [
                'appendTimestamp' => false,
                'depends' => 'yii\web\AssetBundle',
            ]
        ); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // wo depends == wo AssetManager
        $view = new View();
        $view->registerCssFile(
            '@web/assetSources/css/stub.css',
            ['appendTimestamp' => false]
        ); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );

        $view = new View();
        $view->registerCssFile(
            '//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );

        $view = new View();
        $view->registerCssFile(
            'https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );

        Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['depends' => 'yii\web\AssetBundle']
        ); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['appendTimestamp' => true]
        ); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            [
                'appendTimestamp' => true,
                'depends' => 'yii\web\AssetBundle',
            ]
        ); // <link href="/assetSources/css/stub.css?v=1602294572" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertMatchesRegularExpression($pattern, $html);

        $view = new View();
        $view->registerCssFile(
            '/assetSources/css/stub.css',
            ['appendTimestamp' => false]
        ); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertDoesNotMatchRegularExpression($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );

        $view = new View();
        $view->registerCssFile(
            '//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );

        $view = new View();
        $view->registerCssFile(
            'https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']
        );
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertStringContainsString(
            '<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html
        );
    }
}
