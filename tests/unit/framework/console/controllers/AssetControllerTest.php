<?php

namespace yiiunit\framework\console\controllers;

use yii\helpers\StringHelper;
use yiiunit\TestCase;
use yii\console\controllers\AssetController;
use Yii;

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

    public function setUp()
    {
        $this->mockApplication();
        $this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . str_replace('\\', '_', get_class($this));
        $this->createDir($this->testFilePath);
        $this->testAssetsBasePath = $this->testFilePath . DIRECTORY_SEPARATOR . 'assets';
        $this->createDir($this->testAssetsBasePath);
    }

    public function tearDown()
    {
        $this->removeDir($this->testFilePath);
    }

    /**
     * Creates directory.
     * @param string $dirName directory full name.
     */
    protected function createDir($dirName)
    {
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * Removes directory.
     * @param string $dirName directory full name
     */
    protected function removeDir($dirName)
    {
        if (!empty($dirName) && file_exists($dirName)) {
            exec("rm -rf {$dirName}");
        }
    }

    /**
     * Creates test asset controller instance.
     * @return AssetController
     */
    protected function createAssetController()
    {
        $module = $this->getMock('yii\\base\\Module', ['fake'], ['console']);
        $assetController = new AssetController('asset', $module);
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
        ob_start();
        ob_implicit_flush(false);
        $controller->run($actionID, $args);

        return ob_get_clean();
    }

    /**
     * Creates test compress config.
     * @param  array[] $bundles asset bundles config.
     * @return array   config array.
     */
    protected function createCompressConfig(array $bundles)
    {
        $className = $this->declareAssetBundleClass(['class' => 'AssetBundleAll']);
        $baseUrl = '/test';
        $config = [
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
        ];

        return $config;
    }

    /**
     * Creates test compress config file.
     * @param  string     $fileName output file name.
     * @param  array[]    $bundles  asset bundles config.
     * @throws \Exception on failure.
     */
    protected function createCompressConfigFile($fileName, array $bundles)
    {
        $content = '<?php return ' . var_export($this->createCompressConfig($bundles), true) . ';';
        if (file_put_contents($fileName, $content) <= 0) {
            throw new \Exception("Unable to create file '{$fileName}'!");
        }
    }

    /**
     * Creates test asset file.
     * @param  string     $fileRelativeName file name relative to [[testFilePath]]
     * @param  string     $content          file content
     * @throws \Exception on failure.
     */
    protected function createAssetSourceFile($fileRelativeName, $content)
    {
        $fileFullName = $this->testFilePath . DIRECTORY_SEPARATOR . $fileRelativeName;
        $this->createDir(dirname($fileFullName));
        if (file_put_contents($fileFullName, $content) <= 0) {
            throw new \Exception("Unable to create file '{$fileFullName}'!");
        }
    }

    /**
     * Creates a list of asset source files.
     * @param array $files assert source files in format: file/relative/name => fileContent
     */
    protected function createAssetSourceFiles(array $files)
    {
        foreach ($files as $name => $content) {
            $this->createAssetSourceFile($name, $content);
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
                'basePath' => $this->testFilePath,
                'baseUrl' => '',
                'css' => [],
                'js' => [],
                'depends' => [],
            ],
            $config
        );
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $config[$name] = var_export($value, true);
            }
        }

        $source = <<<EOL
namespace {$config['namespace']};

use yii\web\AssetBundle;

class {$config['class']} extends AssetBundle
{
    public \$basePath = '{$config['basePath']}';
    public \$baseUrl = '{$config['baseUrl']}';
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
        $this->assertTrue(file_exists($configFileName), 'Unable to create config file template!');
        $config = require($configFileName);
        $this->assertTrue(is_array($config), 'Invalid config created!');
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
            'js/test_sum_ab.js' => "function sumAB(a, b) {
                return a + b;
            }",
        ];
        $this->createAssetSourceFiles($jsFiles);
        $assetBundleClassName = $this->declareAssetBundleClass([
            'css' => array_keys($cssFiles),
            'js' => array_keys($jsFiles),
        ]);

        $bundles = [
            $assetBundleClassName
        ];
        $bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';

        $configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config2.php';
        $this->createCompressConfigFile($configFile, $bundles);

        // When :
        $this->runAssetControllerAction('compress', [$configFile, $bundleFile]);

        // Then :
        $this->assertTrue(file_exists($bundleFile), 'Unable to create output bundle file!');
        $this->assertTrue(is_array(require($bundleFile)), 'Output bundle file has incorrect format!');

        $compressedCssFileName = $this->testAssetsBasePath . DIRECTORY_SEPARATOR . 'all.css';
        $this->assertTrue(file_exists($compressedCssFileName), 'Unable to compress CSS files!');
        $compressedJsFileName = $this->testAssetsBasePath . DIRECTORY_SEPARATOR . 'all.js';
        $this->assertTrue(file_exists($compressedJsFileName), 'Unable to compress JS files!');

        $compressedCssFileContent = file_get_contents($compressedCssFileName);
        foreach ($cssFiles as $name => $content) {
            $this->assertContains($content, $compressedCssFileContent, "Source of '{$name}' is missing in combined file!");
        }
        $compressedJsFileContent = file_get_contents($compressedJsFileName);
        foreach ($jsFiles as $name => $content) {
            $this->assertContains($content, $compressedJsFileContent, "Source of '{$name}' is missing in combined file!");
        }
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
}
