<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\web\AssetBundle;

/**
 * Allows you to combine and compress your JavaScript and CSS files.
 *
 * Usage:
 *
 * 1. Create a configuration file using the `template` action:
 *
 *    yii asset/template /path/to/myapp/config.php
 *
 * 2. Edit the created config file, adjusting it for your web application needs.
 * 3. Run the 'compress' action, using created config:
 *
 *    yii asset /path/to/myapp/config.php /path/to/myapp/config/assets_compressed.php
 *
 * 4. Adjust your web application config to use compressed assets.
 *
 * Note: in the console environment some [path aliases](guide:concept-aliases) like `@webroot` and `@web` may not exist,
 * so corresponding paths inside the configuration should be specified directly.
 *
 * Note: by default this command relies on an external tools to perform actual files compression,
 * check [[jsCompressor]] and [[cssCompressor]] for more details.
 *
 * @property \yii\web\AssetManager $assetManager Asset manager instance. Note that the type of this property
 * differs in getter and setter. See [[getAssetManager()]] and [[setAssetManager()]] for details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AssetController extends Controller
{
    /**
     * @var string controller default action ID.
     */
    public $defaultAction = 'compress';
    /**
     * @var array list of asset bundles to be compressed.
     */
    public $bundles = [];
    /**
     * @var array list of asset bundles, which represents output compressed files.
     * You can specify the name of the output compressed file using 'css' and 'js' keys:
     * For example:
     *
     * ```php
     * 'app\config\AllAsset' => [
     *     'js' => 'js/all-{hash}.js',
     *     'css' => 'css/all-{hash}.css',
     *     'depends' => [ ... ],
     * ]
     * ```
     *
     * File names can contain placeholder "{hash}", which will be filled by the hash of the resulting file.
     *
     * You may specify several target bundles in order to compress different groups of assets.
     * In this case you should use 'depends' key to specify, which bundles should be covered with particular
     * target bundle. You may leave 'depends' to be empty for single bundle, which will compress all remaining
     * bundles in this case.
     * For example:
     *
     * ```php
     * 'allShared' => [
     *     'js' => 'js/all-shared-{hash}.js',
     *     'css' => 'css/all-shared-{hash}.css',
     *     'depends' => [
     *         // Include all assets shared between 'backend' and 'frontend'
     *         'yii\web\YiiAsset',
     *         'app\assets\SharedAsset',
     *     ],
     * ],
     * 'allBackEnd' => [
     *     'js' => 'js/all-{hash}.js',
     *     'css' => 'css/all-{hash}.css',
     *     'depends' => [
     *         // Include only 'backend' assets:
     *         'app\assets\AdminAsset'
     *     ],
     * ],
     * 'allFrontEnd' => [
     *     'js' => 'js/all-{hash}.js',
     *     'css' => 'css/all-{hash}.css',
     *     'depends' => [], // Include all remaining assets
     * ],
     * ```
     */
    public $targets = [];
    /**
     * @var string|callable JavaScript file compressor.
     * If a string, it is treated as shell command template, which should contain
     * placeholders {from} - source file name - and {to} - output file name.
     * Otherwise, it is treated as PHP callback, which should perform the compression.
     *
     * Default value relies on usage of "Closure Compiler"
     * @see https://developers.google.com/closure/compiler/
     */
    public $jsCompressor = 'java -jar compiler.jar --js {from} --js_output_file {to}';
    /**
     * @var string|callable CSS file compressor.
     * If a string, it is treated as shell command template, which should contain
     * placeholders {from} - source file name - and {to} - output file name.
     * Otherwise, it is treated as PHP callback, which should perform the compression.
     *
     * Default value relies on usage of "YUI Compressor"
     * @see https://github.com/yui/yuicompressor/
     */
    public $cssCompressor = 'java -jar yuicompressor.jar --type css {from} -o {to}';
    /**
     * @var bool whether to delete asset source files after compression.
     * This option affects only those bundles, which have [[\yii\web\AssetBundle::sourcePath]] is set.
     * @since 2.0.10
     */
    public $deleteSource = false;

    /**
     * @var array|\yii\web\AssetManager [[\yii\web\AssetManager]] instance or its array configuration, which will be used
     * for assets processing.
     */
    private $_assetManager = [];


    /**
     * Returns the asset manager instance.
     * @throws \yii\console\Exception on invalid configuration.
     * @return \yii\web\AssetManager asset manager instance.
     */
    public function getAssetManager()
    {
        if (!is_object($this->_assetManager)) {
            $options = $this->_assetManager;
            if (!isset($options['class'])) {
                $options['class'] = 'yii\\web\\AssetManager';
            }
            if (!isset($options['basePath'])) {
                throw new Exception("Please specify 'basePath' for the 'assetManager' option.");
            }
            if (!isset($options['baseUrl'])) {
                throw new Exception("Please specify 'baseUrl' for the 'assetManager' option.");
            }

            if (!isset($options['forceCopy'])) {
                $options['forceCopy'] = true;
            }

            $this->_assetManager = Yii::createObject($options);
        }

        return $this->_assetManager;
    }

    /**
     * Sets asset manager instance or configuration.
     * @param \yii\web\AssetManager|array $assetManager asset manager instance or its array configuration.
     * @throws \yii\console\Exception on invalid argument type.
     */
    public function setAssetManager($assetManager)
    {
        if (is_scalar($assetManager)) {
            throw new Exception('"' . get_class($this) . '::assetManager" should be either object or array - "' . gettype($assetManager) . '" given.');
        }
        $this->_assetManager = $assetManager;
    }

    /**
     * Combines and compresses the asset files according to the given configuration.
     * During the process new asset bundle configuration file will be created.
     * You should replace your original asset bundle configuration with this file in order to use compressed files.
     * @param string $configFile configuration file name.
     * @param string $bundleFile output asset bundles configuration file name.
     */
    public function actionCompress($configFile, $bundleFile)
    {
        $this->loadConfiguration($configFile);
        $bundles = $this->loadBundles($this->bundles);
        $targets = $this->loadTargets($this->targets, $bundles);
        foreach ($targets as $name => $target) {
            $this->stdout("Creating output bundle '{$name}':\n");
            if (!empty($target->js)) {
                $this->buildTarget($target, 'js', $bundles);
            }
            if (!empty($target->css)) {
                $this->buildTarget($target, 'css', $bundles);
            }
            $this->stdout("\n");
        }

        $targets = $this->adjustDependency($targets, $bundles);
        $this->saveTargets($targets, $bundleFile);

        if ($this->deleteSource) {
            $this->deletePublishedAssets($bundles);
        }
    }

    /**
     * Applies configuration from the given file to self instance.
     * @param string $configFile configuration file name.
     * @throws \yii\console\Exception on failure.
     */
    protected function loadConfiguration($configFile)
    {
        $this->stdout("Loading configuration from '{$configFile}'...\n");
        foreach (require($configFile) as $name => $value) {
            if (property_exists($this, $name) || $this->canSetProperty($name)) {
                $this->$name = $value;
            } else {
                throw new Exception("Unknown configuration option: $name");
            }
        }

        $this->getAssetManager(); // check if asset manager configuration is correct
    }

    /**
     * Creates full list of source asset bundles.
     * @param string[] $bundles list of asset bundle names
     * @return \yii\web\AssetBundle[] list of source asset bundles.
     */
    protected function loadBundles($bundles)
    {
        $this->stdout("Collecting source bundles information...\n");

        $am = $this->getAssetManager();
        $result = [];
        foreach ($bundles as $name) {
            $result[$name] = $am->getBundle($name);
        }
        foreach ($result as $bundle) {
            $this->loadDependency($bundle, $result);
        }

        return $result;
    }

    /**
     * Loads asset bundle dependencies recursively.
     * @param \yii\web\AssetBundle $bundle bundle instance
     * @param array $result already loaded bundles list.
     * @throws Exception on failure.
     */
    protected function loadDependency($bundle, &$result)
    {
        $am = $this->getAssetManager();
        foreach ($bundle->depends as $name) {
            if (!isset($result[$name])) {
                $dependencyBundle = $am->getBundle($name);
                $result[$name] = false;
                $this->loadDependency($dependencyBundle, $result);
                $result[$name] = $dependencyBundle;
            } elseif ($result[$name] === false) {
                throw new Exception("A circular dependency is detected for bundle '{$name}': " . $this->composeCircularDependencyTrace($name, $result) . '.');
            }
        }
    }

    /**
     * Creates full list of output asset bundles.
     * @param array $targets output asset bundles configuration.
     * @param \yii\web\AssetBundle[] $bundles list of source asset bundles.
     * @return \yii\web\AssetBundle[] list of output asset bundles.
     * @throws Exception on failure.
     */
    protected function loadTargets($targets, $bundles)
    {
        // build the dependency order of bundles
        $registered = [];
        foreach ($bundles as $name => $bundle) {
            $this->registerBundle($bundles, $name, $registered);
        }
        $bundleOrders = array_combine(array_keys($registered), range(0, count($bundles) - 1));

        // fill up the target which has empty 'depends'.
        $referenced = [];
        foreach ($targets as $name => $target) {
            if (empty($target['depends'])) {
                if (!isset($all)) {
                    $all = $name;
                } else {
                    throw new Exception("Only one target can have empty 'depends' option. Found two now: $all, $name");
                }
            } else {
                foreach ($target['depends'] as $bundle) {
                    if (!isset($referenced[$bundle])) {
                        $referenced[$bundle] = $name;
                    } else {
                        throw new Exception("Target '{$referenced[$bundle]}' and '$name' cannot contain the bundle '$bundle' at the same time.");
                    }
                }
            }
        }
        if (isset($all)) {
            $targets[$all]['depends'] = array_diff(array_keys($registered), array_keys($referenced));
        }

        // adjust the 'depends' order for each target according to the dependency order of bundles
        // create an AssetBundle object for each target
        foreach ($targets as $name => $target) {
            if (!isset($target['basePath'])) {
                throw new Exception("Please specify 'basePath' for the '$name' target.");
            }
            if (!isset($target['baseUrl'])) {
                throw new Exception("Please specify 'baseUrl' for the '$name' target.");
            }
            usort($target['depends'], function ($a, $b) use ($bundleOrders) {
                if ($bundleOrders[$a] == $bundleOrders[$b]) {
                    return 0;
                } else {
                    return $bundleOrders[$a] > $bundleOrders[$b] ? 1 : -1;
                }
            });
            if (!isset($target['class'])) {
                $target['class'] = $name;
            }
            $targets[$name] = Yii::createObject($target);
        }

        return $targets;
    }

    /**
     * Builds output asset bundle.
     * @param \yii\web\AssetBundle $target output asset bundle
     * @param string $type either 'js' or 'css'.
     * @param \yii\web\AssetBundle[] $bundles source asset bundles.
     * @throws Exception on failure.
     */
    protected function buildTarget($target, $type, $bundles)
    {
        $inputFiles = [];
        foreach ($target->depends as $name) {
            if (isset($bundles[$name])) {
                if (!$this->isBundleExternal($bundles[$name])) {
                    foreach ($bundles[$name]->$type as $file) {
                        if (is_array($file)) {
                            $inputFiles[] = $bundles[$name]->basePath . '/' . $file[0];
                        } else {
                            $inputFiles[] = $bundles[$name]->basePath . '/' . $file;
                        }
                    }
                }
            } else {
                throw new Exception("Unknown bundle: '{$name}'");
            }
        }

        if (empty($inputFiles)) {
            $target->$type = [];
        } else {
            FileHelper::createDirectory($target->basePath, $this->getAssetManager()->dirMode);
            $tempFile = $target->basePath . '/' . strtr($target->$type, ['{hash}' => 'temp']);

            if ($type === 'js') {
                $this->compressJsFiles($inputFiles, $tempFile);
            } else {
                $this->compressCssFiles($inputFiles, $tempFile);
            }

            $targetFile = strtr($target->$type, ['{hash}' => md5_file($tempFile)]);
            $outputFile = $target->basePath . '/' . $targetFile;
            rename($tempFile, $outputFile);
            $target->$type = [$targetFile];
        }
    }

    /**
     * Adjust dependencies between asset bundles in the way source bundles begin to depend on output ones.
     * @param \yii\web\AssetBundle[] $targets output asset bundles.
     * @param \yii\web\AssetBundle[] $bundles source asset bundles.
     * @return \yii\web\AssetBundle[] output asset bundles.
     */
    protected function adjustDependency($targets, $bundles)
    {
        $this->stdout("Creating new bundle configuration...\n");

        $map = [];
        foreach ($targets as $name => $target) {
            foreach ($target->depends as $bundle) {
                $map[$bundle] = $name;
            }
        }

        foreach ($targets as $name => $target) {
            $depends = [];
            foreach ($target->depends as $bn) {
                foreach ($bundles[$bn]->depends as $bundle) {
                    $depends[$map[$bundle]] = true;
                }
            }
            unset($depends[$name]);
            $target->depends = array_keys($depends);
        }

        // detect possible circular dependencies
        foreach ($targets as $name => $target) {
            $registered = [];
            $this->registerBundle($targets, $name, $registered);
        }

        foreach ($map as $bundle => $target) {
            $sourceBundle = $bundles[$bundle];
            $depends = $sourceBundle->depends;
            if (!$this->isBundleExternal($sourceBundle)) {
                $depends[] = $target;
            }
            $targetBundle = clone $sourceBundle;
            $targetBundle->depends = $depends;
            $targets[$bundle] = $targetBundle;
        }

        return $targets;
    }

    /**
     * Registers asset bundles including their dependencies.
     * @param \yii\web\AssetBundle[] $bundles asset bundles list.
     * @param string $name bundle name.
     * @param array $registered stores already registered names.
     * @throws Exception if circular dependency is detected.
     */
    protected function registerBundle($bundles, $name, &$registered)
    {
        if (!isset($registered[$name])) {
            $registered[$name] = false;
            $bundle = $bundles[$name];
            foreach ($bundle->depends as $depend) {
                $this->registerBundle($bundles, $depend, $registered);
            }
            unset($registered[$name]);
            $registered[$name] = $bundle;
        } elseif ($registered[$name] === false) {
            throw new Exception("A circular dependency is detected for target '{$name}': " . $this->composeCircularDependencyTrace($name, $registered) . '.');
        }
    }

    /**
     * Saves new asset bundles configuration.
     * @param \yii\web\AssetBundle[] $targets list of asset bundles to be saved.
     * @param string $bundleFile output file name.
     * @throws \yii\console\Exception on failure.
     */
    protected function saveTargets($targets, $bundleFile)
    {
        $array = [];
        foreach ($targets as $name => $target) {
            if (isset($this->targets[$name])) {
                $array[$name] = array_merge($this->targets[$name], [
                    'class' => get_class($target),
                    'sourcePath' => null,
                    'basePath' => $this->targets[$name]['basePath'],
                    'baseUrl' => $this->targets[$name]['baseUrl'],
                    'js' => $target->js,
                    'css' => $target->css,
                    'depends' => [],
                ]);
            } else {
                if ($this->isBundleExternal($target)) {
                    $array[$name] = $this->composeBundleConfig($target);
                } else {
                    $array[$name] = [
                        'sourcePath' => null,
                        'js' => [],
                        'css' => [],
                        'depends' => $target->depends,
                    ];
                }
            }
        }
        $array = VarDumper::export($array);
        $version = date('Y-m-d H:i:s', time());
        $bundleFileContent = <<<EOD
<?php
/**
 * This file is generated by the "yii {$this->id}" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version {$version}
 */
return {$array};
EOD;
        if (!file_put_contents($bundleFile, $bundleFileContent)) {
            throw new Exception("Unable to write output bundle configuration at '{$bundleFile}'.");
        }
        $this->stdout("Output bundle configuration created at '{$bundleFile}'.\n", Console::FG_GREEN);
    }

    /**
     * Compresses given JavaScript files and combines them into the single one.
     * @param array $inputFiles list of source file names.
     * @param string $outputFile output file name.
     * @throws \yii\console\Exception on failure
     */
    protected function compressJsFiles($inputFiles, $outputFile)
    {
        if (empty($inputFiles)) {
            return;
        }
        $this->stdout("  Compressing JavaScript files...\n");
        if (is_string($this->jsCompressor)) {
            $tmpFile = $outputFile . '.tmp';
            $this->combineJsFiles($inputFiles, $tmpFile);
            $this->stdout(shell_exec(strtr($this->jsCompressor, [
                '{from}' => escapeshellarg($tmpFile),
                '{to}' => escapeshellarg($outputFile),
            ])));
            @unlink($tmpFile);
        } else {
            call_user_func($this->jsCompressor, $this, $inputFiles, $outputFile);
        }
        if (!file_exists($outputFile)) {
            throw new Exception("Unable to compress JavaScript files into '{$outputFile}'.");
        }
        $this->stdout("  JavaScript files compressed into '{$outputFile}'.\n");
    }

    /**
     * Compresses given CSS files and combines them into the single one.
     * @param array $inputFiles list of source file names.
     * @param string $outputFile output file name.
     * @throws \yii\console\Exception on failure
     */
    protected function compressCssFiles($inputFiles, $outputFile)
    {
        if (empty($inputFiles)) {
            return;
        }
        $this->stdout("  Compressing CSS files...\n");
        if (is_string($this->cssCompressor)) {
            $tmpFile = $outputFile . '.tmp';
            $this->combineCssFiles($inputFiles, $tmpFile);
            $this->stdout(shell_exec(strtr($this->cssCompressor, [
                '{from}' => escapeshellarg($tmpFile),
                '{to}' => escapeshellarg($outputFile),
            ])));
            @unlink($tmpFile);
        } else {
            call_user_func($this->cssCompressor, $this, $inputFiles, $outputFile);
        }
        if (!file_exists($outputFile)) {
            throw new Exception("Unable to compress CSS files into '{$outputFile}'.");
        }
        $this->stdout("  CSS files compressed into '{$outputFile}'.\n");
    }

    /**
     * Combines JavaScript files into a single one.
     * @param array $inputFiles source file names.
     * @param string $outputFile output file name.
     * @throws \yii\console\Exception on failure.
     */
    public function combineJsFiles($inputFiles, $outputFile)
    {
        $content = '';
        foreach ($inputFiles as $file) {
            $content .= "/*** BEGIN FILE: $file ***/\n"
                . file_get_contents($file)
                . "/*** END FILE: $file ***/\n";
        }
        if (!file_put_contents($outputFile, $content)) {
            throw new Exception("Unable to write output JavaScript file '{$outputFile}'.");
        }
    }

    /**
     * Combines CSS files into a single one.
     * @param array $inputFiles source file names.
     * @param string $outputFile output file name.
     * @throws \yii\console\Exception on failure.
     */
    public function combineCssFiles($inputFiles, $outputFile)
    {
        $content = '';
        $outputFilePath = dirname($this->findRealPath($outputFile));
        foreach ($inputFiles as $file) {
            $content .= "/*** BEGIN FILE: $file ***/\n"
                . $this->adjustCssUrl(file_get_contents($file), dirname($this->findRealPath($file)), $outputFilePath)
                . "/*** END FILE: $file ***/\n";
        }
        if (!file_put_contents($outputFile, $content)) {
            throw new Exception("Unable to write output CSS file '{$outputFile}'.");
        }
    }

    /**
     * Adjusts CSS content allowing URL references pointing to the original resources.
     * @param string $cssContent source CSS content.
     * @param string $inputFilePath input CSS file name.
     * @param string $outputFilePath output CSS file name.
     * @return string adjusted CSS content.
     */
    protected function adjustCssUrl($cssContent, $inputFilePath, $outputFilePath)
    {
        $inputFilePath = str_replace('\\', '/', $inputFilePath);
        $outputFilePath = str_replace('\\', '/', $outputFilePath);

        $sharedPathParts = [];
        $inputFilePathParts = explode('/', $inputFilePath);
        $inputFilePathPartsCount = count($inputFilePathParts);
        $outputFilePathParts = explode('/', $outputFilePath);
        $outputFilePathPartsCount = count($outputFilePathParts);
        for ($i =0; $i < $inputFilePathPartsCount && $i < $outputFilePathPartsCount; $i++) {
            if ($inputFilePathParts[$i] == $outputFilePathParts[$i]) {
                $sharedPathParts[] = $inputFilePathParts[$i];
            } else {
                break;
            }
        }
        $sharedPath = implode('/', $sharedPathParts);

        $inputFileRelativePath = trim(str_replace($sharedPath, '', $inputFilePath), '/');
        $outputFileRelativePath = trim(str_replace($sharedPath, '', $outputFilePath), '/');
        if (empty($inputFileRelativePath)) {
            $inputFileRelativePathParts = [];
        } else {
            $inputFileRelativePathParts = explode('/', $inputFileRelativePath);
        }
        if (empty($outputFileRelativePath)) {
            $outputFileRelativePathParts = [];
        } else {
            $outputFileRelativePathParts = explode('/', $outputFileRelativePath);
        }

        $callback = function ($matches) use ($inputFileRelativePathParts, $outputFileRelativePathParts) {
            $fullMatch = $matches[0];
            $inputUrl = $matches[1];

            if (strpos($inputUrl, '/') === 0 || strpos($inputUrl, '#') === 0 || preg_match('/^https?:\/\//i', $inputUrl) || preg_match('/^data:/i', $inputUrl)) {
                return $fullMatch;
            }
            if ($inputFileRelativePathParts === $outputFileRelativePathParts) {
                return $fullMatch;
            }

            if (empty($outputFileRelativePathParts)) {
                $outputUrlParts = [];
            } else {
                $outputUrlParts = array_fill(0, count($outputFileRelativePathParts), '..');
            }
            $outputUrlParts = array_merge($outputUrlParts, $inputFileRelativePathParts);

            if (strpos($inputUrl, '/') !== false) {
                $inputUrlParts = explode('/', $inputUrl);
                foreach ($inputUrlParts as $key => $inputUrlPart) {
                    if ($inputUrlPart === '..') {
                        array_pop($outputUrlParts);
                        unset($inputUrlParts[$key]);
                    }
                }
                $outputUrlParts[] = implode('/', $inputUrlParts);
            } else {
                $outputUrlParts[] = $inputUrl;
            }
            $outputUrl = implode('/', $outputUrlParts);

            return str_replace($inputUrl, $outputUrl, $fullMatch);
        };

        $cssContent = preg_replace_callback('/url\(["\']?([^)^"^\']*)["\']?\)/i', $callback, $cssContent);

        return $cssContent;
    }

    /**
     * Creates template of configuration file for [[actionCompress]].
     * @param string $configFile output file name.
     * @return int CLI exit code
     * @throws \yii\console\Exception on failure.
     */
    public function actionTemplate($configFile)
    {
        $jsCompressor = VarDumper::export($this->jsCompressor);
        $cssCompressor = VarDumper::export($this->cssCompressor);

        $template = <<<EOD
<?php
/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
// Yii::setAlias('@webroot', __DIR__ . '/../web');
// Yii::setAlias('@web', '/');

return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => {$jsCompressor},
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => {$cssCompressor},
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    // The list of asset bundles to compress:
    'bundles' => [
        // 'app\assets\AppAsset',
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
        //'basePath' => '@webroot/assets',
        //'baseUrl' => '@web/assets',
    ],
];
EOD;
        if (file_exists($configFile)) {
            if (!$this->confirm("File '{$configFile}' already exists. Do you wish to overwrite it?")) {
                return self::EXIT_CODE_NORMAL;
            }
        }
        if (!file_put_contents($configFile, $template)) {
            throw new Exception("Unable to write template file '{$configFile}'.");
        } else {
            $this->stdout("Configuration file template created at '{$configFile}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        }
    }

    /**
     * Returns canonicalized absolute pathname.
     * Unlike regular `realpath()` this method does not expand symlinks and does not check path existence.
     * @param string $path raw path
     * @return string canonicalized absolute pathname
     */
    private function findRealPath($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);

        $realPathParts = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart === '..') {
                array_pop($realPathParts);
            } else {
                $realPathParts[] = $pathPart;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $realPathParts);
    }

    /**
     * @param AssetBundle $bundle
     * @return bool whether asset bundle external or not.
     */
    private function isBundleExternal($bundle)
    {
        return (empty($bundle->sourcePath) && empty($bundle->basePath));
    }

    /**
     * @param AssetBundle $bundle asset bundle instance.
     * @return array bundle configuration.
     */
    private function composeBundleConfig($bundle)
    {
        $config = Yii::getObjectVars($bundle);
        $config['class'] = get_class($bundle);
        return $config;
    }

    /**
     * Composes trace info for bundle circular dependency.
     * @param string $circularDependencyName name of the bundle, which have circular dependency
     * @param array $registered list of bundles registered while detecting circular dependency.
     * @return string bundle circular dependency trace string.
     */
    private function composeCircularDependencyTrace($circularDependencyName, array $registered)
    {
        $dependencyTrace = [];
        $startFound = false;
        foreach ($registered as $name => $value) {
            if ($name === $circularDependencyName) {
                $startFound = true;
            }
            if ($startFound && $value === false) {
                $dependencyTrace[] = $name;
            }
        }
        $dependencyTrace[] = $circularDependencyName;
        return implode(' -> ', $dependencyTrace);
    }

    /**
     * Deletes bundle asset files, which have been published from `sourcePath`.
     * @param \yii\web\AssetBundle[] $bundles asset bundles to be processed.
     * @since 2.0.10
     */
    private function deletePublishedAssets($bundles)
    {
        $this->stdout("Deleting source files...\n");

        if ($this->getAssetManager()->linkAssets) {
            $this->stdout("`AssetManager::linkAssets` option is enabled. Deleting of source files canceled.\n", Console::FG_YELLOW);
            return;
        }

        foreach ($bundles as $bundle) {
            if ($bundle->sourcePath !== null) {
                foreach ($bundle->js as $jsFile) {
                    @unlink($bundle->basePath . DIRECTORY_SEPARATOR . $jsFile);
                }
                foreach ($bundle->css as $cssFile) {
                    @unlink($bundle->basePath . DIRECTORY_SEPARATOR . $cssFile);
                }
            }
        }

        $this->stdout("Source files deleted.\n", Console::FG_GREEN);
    }
}
