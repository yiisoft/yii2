<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\AssetController;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\AssetController]].
 * @see AssetController
 *
 * @group console
 */
class AssetControllerTest extends TestCase
{
    /**
     * @var string path for the test files.
     */
    protected $testFilePath = '';
    /**
     * @var string test assets path.
     */
    protected $testAssetsBasePath = '';

    protected function setUp(): void
    {
        $this->mockApplication();
        $this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . str_replace('\\', '_', get_class($this)) . uniqid();
        $this->createDir($this->testFilePath);
        $this->testAssetsBasePath = $this->testFilePath . DIRECTORY_SEPARATOR . 'assets';
        $this->createDir($this->testAssetsBasePath);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->testFilePath);
    }

    /**
     * Creates directory.
     * @param string $dirName directory full name.
     */
    protected function createDir($dirName)
    {
        FileHelper::createDirectory($dirName);
    }

    /**
     * Removes directory.
     * @param string $dirName directory full name
     */
    protected function removeDir($dirName)
    {
        if (!empty($dirName)) {
            FileHelper::removeDirectory($dirName);
        }
    }

    /**
     * Creates test asset controller instance.
     * @return AssetControllerMock
     */
    protected function createAssetController()
    {
        $module = $this->getMockBuilder('yii\\base\\Module')
            ->setMethods(['fake'])
            ->setConstructorArgs(['console'])
            ->getMock();
        $assetController = new AssetControllerMock('asset', $module);
        $assetController->interactive = false;
        $assetController->jsCompressor = 'cp {from} {to}';
        $assetController->cssCompressor = 'cp {from} {to}';

        return $assetController;
    }

    /**
     * Emulates running of the asset controller action.
     * @param  string $actionID id of action to be run.
     * @param  array  $args     action arguments.
     * @return string command output.
     */
    protected function runAssetControllerAction($actionID, array $args = [])
    {
        $controller = $this->createAssetController();
        $controller->run($actionID, $args);
        return $controller->flushStdOutBuffer();
    }

    /**
     * Creates test compress config.
     * @param array[] $bundles asset bundles config.
     * @param array $config additional config.
     * @return array config array.
     */
    protected function createCompressConfig(array $bundles, array $config = [])
    {
        static $classNumber = 0;
        $classNumber++;
        $className = $this->declareAssetBundleClass(['class' => 'AssetBundleAll' . $classNumber]);
        $baseUrl = '/test';
        $config = ArrayHelper::merge($config, [
            'bundles' => $bundles,
            'targets' => [
                $className => [
                    'basePath' => $this->testAssetsBasePath,
                    'baseUrl' => $baseUrl,
                    'js' => 'all.js',
                    'css' => 'all.css',
                ],
            ],
            'assetManager' => [
                'basePath' => $this->testAssetsBasePath,
                'baseUrl' => '',
            ],
        ]);

        return $config;
    }

    /**
     * Creates test compress config file.
     * @param string $fileName output file name.
     * @param array[] $bundles asset bundles config.
     * @param array $config additional config parameters.
     * @throws \Exception on failure.
     */
    protected function createCompressConfigFile($fileName, array $bundles, array $config = [])
    {
        $content = '<?php return ' . var_export($this->createCompressConfig($bundles, $config), true) . ';';
        if (file_put_contents($fileName, $content) <= 0) {
            throw new \Exception("Unable to create file '{$fileName}'!");
        }
    }

    /**
     * Creates test asset file.
     * @param string $fileRelativeName file name relative to [[testFilePath]]
     * @param string $content file content
     * @param string $fileBasePath base path for the created files, if not set [[testFilePath]] is used.
     * @throws \Exception on failure.
     */
    protected function createAssetSourceFile($fileRelativeName, $content, $fileBasePath = null)
    {
        if ($fileBasePath === null) {
            $fileBasePath = $this->testFilePath;
        }
        $fileFullName = $fileBasePath . DIRECTORY_SEPARATOR . $fileRelativeName;
        $this->createDir(dirname($fileFullName));
        if (file_put_contents($fileFullName, $content) <= 0) {
            throw new \Exception("Unable to create file '{$fileFullName}'!");
        }
    }

    /**
     * Creates a list of asset source files.
     * @param array $files assert source files in format: file/relative/name => fileContent
     * @param string $fileBasePath base path for the created files, if not set [[testFilePath]]
     */
    protected function createAssetSourceFiles(array $files, $fileBasePath = null)
    {
        foreach ($files as $name => $content) {
            $this->createAssetSourceFile($name, $content, $fileBasePath);
        }
    }

    /**
     * Invokes the asset controller method even if it is protected.
     * @param  string $methodName name of the method to be invoked.
     * @param  array  $args       method arguments.
     * @return mixed  method invoke result.
     */
    protected function invokeAssetControllerMethod($methodName, array $args = [])
    {
        $controller = $this->createAssetController();
        $controllerClassReflection = new \ReflectionClass(get_class($controller));
        $methodReflection = $controllerClassReflection->getMethod($methodName);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($controller, $args);
        $methodReflection->setAccessible(false);

        return $result;
    }

    /**
     * Composes asset bundle class source code.
     * @param  array  $config asset bundle config.
     * @return string class source code.
     */
    protected function composeAssetBundleClassSource(array &$config)
    {
        $config = array_merge(
            [
                'namespace' => StringHelper::dirname(get_class($this)),
                'class' => 'AppAsset',
                'sourcePath' => null,
                'basePath' => $this->testFilePath,
                'baseUrl' => '',
                'css' => [],
                'js' => [],
                'depends' => [],
            ],
            $config
        );
        foreach ($config as $name => $value) {
            if (!in_array($name, ['namespace', 'class'])) {
                $config[$name] = VarDumper::export($value);
            }
        }

        $source = <<<EOL
namespace {$config['namespace']};

use yii\web\AssetBundle;

class {$config['class']} extends AssetBundle
{
    public \$sourcePath = {$config['sourcePath']};
    public \$basePath = {$config['basePath']};
    public \$baseUrl = {$config['baseUrl']};
    public \$css = {$config['css']};
    public \$js = {$config['js']};
    public \$depends = {$config['depends']};
}
EOL;

        return $source;
    }

    /**
     * Declares asset bundle class according to given configuration.
     * @param  array  $config asset bundle config.
     * @return string new class full name.
     */
    protected function declareAssetBundleClass(array $config)
    {
        $sourceCode = $this->composeAssetBundleClassSource($config);
        eval($sourceCode);

        return $config['namespace'] . '\\' . $config['class'];
    }

    // Tests :

    public function testActionTemplate()
    {
        $configFileName = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
        $this->runAssetControllerAction('template', [$configFileName]);
        $this->assertFileExists($configFileName, 'Unable to create config file template!');
        $config = require $configFileName;
        $this->assertIsArray($config, 'Invalid config created!');
    }

    public function testActionCompress()
    {
        // Given :
        $cssFiles = [
            'css/test_body.css' => 'body {
                padding-top: 20px;
                padding-bottom: 60px;
            }',
            'css/test_footer.css' => '.footer {
                margin: 20px;
                display: block;
            }',
        ];
        $this->createAssetSourceFiles($cssFiles);

        $jsFiles = [
            'js/test_alert.js' => "function test() {
                alert('Test message');
            }",
            'js/test_sum_ab.js' => 'function sumAB(a, b) {
                return a + b;
            }',
        ];
        $this->createAssetSourceFiles($jsFiles);
        $assetBundleClassName = $this->declareAssetBundleClass([
            'css' => array_keys($cssFiles),
            'js' => array_keys($jsFiles),
        ]);

        $bundles = [
            $assetBundleClassName,
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';

        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config2.php';
        $this->createCompressConfigFile($configFile, $bundles);

        // When :
        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        // Then :
        $this->assertFileExists($bundleFile, 'Unable to create output bundle file!');
        $compressedBundleConfig = require $bundleFile;
        $this->assertIsArray($compressedBundleConfig, 'Output bundle file has incorrect format!');
        $this->assertCount(2, $compressedBundleConfig, 'Output bundle config contains wrong bundle count!');

        $this->assertArrayHasKey($assetBundleClassName, $compressedBundleConfig, 'Source bundle is lost!');
        $compressedAssetBundleConfig = $compressedBundleConfig[$assetBundleClassName];
        $this->assertEmpty($compressedAssetBundleConfig['css'], 'Compressed bundle css is not empty!');
        $this->assertEmpty($compressedAssetBundleConfig['js'], 'Compressed bundle js is not empty!');
        $this->assertNotEmpty($compressedAssetBundleConfig['depends'], 'Compressed bundle dependency is invalid!');

        $compressedCssFileName = $this->testAssetsBasePath . DIRECTORY_SEPARATOR . 'all.css';
        $this->assertFileExists($compressedCssFileName, 'Unable to compress CSS files!');
        $compressedJsFileName = $this->testAssetsBasePath . DIRECTORY_SEPARATOR . 'all.js';
        $this->assertFileExists($compressedJsFileName, 'Unable to compress JS files!');

        $compressedCssFileContent = file_get_contents($compressedCssFileName);
        foreach ($cssFiles as $name => $content) {
            $this->assertStringContainsString(
                $content,
                $compressedCssFileContent,
                "Source of '{$name}' is missing in combined file!",
            );
        }
        $compressedJsFileContent = file_get_contents($compressedJsFileName);
        foreach ($jsFiles as $name => $content) {
            $this->assertStringContainsString(
                $content,
                $compressedJsFileContent,
                "Source of '{$name}' is missing in combined file!",
            );
        }
    }

    /**
     * @depends testActionCompress
     *
     * @see https://github.com/yiisoft/yii2/issues/5194
     */
    public function testCompressExternalAsset()
    {
        // Given :
        $externalAssetConfig = [
            'class' => 'ExternalAsset',
            'sourcePath' => null,
            'basePath' => null,
            'js' => [
                '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
            'css' => [
                '//ajax.googleapis.com/css/libs/jquery/2.1.1/jquery.ui.min.css',
            ],
        ];
        $externalAssetBundleClassName = $this->declareAssetBundleClass($externalAssetConfig);

        $cssFiles = [
            'css/test.css' => 'body {
                padding-top: 20px;
                padding-bottom: 60px;
            }',
        ];
        $this->createAssetSourceFiles($cssFiles);
        $jsFiles = [
            'js/test.js' => "function test() {
                alert('Test message');
            }",
        ];
        $this->createAssetSourceFiles($jsFiles);
        $regularAssetBundleClassName = $this->declareAssetBundleClass([
            'class' => 'RegularAsset',
            'css' => array_keys($cssFiles),
            'js' => array_keys($jsFiles),
            'depends' => [
                $externalAssetBundleClassName,
            ],
        ]);
        $bundles = [
            $regularAssetBundleClassName,
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';

        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
        $this->createCompressConfigFile($configFile, $bundles);

        // When :
        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        // Then :
        $this->assertFileExists($bundleFile, 'Unable to create output bundle file!');
        $compressedBundleConfig = require $bundleFile;
        $this->assertIsArray($compressedBundleConfig, 'Output bundle file has incorrect format!');
        $this->assertArrayHasKey($externalAssetBundleClassName, $compressedBundleConfig, 'External bundle is lost!');

        $compressedExternalAssetConfig = $compressedBundleConfig[$externalAssetBundleClassName];
        $this->assertEquals($externalAssetConfig['js'], $compressedExternalAssetConfig['js'], 'External bundle js is lost!');
        $this->assertEquals($externalAssetConfig['css'], $compressedExternalAssetConfig['css'], 'External bundle css is lost!');

        $compressedRegularAssetConfig = $compressedBundleConfig[$regularAssetBundleClassName];
        $this->assertContains(
            $externalAssetBundleClassName,
            $compressedRegularAssetConfig['depends'],
            'Dependency on external bundle is lost!',
        );
    }

    /**
     * @depends testActionCompress
     *
     * @see https://github.com/yiisoft/yii2/issues/7539
     */
    public function testDetectCircularDependency()
    {
        // Given :
        $namespace = __NAMESPACE__;

        $this->declareAssetBundleClass([
            'namespace' => $namespace,
            'class' => 'AssetStart',
            'depends' => [
                $namespace . '\AssetA',
            ],
        ]);
        $this->declareAssetBundleClass([
            'namespace' => $namespace,
            'class' => 'AssetA',
            'depends' => [
                $namespace . '\AssetB',
            ],
        ]);
        $this->declareAssetBundleClass([
            'namespace' => $namespace,
            'class' => 'AssetB',
            'depends' => [
                $namespace . '\AssetC',
            ],
        ]);
        $this->declareAssetBundleClass([
            'namespace' => $namespace,
            'class' => 'AssetC',
            'depends' => [
                $namespace . '\AssetA',
            ],
        ]);

        $bundles = [
            $namespace . '\AssetStart',
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';

        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
        $this->createCompressConfigFile($configFile, $bundles);

        // Assert :
        $expectedExceptionMessage = ": {$namespace}\AssetA -> {$namespace}\AssetB -> {$namespace}\AssetC -> {$namespace}\AssetA";
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage($expectedExceptionMessage);

        // When :
        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);
    }

    /**
     * Data provider for [[testAdjustCssUrl()]].
     * @return array test data.
     */
    public function adjustCssUrlDataProvider()
    {
        return [
            [
                '.published-same-dir-class {background-image: url(published_same_dir.png);}',
                '/test/base/path/assets/input',
                '/test/base/path/assets/output',
                '.published-same-dir-class {background-image: url(../input/published_same_dir.png);}',
            ],
            [
                '.published-relative-dir-class {background-image: url(../img/published_relative_dir.png);}',
                '/test/base/path/assets/input',
                '/test/base/path/assets/output',
                '.published-relative-dir-class {background-image: url(../img/published_relative_dir.png);}',
            ],
            [
                '.static-same-dir-class {background-image: url(\'static_same_dir.png\');}',
                '/test/base/path/css',
                '/test/base/path/assets/output',
                '.static-same-dir-class {background-image: url(\'../../css/static_same_dir.png\');}',
            ],
            [
                '.static-relative-dir-class {background-image: url("../img/static_relative_dir.png");}',
                '/test/base/path/css',
                '/test/base/path/assets/output',
                '.static-relative-dir-class {background-image: url("../../img/static_relative_dir.png");}',
            ],
            [
                '.absolute-url-class {background-image: url(http://domain.com/img/image.gif);}',
                '/test/base/path/assets/input',
                '/test/base/path/assets/output',
                '.absolute-url-class {background-image: url(http://domain.com/img/image.gif);}',
            ],
            [
                '.absolute-url-secure-class {background-image: url(https://secure.domain.com/img/image.gif);}',
                '/test/base/path/assets/input',
                '/test/base/path/assets/output',
                '.absolute-url-secure-class {background-image: url(https://secure.domain.com/img/image.gif);}',
            ],
            [
                "@font-face {
                src: url('../fonts/glyphicons-halflings-regular.eot');
                src: url('../fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype');
                }",
                '/test/base/path/assets/input/css',
                '/test/base/path/assets/output',
                "@font-face {
                src: url('../input/fonts/glyphicons-halflings-regular.eot');
                src: url('../input/fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype');
                }",
            ],
            [
                "@font-face {
                src: url('../fonts/glyphicons-halflings-regular.eot');
                src: url('../fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype');
                }",
                '/test/base/path/assets/input/css',
                '/test/base/path/assets',
                "@font-face {
                src: url('input/fonts/glyphicons-halflings-regular.eot');
                src: url('input/fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype');
                }",
            ],
            [
                "@font-face {
                src: url(data:application/x-font-ttf;charset=utf-8;base64,AAEAAAALAIAAAwAwT==) format('truetype');
                }",
                '/test/base/path/assets/input/css',
                '/test/base/path/assets/output',
                "@font-face {
                src: url(data:application/x-font-ttf;charset=utf-8;base64,AAEAAAALAIAAAwAwT==) format('truetype');
                }",
            ],
            [
                '.published-same-dir-class {background-image: url(published_same_dir.png);}',
                'C:\test\base\path\assets\input',
                'C:\test\base\path\assets\output',
                '.published-same-dir-class {background-image: url(../input/published_same_dir.png);}',
            ],
            [
                '.static-root-relative-class {background-image: url(\'/images/static_root_relative.png\');}',
                '/test/base/path/css',
                '/test/base/path/assets/output',
                '.static-root-relative-class {background-image: url(\'/images/static_root_relative.png\');}',
            ],
            [
                '.published-relative-dir-class {background-image: url(../img/same_relative_dir.png);}',
                '/test/base/path/assets/css',
                '/test/base/path/assets/css',
                '.published-relative-dir-class {background-image: url(../img/same_relative_dir.png);}',
            ],
            [
                'img {clip-path: url(#xxx)}',
                '/test/base/path/css',
                '/test/base/path/assets/output',
                'img {clip-path: url(#xxx)}',
            ],
        ];
    }

    /**
     * @dataProvider adjustCssUrlDataProvider
     *
     * @param $cssContent
     * @param $inputFilePath
     * @param $outputFilePath
     * @param $expectedCssContent
     */
    public function testAdjustCssUrl($cssContent, $inputFilePath, $outputFilePath, $expectedCssContent)
    {
        $adjustedCssContent = $this->invokeAssetControllerMethod('adjustCssUrl', [$cssContent, $inputFilePath, $outputFilePath]);

        $this->assertEquals($expectedCssContent, $adjustedCssContent, 'Unable to adjust CSS correctly!');
    }

    /**
     * Data provider for [[testFindRealPath()]].
     * @return array test data
     */
    public function findRealPathDataProvider()
    {
        return [
            [
                '/linux/absolute/path',
                '/linux/absolute/path',
            ],
            [
                '/linux/up/../path',
                '/linux/path',
            ],
            [
                '/linux/twice/up/../../path',
                '/linux/path',
            ],
            [
                '/linux/../mix/up/../path',
                '/mix/path',
            ],
            [
                'C:\\windows\\absolute\\path',
                'C:\\windows\\absolute\\path',
            ],
            [
                'C:\\windows\\up\\..\\path',
                'C:\\windows\\path',
            ],
        ];
    }

    /**
     * @dataProvider findRealPathDataProvider
     *
     * @param string $sourcePath
     * @param string $expectedRealPath
     */
    public function testFindRealPath($sourcePath, $expectedRealPath)
    {
        $expectedRealPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $expectedRealPath);
        $realPath = $this->invokeAssetControllerMethod('findRealPath', [$sourcePath]);
        $this->assertEquals($expectedRealPath, $realPath);
    }

    /**
     * @depends testActionCompress
     *
     * @see https://github.com/yiisoft/yii2/issues/9708
     */
    public function testActionCompressDeleteSource()
    {
        // Given :
        $cssFiles = [
            'css/test_body.css' => 'body {
                padding-top: 20px;
                padding-bottom: 60px;
            }',
        ];
        $this->createAssetSourceFiles($cssFiles);

        $jsFiles = [
            'js/test_alert.js' => "function test() {
                alert('Test message');
            }",
        ];
        $sourcePath = $this->testFilePath . DIRECTORY_SEPARATOR . 'source';
        $this->createAssetSourceFiles($cssFiles, $sourcePath);
        $this->createAssetSourceFiles($jsFiles, $sourcePath);
        $assetBundleClassName = $this->declareAssetBundleClass([
            'class' => 'AssetDelete',
            'css' => array_keys($cssFiles),
            'js' => array_keys($jsFiles),
            'basePath' => null,
            'sourcePath' => $sourcePath,
        ]);

        $bundles = [
            $assetBundleClassName,
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';

        // Keep source :
        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config_no_source_delete.php';
        $this->createCompressConfigFile($configFile, $bundles, [
            'deleteSource' => false,
        ]);

        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        $files = FileHelper::findFiles($this->testAssetsBasePath, [
            'only' => [
                'test_body.css',
                'test_alert.js',
            ],
        ]);
        $this->assertNotEmpty($files);

        // Delete source :
        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config_source_delete.php';
        $this->createCompressConfigFile($configFile, $bundles, [
            'deleteSource' => true,
        ]);

        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        $files = FileHelper::findFiles($this->testAssetsBasePath, [
            'only' => [
                'test_body.css',
                'test_alert.js',
            ],
        ]);
        $this->assertEmpty($files);
    }

    /**
     * @depends testActionCompress
     *
     * @see https://github.com/yiisoft/yii2/issues/10567
     */
    public function testActionCompressOverrideAsExternal()
    {
        // Given :
        $cssFiles = [
            'css/override_external.css' => 'body {
                padding-top: 20px;
                padding-bottom: 60px;
            }',
        ];
        $this->createAssetSourceFiles($cssFiles);

        $jsFiles = [
            'js/override_external.js' => "function test() {
                alert('Test message');
            }",
        ];
        //$this->createAssetSourceFiles($cssFiles, $sourcePath);
        //$this->createAssetSourceFiles($jsFiles, $sourcePath);
        $assetBundleClassName = $this->declareAssetBundleClass([
            'class' => 'AssetOverrideExternal',
            'css' => array_keys($cssFiles),
            'js' => array_keys($jsFiles),
        ]);

        $bundles = [
            $assetBundleClassName,
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle_override_as_external.php';

        // Keep source :
        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config_override_as_external.php';
        $assetBundleOverrideConfig = [
            'sourcePath' => null,
            'basePath' => null,
            'baseUrl' => null,
            'css' => [
                '//some.cdn.com/js/override_external.css',
            ],
            'js' => [
                '//some.cdn.com/js/override_external.js',
            ],
        ];
        $this->createCompressConfigFile($configFile, $bundles, [
            'assetManager' => [
                'bundles' => [
                    $assetBundleClassName => $assetBundleOverrideConfig,
                ],
            ],
        ]);

        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        $bundlesConfig = require $bundleFile;

        $this->assertEquals($assetBundleOverrideConfig['css'], $bundlesConfig[$assetBundleClassName]['css']);
        $this->assertEquals($assetBundleOverrideConfig['js'], $bundlesConfig[$assetBundleClassName]['js']);
    }
}

/**
 * Mock class for [[\yii\console\controllers\AssetController]].
 */
class AssetControllerMock extends AssetController
{
    use StdOutBufferControllerTrait;
}
