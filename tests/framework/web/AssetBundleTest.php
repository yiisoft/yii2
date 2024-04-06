<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;
use yii\web\AssetManager;
use yii\web\View;

/**
 * @group web
 */
class AssetBundleTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        Yii::setAlias('@web', '/');
        Yii::setAlias('@webroot', '@yiiunit/data/web');
        Yii::setAlias('@testAssetsPath', '@webroot/assets');
        Yii::setAlias('@testAssetsUrl', '@web/assets');
        Yii::setAlias('@testSourcePath', '@webroot/assetSources');

        // clean up assets directory
        $handle = opendir($dir = Yii::getAlias('@testAssetsPath'));
        if ($handle === false) {
            throw new \Exception("Unable to open directory: $dir");
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                FileHelper::removeDirectory($path);
            } else {
                FileHelper::unlink($path);
            }
        }
        closedir($handle);
    }

    /**
     * Returns View with configured AssetManager.
     *
     * @param array $config may be used to override default AssetManager config
     * @return View
     */
    protected function getView(array $config = [])
    {
        $this->mockApplication();
        $view = new View();
        $config = array_merge([
            'basePath' => '@testAssetsPath',
            'baseUrl' => '@testAssetsUrl',
        ], $config);
        $view->setAssetManager(new AssetManager($config));

        return $view;
    }

    public function testSourcesPublish()
    {
        $view = $this->getView();
        $am = $view->assetManager;

        $bundle = TestSourceAsset::register($view);
        $bundle->publish($am);

        $this->assertTrue(is_dir($bundle->basePath));
        $this->sourcesPublish_VerifyFiles('css', $bundle);
        $this->sourcesPublish_VerifyFiles('js', $bundle);
    }

    private function sourcesPublish_VerifyFiles($type, $bundle)
    {
        foreach ($bundle->$type as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $sourceFile = $bundle->sourcePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileExists($publishedFile);
            $this->assertFileEquals($publishedFile, $sourceFile);
        }
        $this->assertTrue(is_dir($bundle->basePath . DIRECTORY_SEPARATOR . $type));
    }

    public function testSourcesPublishedBySymlink()
    {
        $view = $this->getView(['linkAssets' => true]);
        $this->verifySourcesPublishedBySymlink($view);
    }

    public function testSourcesPublishedBySymlink_Issue9333()
    {
        $view = $this->getView([
            'linkAssets' => true,
            'hashCallback' => function ($path) {
                return sprintf('%x/%x', crc32($path), crc32(Yii::getVersion()));
            },
        ]);
        $bundle = $this->verifySourcesPublishedBySymlink($view);
        $this->assertTrue(is_dir(dirname($bundle->basePath)));
    }

    public function testSourcesPublish_AssetManagerBeforeCopy()
    {
        $view = $this->getView([
            'beforeCopy' => function ($from, $to) {
                return false;
            },
        ]);
        $am = $view->assetManager;

        $bundle = TestSourceAsset::register($view);
        $bundle->publish($am);

        $this->assertFalse(is_dir($bundle->basePath));
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileDoesNotExist($publishedFile);
        }
    }

    public function testSourcesPublish_AssetBeforeCopy()
    {
        $view = $this->getView();
        $am = $view->assetManager;

        $bundle = new TestSourceAsset();
        $bundle->publishOptions = [
            'beforeCopy' => function ($from, $to) {
                return false;
            },
        ];
        $bundle->publish($am);

        $this->assertFalse(is_dir($bundle->basePath));
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileDoesNotExist($publishedFile);
        }
    }

    public function testSourcesPublish_publishOptions_Only()
    {
        $view = $this->getView();
        $am = $view->assetManager;

        $bundle = new TestSourceAsset([
            'publishOptions' => [
                'only' => [
                    'js/*',
                ],
            ],
        ]);
        $bundle->publish($am);

        $notNeededFilesDir = dirname($bundle->basePath . DIRECTORY_SEPARATOR . $bundle->css[0]);
        $this->assertFileDoesNotExist($notNeededFilesDir);

        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileExists($publishedFile);
        }
        $this->assertTrue(is_dir(dirname($bundle->basePath . DIRECTORY_SEPARATOR . $bundle->js[0])));
        $this->assertTrue(is_dir($bundle->basePath));
    }

    public function testBasePathIsWritableOnPublish()
    {
        Yii::setAlias('@testReadOnlyAssetPath', '@webroot/readOnlyAssets');
        $path = Yii::getAlias('@testReadOnlyAssetPath');

        // Deleting a directory that could remain after a previous unsuccessful test run
        FileHelper::removeDirectory($path);

        mkdir($path, 0555);
        if (is_writable($path)) {
            $this->markTestSkipped("This test can only be performed with reliable chmod. It's unreliable on your system.");
        }

        $view = $this->getView(['basePath' => '@testReadOnlyAssetPath']);
        $bundle = new TestSourceAsset();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('The directory is not writable by the Web process');

        $bundle->publish($view->getAssetManager());

        FileHelper::removeDirectory($path);
    }

    /**
     * @param View $view
     * @return AssetBundle
     */
    protected function verifySourcesPublishedBySymlink($view)
    {
        $am = $view->assetManager;

        $bundle = TestSourceAsset::register($view);
        $bundle->publish($am);

        $this->assertTrue(is_dir($bundle->basePath));
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $sourceFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;

            $this->assertTrue(is_link($bundle->basePath));
            $this->assertFileExists($publishedFile);
            $this->assertFileEquals($publishedFile, $sourceFile);
        }

        $this->assertTrue(FileHelper::unlink($bundle->basePath));
        return $bundle;
    }

    public function testRegister()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertCount(1, $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestSimpleAsset', $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestSimpleAsset']);

        $expected = <<<'EOF'
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function testSimpleDependency()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetBundle::register($view);
        $this->assertCount(3, $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']);

        $expected = <<<'EOF'
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
        $this->assertEqualsWithoutLE($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function positionProvider()
    {
        return [
            [View::POS_HEAD, true],
            [View::POS_HEAD, false],
            [View::POS_BEGIN, true],
            [View::POS_BEGIN, false],
            [View::POS_END, true],
            [View::POS_END, false],
        ];
    }

    /**
     * @dataProvider positionProvider
     * @param int $pos
     * @param bool $jqAlreadyRegistered
     */
    public function testPositionDependency($pos, $jqAlreadyRegistered)
    {
        $view = $this->getView();

        $view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestAssetBundle'] = [
            'jsOptions' => [
                'position' => $pos,
            ],
        ];

        $this->assertEmpty($view->assetBundles);
        if ($jqAlreadyRegistered) {
            TestJqueryAsset::register($view);
        }
        TestAssetBundle::register($view);
        $this->assertCount(3, $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);

        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']);

        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions['position']);

        switch ($pos) {
            case View::POS_HEAD:
                $expected = <<<'EOF'
1<link href="/files/cssFile.css" rel="stylesheet">
<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>234
EOF;
            break;
            case View::POS_BEGIN:
                $expected = <<<'EOF'
1<link href="/files/cssFile.css" rel="stylesheet">2<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>34
EOF;
            break;
            default:
            case View::POS_END:
                $expected = <<<'EOF'
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
            break;
        }
        $this->assertEqualsWithoutLE($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function positionProvider2()
    {
        return [
            [View::POS_BEGIN, true],
            [View::POS_BEGIN, false],
            [View::POS_END, true],
            [View::POS_END, false],
        ];
    }

    /**
     * @dataProvider positionProvider
     * @param int $pos
     * @param bool $jqAlreadyRegistered
     */
    public function testPositionDependencyConflict($pos, $jqAlreadyRegistered)
    {
        $view = $this->getView();

        $view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestAssetBundle'] = [
            'jsOptions' => [
                'position' => $pos - 1,
            ],
        ];
        $view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestJqueryAsset'] = [
            'jsOptions' => [
                'position' => $pos,
            ],
        ];

        $this->assertEmpty($view->assetBundles);
        if ($jqAlreadyRegistered) {
            TestJqueryAsset::register($view);
        }
        $this->expectException('yii\\base\\InvalidConfigException');
        TestAssetBundle::register($view);
    }

    public function testCircularDependency()
    {
        $this->expectException('yii\\base\\InvalidConfigException');
        TestAssetCircleA::register($this->getView());
    }

    public function testDuplicateAssetFile()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertCount(1, $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestSimpleAsset', $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::className(), $view->assetBundles['yiiunit\\framework\\web\\TestSimpleAsset']);
        // register TestJqueryAsset which also has the jquery.js
        TestJqueryAsset::register($view);

        $expected = <<<'EOF'
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function testPerFileOptions()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetPerFileOptions::register($view);

        $expected = <<<'EOF'
1<link href="/default_options.css" rel="stylesheet" media="screen" hreflang="en">
<link href="/tv.css" rel="stylesheet" media="tv" hreflang="en">
<link href="/screen_and_print.css" rel="stylesheet" media="screen, print" hreflang="en">23<script src="/normal.js" charset="utf-8"></script>
<script src="/defered.js" charset="utf-8" defer></script>4
EOF;
        $this->assertEqualsWithoutLE($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function registerFileDataProvider()
    {
        return [
            // JS files registration
            [
                'js', '@web/assetSources/js/missing-file.js', true,
                '123<script src="/assetSources/js/missing-file.js"></script>4',
            ],
            [
                'js', '@web/assetSources/js/jquery.js', false,
                '123<script src="/assetSources/js/jquery.js"></script>4',
            ],
            [
                'js', 'http://example.com/assetSources/js/jquery.js', false,
                '123<script src="http://example.com/assetSources/js/jquery.js"></script>4',
            ],
            [
                'js', '//example.com/assetSources/js/jquery.js', false,
                '123<script src="//example.com/assetSources/js/jquery.js"></script>4',
            ],
            [
                'js', 'assetSources/js/jquery.js', false,
                '123<script src="assetSources/js/jquery.js"></script>4',
            ],
            [
                'js', '/assetSources/js/jquery.js', false,
                '123<script src="/assetSources/js/jquery.js"></script>4',
            ],

            // CSS file registration
            [
                'css', '@web/assetSources/css/missing-file.css', true,
                '1<link href="/assetSources/css/missing-file.css" rel="stylesheet">234',
            ],
            [
                'css', '@web/assetSources/css/stub.css', false,
                '1<link href="/assetSources/css/stub.css" rel="stylesheet">234',
            ],
            [
                'css', 'http://example.com/assetSources/css/stub.css', false,
                '1<link href="http://example.com/assetSources/css/stub.css" rel="stylesheet">234',
            ],
            [
                'css', '//example.com/assetSources/css/stub.css', false,
                '1<link href="//example.com/assetSources/css/stub.css" rel="stylesheet">234',
            ],
            [
                'css', 'assetSources/css/stub.css', false,
                '1<link href="assetSources/css/stub.css" rel="stylesheet">234',
            ],
            [
                'css', '/assetSources/css/stub.css', false,
                '1<link href="/assetSources/css/stub.css" rel="stylesheet">234',
            ],

            // Custom `@web` aliases
            [
                'js', '@web/assetSources/js/missing-file1.js', true,
                '123<script src="/backend/assetSources/js/missing-file1.js"></script>4',
                '/backend',
            ],
            [
                'js', 'http://full-url.example.com/backend/assetSources/js/missing-file.js', true,
                '123<script src="http://full-url.example.com/backend/assetSources/js/missing-file.js"></script>4',
                '/backend',
            ],
            [
                'css', '//backend/backend/assetSources/js/missing-file.js', true,
                '1<link href="//backend/backend/assetSources/js/missing-file.js" rel="stylesheet">234',
                '/backend',
            ],
            [
                'css', '@web/assetSources/css/stub.css', false,
                '1<link href="/en/blog/backend/assetSources/css/stub.css" rel="stylesheet">234',
                '/en/blog/backend',
            ],

            // UTF-8 chars
            [
                'css', '@web/assetSources/css/stub.css', false,
                '1<link href="/рус/сайт/assetSources/css/stub.css" rel="stylesheet">234',
                '/рус/сайт',
            ],
            [
                'js', '@web/assetSources/js/jquery.js', false,
                '123<script src="/汉语/漢語/assetSources/js/jquery.js"></script>4',
                '/汉语/漢語',
            ],

            // Custom alias repeats in the asset URL
            [
                'css', '@web/assetSources/repeat/css/stub.css', false,
                '1<link href="/repeat/assetSources/repeat/css/stub.css" rel="stylesheet">234',
                '/repeat',
            ],
            [
                'js', '@web/assetSources/repeat/js/jquery.js', false,
                '123<script src="/repeat/assetSources/repeat/js/jquery.js"></script>4',
                '/repeat',
            ],
        ];
    }

    /**
     * @dataProvider registerFileDataProvider
     * @param string $type either `js` or `css`
     * @param string $path
     * @param string|bool $appendTimestamp
     * @param string $expected
     * @param string|null $webAlias
     */
    public function testRegisterFileAppendTimestamp($type, $path, $appendTimestamp, $expected, $webAlias = null)
    {
        $originalAlias = Yii::getAlias('@web');
        if ($webAlias === null) {
            $webAlias = $originalAlias;
        }
        Yii::setAlias('@web', $webAlias);

        $view = $this->getView(['appendTimestamp' => $appendTimestamp]);
        $method = 'register' . ucfirst($type) . 'File';
        $view->$method($path);
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));

        Yii::setAlias('@web', $originalAlias);
    }

    public function testCustomFilePublishWithTimestamp()
    {
        $path = Yii::getAlias('@webroot');

        $view = $this->getView();
        $am = $view->assetManager;
        // publishing without timestamp
        $result = $am->publish($path . '/data.txt');
        $this->assertMatchesRegularExpression('/.*data.txt$/i', $result[1]);
        unset($view, $am, $result);

        $view = $this->getView();
        $am = $view->assetManager;
        // turn on timestamp appending
        $am->appendTimestamp = true;
        $result = $am->publish($path . '/data.txt');
        $this->assertMatchesRegularExpression('/.*data.txt\?v=\d+$/i', $result[1]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18529
     */
    public function testNonRelativeAssetWebPathWithTimestamp()
    {
        Yii::setAlias('@webroot', '@yiiunit/data/web/assetSources/');

        $view = $this->getView(['appendTimestamp' => true]);
        TestNonRelativeAsset::register($view);
        $this->assertMatchesRegularExpression(
            '~123<script src="http:\/\/example\.com\/js\/jquery\.js\?v=\d+"><\/script>4~',
            $view->renderFile('@yiiunit/data/views/rawlayout.php')
        );
    }
}

class TestSimpleAsset extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
}

class TestSourceAsset extends AssetBundle
{
    public $sourcePath = '@testSourcePath';
    public $js = [
        'js/jquery.js',
    ];
    public $css = [
        'css/stub.css',
    ];
}

class TestAssetBundle extends AssetBundle
{
    public $basePath = '@webroot/files';
    public $baseUrl = '@web/files';
    public $css = [
        'cssFile.css',
    ];
    public $js = [
        'jsFile.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestJqueryAsset',
    ];
}

class TestJqueryAsset extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetLevel3',
    ];
}

class TestAssetLevel3 extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
}

class TestAssetCircleA extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetCircleB',
    ];
}

class TestAssetCircleB extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetCircleA',
    ];
}

class TestAssetPerFileOptions extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'default_options.css',
        ['tv.css', 'media' => 'tv'],
        ['screen_and_print.css', 'media' => 'screen, print'],
    ];
    public $js = [
        'normal.js',
        ['defered.js', 'defer' => true],
    ];
    public $cssOptions = ['media' => 'screen', 'hreflang' => 'en'];
    public $jsOptions = ['charset' => 'utf-8'];
}

class TestNonRelativeAsset extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = 'http://example.com/js/';
    public $js = [
        'jquery.js',
    ];
}
