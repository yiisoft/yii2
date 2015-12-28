<?php
/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\View;
use yii\web\AssetBundle;
use yii\web\AssetManager;

/**
 * @group web
 */
class AssetBundleTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');
        Yii::setAlias('@testAssetsPath', '@testWebRoot/assets');
        Yii::setAlias('@testAssetsUrl', '@testWeb/assets');
        Yii::setAlias('@testSourcePath', '@testWebRoot/assetSources');
    }

    /**
     * Returns View with configured AssetManager
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
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $sourceFile = $bundle->sourcePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileExists($publishedFile);
            $this->assertFileEquals($publishedFile, $sourceFile);
            $this->assertTrue(unlink($publishedFile));
        }
        $this->assertTrue(rmdir($bundle->basePath . DIRECTORY_SEPARATOR . 'js'));

        $this->assertTrue(rmdir($bundle->basePath));
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
            }
        ]);
        $bundle = $this->verifySourcesPublishedBySymlink($view);
        $this->assertTrue(rmdir(dirname($bundle->basePath)));
    }

    public function testSourcesPublish_AssetManagerBeforeCopy()
    {
        $view = $this->getView([
            'beforeCopy' => function ($from, $to) {
                return false;
            }
        ]);
        $am = $view->assetManager;

        $bundle = TestSourceAsset::register($view);
        $bundle->publish($am);

        $this->assertTrue(is_dir($bundle->basePath));
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileNotExists($publishedFile);
        }
        $this->assertTrue(rmdir($bundle->basePath));
    }

    public function testSourcesPublish_AssetBeforeCopy()
    {
        $view = $this->getView();
        $am = $view->assetManager;

        $bundle = new TestSourceAsset();
        $bundle->publishOptions = [
            'beforeCopy' => function ($from, $to) {
                return false;
            }
        ];
        $bundle->publish($am);

        $this->assertTrue(is_dir($bundle->basePath));
        foreach ($bundle->js as $filename) {
            $publishedFile = $bundle->basePath . DIRECTORY_SEPARATOR . $filename;
            $this->assertFileNotExists($publishedFile);
        }
        $this->assertTrue(rmdir($bundle->basePath));
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

        $this->assertTrue(unlink($bundle->basePath));
        return $bundle;
    }

    public function testRegister()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertEquals(1, count($view->assetBundles));
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestSimpleAsset', $view->assetBundles);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestSimpleAsset'] instanceof AssetBundle);

        $expected = <<<EOF
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function testSimpleDependency()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetBundle::register($view);
        $this->assertEquals(3, count($view->assetBundles));
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3'] instanceof AssetBundle);

        $expected = <<<EOF
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
        $this->assertEquals(3, count($view->assetBundles));
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);

        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3'] instanceof AssetBundle);

        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions['position']);

        switch ($pos) {
            case View::POS_HEAD:
                $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">
<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>234
EOF;
            break;
            case View::POS_BEGIN:
                $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">2<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>34
EOF;
            break;
            default:
            case View::POS_END:
                $expected = <<<EOF
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
        $this->setExpectedException('yii\\base\\InvalidConfigException');
        TestAssetBundle::register($view);
    }

    public function testCircularDependency()
    {
        $this->setExpectedException('yii\\base\\InvalidConfigException');
        TestAssetCircleA::register($this->getView());
    }

    public function testDuplicateAssetFile()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertEquals(1, count($view->assetBundles));
        $this->assertArrayHasKey('yiiunit\\framework\\web\\TestSimpleAsset', $view->assetBundles);
        $this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestSimpleAsset'] instanceof AssetBundle);
        // register TestJqueryAsset which also has the jquery.js
        TestJqueryAsset::register($view);

        $expected = <<<EOF
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }

    public function testPerFileOptions()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetPerFileOptions::register($view);

        $expected = <<<EOF
1<link href="/default_options.css" rel="stylesheet" media="screen" hreflang="en">
<link href="/tv.css" rel="stylesheet" media="tv" hreflang="en">
<link href="/screen_and_print.css" rel="stylesheet" media="screen, print" hreflang="en">23<script src="/normal.js" charset="utf-8"></script>
<script src="/defered.js" charset="utf-8" defer></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
    }
}

class TestSimpleAsset extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
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
}

class TestAssetBundle extends AssetBundle
{
    public $basePath = '@testWebRoot/files';
    public $baseUrl = '@testWeb/files';
    public $css = [
        'cssFile.css',
    ];
    public $js = [
        'jsFile.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestJqueryAsset'
    ];
}

class TestJqueryAsset extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetLevel3'
    ];
}

class TestAssetLevel3 extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
}

class TestAssetCircleA extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetCircleB'
    ];
}

class TestAssetCircleB extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'yiiunit\\framework\\web\\TestAssetCircleA'
    ];
}

class TestAssetPerFileOptions extends AssetBundle
{
    public $basePath = '@testWebRoot';
    public $baseUrl = '@testWeb';
    public $css = [
        'default_options.css',
        ['tv.css', 'media' => 'tv'],
        ['screen_and_print.css', 'media' => 'screen, print']
    ];
    public $js = [
        'normal.js',
        ['defered.js', 'defer' => true],
    ];
    public $cssOptions = ['media' => 'screen', 'hreflang' => 'en'];
    public $jsOptions = ['charset' => 'utf-8'];
}
