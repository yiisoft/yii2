<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\caching\FileCache;
use yii\web\View;
use yiiunit\TestCase;

/**
 * @group web
 */
class ViewTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testRegisterJsVar()
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
        $this->assertContains('<script>var username = "samdark";</script></head>', $html);

        $view = new View();
        $view->registerJsVar('objectTest',
            [
                'number' => 42,
                'question' => 'Unknown',
            ]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script>var objectTest = {"number":42,"question":"Unknown"};</script></head>', $html);
    }

    public function testRegisterJsFileWithAlias()
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
        $this->assertContains('<script src="/baseUrl/js/somefile.js"></script></head>', $html);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_BEGIN]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContainsWithoutLE('<body>' . PHP_EOL . '<script src="/baseUrl/js/somefile.js"></script>', $html);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_END]);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="/baseUrl/js/somefile.js"></script></body>', $html);

        // alias with depends
        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_END, 'depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="/baseUrl/js/somefile.js"></script></body>', $html);
    }

    public function testRegisterCssFileWithAlias()
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
        $this->assertContains('<link href="/baseUrl/css/somefile.css" rel="stylesheet"></head>', $html);

        // with depends
        $view = new View();
        $view->registerCssFile('@web/css/somefile.css',
            ['position' => View::POS_END, 'depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="/baseUrl/css/somefile.css" rel="stylesheet"></head>', $html);
    }

    public function testRegisterregisterCsrfMetaTags()
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'scriptFile' => __DIR__ . '/baseUrl/index.php',
                    'scriptUrl' => '/baseUrl/index.php',
                ],
                'cache' => [
                    'class' => FileCache::className(),
                ],
            ],
        ]);

        $view = new View();

        $view->registerCsrfMetaTags();
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<meta name="csrf-param" content="_csrf">', $html);
        $this->assertContains('<meta name="csrf-token" content="', $html);
        $csrfToken1 = $this->getCSRFTokenValue($html);

        // regenerate token
        \Yii::$app->request->getCsrfToken(true);
        $view->registerCsrfMetaTags();
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<meta name="csrf-param" content="_csrf">', $html);
        $this->assertContains('<meta name="csrf-token" content="', $html);
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

    private function setUpAliases()
    {
        \Yii::setAlias('@web', '/');
        \Yii::setAlias('@webroot', '@yiiunit/data/web');
        \Yii::setAlias('@testAssetsPath', '@webroot/assets');
        \Yii::setAlias('@testAssetsUrl', '@web/assets');
        \Yii::setAlias('@testSourcePath', '@webroot/assetSources');
    }

    public function testAppendTimestampForRegisterJsFile()
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

        \Yii::$app->assetManager->appendTimestamp = true;

        // will be used AssetManager and timestamp
        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['depends' => 'yii\web\AssetBundle']); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // test append timestamp when @web is prefixed in url
        \Yii::setAlias('@web', '/test-app');
        $view = new View();
        $view->registerJsFile(\Yii::getAlias('@web/assetSources/js/jquery.js'),
            ['depends' => 'yii\web\AssetBundle']); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // test append timestamp when @web has the same name as the asset-source folder
        \Yii::setAlias('@web', '/assetSources/');
        $view = new View();
        $view->registerJsFile(\Yii::getAlias('@web/assetSources/js/jquery.js'),
            ['depends' => 'yii\web\AssetBundle']); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);
        // reset aliases
        $this->setUpAliases();

        // won't be used AssetManager but the timestamp will be
        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['appendTimestamp' => true]); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['appendTimestamp' => false]); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // with alias
        $view = new View();
        $view->registerJsFile('@web/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // with alias but wo timestamp
        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile('@web/assetSources/js/jquery.js',
            [
                'appendTimestamp' => false,
                'depends' => 'yii\web\AssetBundle',
            ]); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // wo depends == wo AssetManager
        $view = new View();
        $view->registerJsFile('@web/assetSources/js/jquery.js',
            ['appendTimestamp' => false]); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

        $view = new View();
        $view->registerJsFile('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

        \Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['depends' => 'yii\web\AssetBundle']); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js'); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['appendTimestamp' => true]); // <script src="/assetSources/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            [
                'appendTimestamp' => true,
                'depends' => 'yii\web\AssetBundle',
            ]); // <script src="/assetSources/js/jquery.js?v=1602294572"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js',
            ['appendTimestamp' => false]); // <script src="/assetSources/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

        $view = new View();
        $view->registerJsFile('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

        $view = new View();
        $view->registerJsFile('http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>',
            $html);

    }

    public function testAppendTimestampForRegisterCssFile()
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

        \Yii::$app->assetManager->appendTimestamp = true;

        // will be used AssetManager and timestamp
        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['depends' => 'yii\web\AssetBundle']); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // test append timestamp when @web is prefixed in url
        \Yii::setAlias('@web', '/test-app');
        $view = new View();
        $view->registerCssFile(\Yii::getAlias('@web/assetSources/css/stub.css'),
            ['depends' => 'yii\web\AssetBundle']); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // test append timestamp when @web has the same name as the asset-source folder
        \Yii::setAlias('@web', '/assetSources/');
        $view = new View();
        $view->registerCssFile(\Yii::getAlias('@web/assetSources/css/stub.css'),
            ['depends' => 'yii\web\AssetBundle']); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);
        // reset aliases
        $this->setUpAliases();

        // won't be used AssetManager but the timestamp will be
        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['appendTimestamp' => true]); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['appendTimestamp' => false]); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // with alias
        $view = new View();
        $view->registerCssFile('@web/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // with alias but wo timestamp
        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile('@web/assetSources/css/stub.css',
            [
                'appendTimestamp' => false,
                'depends' => 'yii\web\AssetBundle',
            ]); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // wo depends == wo AssetManager
        $view = new View();
        $view->registerCssFile('@web/assetSources/css/stub.css',
            ['appendTimestamp' => false]); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);

        $view = new View();
        $view->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);

        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);

        \Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['depends' => 'yii\web\AssetBundle']); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css'); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['appendTimestamp' => true]); // <link href="/assetSources/css/stub.css?v=1541056962" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        // redefine AssetManager timestamp setting
        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            [
                'appendTimestamp' => true,
                'depends' => 'yii\web\AssetBundle',
            ]); // <link href="/assetSources/css/stub.css?v=1602294572" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css',
            ['appendTimestamp' => false]); // <link href="/assetSources/css/stub.css" rel="stylesheet" >
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        // absolute link
        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css');
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);

        $view = new View();
        $view->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="//cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);

        $view = new View();
        $view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css',
            ['depends' => 'yii\web\AssetBundle']);
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertContains('<link href="https://cdnjs.cloudflare.com/ajax/libs/balloon-css/1.0.3/balloon.css" rel="stylesheet">',
            $html);
    }
}
