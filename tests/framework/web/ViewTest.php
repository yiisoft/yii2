<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\caching\FileCache;
use yii\helpers\FileHelper;
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
        $view->registerJsVar('objectTest', [
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
        $this->assertContains('<body>' . PHP_EOL . '<script src="/baseUrl/js/somefile.js"></script>', $html);

        $view = new View();
        $view->registerJsFile('@web/js/somefile.js', ['position' => View::POS_END]);
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

    private function setUpAliases() {
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

        $pattern = '/js\/jquery\.js\?v\=\d+"/';

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js', ['appendTimestamp' => true]); // <script src="/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js', ['appendTimestamp' => false]); // <script src="/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        \Yii::$app->assetManager->appendTimestamp = true;

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js', ['depends' => 'yii\web\AssetBundle']); // <script src="/js/jquery.js?v=1541056962"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        \Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerJsFile('/assetSources/js/jquery.js', ['depends' => 'yii\web\AssetBundle']); // <script src="/js/jquery.js"></script>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

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

        $pattern = '/stub\.css\?v\=\d+/';

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css', ['appendTimestamp' => true]); // <link href="/css/stub.css?v=1541055635" rel="stylesheet"></head>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css'); // <link href="/css/stub.css" rel="stylesheet"></head>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);

        \Yii::$app->assetManager->appendTimestamp = true;

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css', ['depends' => 'yii\web\AssetBundle']); // <link href="/css/stub.css?v=1541055635" rel="stylesheet"></head>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertRegExp($pattern, $html);

        \Yii::$app->assetManager->appendTimestamp = false;

        $view = new View();
        $view->registerCssFile('/assetSources/css/stub.css', ['depends' => 'yii\web\AssetBundle']); // <link href="/css/stub.css" rel="stylesheet"></head>
        $html = $view->render('@yiiunit/data/views/layout.php', ['content' => 'content']);
        $this->assertNotRegExp($pattern, $html);
    }
}
