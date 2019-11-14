<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\web\AssetBundle;

/**
 * 允许你合并和压缩你的 JavaScript 和 CSS 文件。
 *
 * 用法:
 *
 * 1. 使用 `template` 方法创建配置文件：
 *
 *    yii asset/template /path/to/myapp/config.php
 *
 * 2. 根据你的 web 应用的需要，编辑创建的配置文件。
 * 3. 使用创建的配置文件，运行 'compress' 动作:
 *
 *    yii asset /path/to/myapp/config.php /path/to/myapp/config/assets_compressed.php
 *
 * 4. 调整你的 web 应用程序配置以使用压缩资源
 *
 * 注意：在控制台环境中一些 [path alias](guide:concept-aliases) 像 `@webroot` 和 `@web` 可能不存在，
 * 因此应该直接指定配置中的相应路径。
 *
 * 注意：默认情况下这个命令依赖外部工具来执行实际的文件压缩，
 * 核实 [[jsCompressor]] 和 [[cssCompressor]] 获取详细信息。
 *
 * @property \yii\web\AssetManager $assetManager 资源管理器实例。注意此属性的类型在 getter 和 setter 中有所不同。
 * 查看 [[getAssetManager()]] 和 [[setAssetManager()]] 获取详情。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AssetController extends Controller
{
    /**
     * @var string 控制器默认动作 ID。
     */
    public $defaultAction = 'compress';
    /**
     * @var array 要压缩的资源包列表。
     */
    public $bundles = [];
    /**
     * @var array 表示输出压缩文件的资源包列表。
     * 你可以使用 'css' 和 'js' 键来指定输出压缩文件的名称：
     * 例如：
     *
     * ```php
     * 'app\config\AllAsset' => [
     *     'js' => 'js/all-{hash}.js',
     *     'css' => 'css/all-{hash}.css',
     *     'depends' => [ ... ],
     * ]
     * ```
     *
     * 文件名可以包含占位符 "{hash}"，他将由生成的文件的哈希填充。
     *
     * 为了压缩不通的资源组，你可以指定多个目标包。
     * 在这种情况下你应该使用 'depends' 键来指定，哪些包应该包含在特定的目标包中。
     * 对于单个包，你可以将 'depends' 保留为空，它将压缩所有剩下的包
     * 在这种情况下。
     * 例如：
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
     * @var string|callable JavaScript 文件压缩程序。
     * 如果是个字符串，它将被视为 shell 命令模版，其中应该包含
     * 占位符 {from} - 源文件名 - 和 {to} - 输出文件名。
     * 否则，他被视为应该执行压缩的PHP回调。
     *
     * 默认值依赖于 "Closure Compiler" 的用法
     * @see https://developers.google.com/closure/compiler/
     */
    public $jsCompressor = 'java -jar compiler.jar --js {from} --js_output_file {to}';
    /**
     * @var string|callable CSS 文件压缩程序。
     * 如果是个字符串，它将被视为 shell 命令模版, 其中应该包含
     * 占位符 {from} - 源文件名 - 和 {to} - 输出文件名。
     * 否则，他被视为应该执行压缩的 PHP 回调。
     *
     * 默认值依赖于 "YUI Compressor" 的用法
     * @see https://github.com/yui/yuicompressor/
     */
    public $cssCompressor = 'java -jar yuicompressor.jar --type css {from} -o {to}';
    /**
     * @var bool 压缩后是否删除资源源文件
     * 此选项仅影响那些设置了 [[\yii\web\AssetBundle::sourcePath]] 的包。
     * @since 2.0.10
     */
    public $deleteSource = false;

    /**
     * @var array|\yii\web\AssetManager [[\yii\web\AssetManager]] 实例或者他数组配置，将用于
     * 资源处理
     */
    private $_assetManager = [];


    /**
     * 返回资源管理器实例。
     * @throws \yii\console\Exception 在无效配置上。
     * @return \yii\web\AssetManager 资源管理器实例。
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
     * 设置资源管理器实例或配置。
     * @param \yii\web\AssetManager|array $assetManager 资源管理器实例或它的配置数组。
     * @throws \yii\console\Exception 在无效参数类型上。
     */
    public function setAssetManager($assetManager)
    {
        if (is_scalar($assetManager)) {
            throw new Exception('"' . get_class($this) . '::assetManager" should be either object or array - "' . gettype($assetManager) . '" given.');
        }
        $this->_assetManager = $assetManager;
    }

    /**
     * 根据给定的配置组合和压缩资源文件。
     * 在此过程中将创建新的资产包配置文件。
     * 为了使用压缩文件，您应该用这个文件替换原来的资源包配置。
     * @param string $configFile 配置文件名。
     * @param string $bundleFile 输出资源包配置文件名。
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
     * 将给定文件中的配置应用于自身实例。
     * @param string $configFile 配置文件名。
     * @throws \yii\console\Exception 失败。
     */
    protected function loadConfiguration($configFile)
    {
        $this->stdout("Loading configuration from '{$configFile}'...\n");
        $config = require $configFile;
        foreach ($config as $name => $value) {
            if (property_exists($this, $name) || $this->canSetProperty($name)) {
                $this->$name = $value;
            } else {
                throw new Exception("Unknown configuration option: $name");
            }
        }

        $this->getAssetManager(); // check if asset manager configuration is correct
    }

    /**
     * 创建源资源包的完整列表。
     * @param string[] $bundles 资源包名称列表。
     * @return \yii\web\AssetBundle[] 源资源包列表。
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
     * 递归加载资源包依赖项。
     * @param \yii\web\AssetBundle $bundle 包实例
     * @param array $result 已经记载包列表。
     * @throws Exception 失败。
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
     * 创建输出资源包的完整列表。
     * @param array $targets 输出资源包配置。
     * @param \yii\web\AssetBundle[] $bundles 源资源包列表。
     * @return \yii\web\AssetBundle[] 输出资源包列表。
     * @throws Exception 失败。
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
                }

                return $bundleOrders[$a] > $bundleOrders[$b] ? 1 : -1;
            });
            if (!isset($target['class'])) {
                $target['class'] = $name;
            }
            $targets[$name] = Yii::createObject($target);
        }

        return $targets;
    }

    /**
     * 构建输出资源包。
     * @param \yii\web\AssetBundle $target 输出资源包。
     * @param string $type 'js' 或 'css'。
     * @param \yii\web\AssetBundle[] $bundles 源资源包。
     * @throws Exception 失败。
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
     * 按照源包依赖输出的方式调整资源包之间的依赖关系。
     * @param \yii\web\AssetBundle[] $targets 输出资源包。
     * @param \yii\web\AssetBundle[] $bundles 源资源包。
     * @return \yii\web\AssetBundle[] 输出资源包。
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
     * 注册资源包包括其依赖项。
     * @param \yii\web\AssetBundle[] $bundles 资源包列表。
     * @param string $name 包名字。
     * @param array $registered 存储已注册的名字。
     * @throws Exception 如果检测到循环依赖项。
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
     * 保存新的资源包配置。
     * @param \yii\web\AssetBundle[] $targets 要保存的资源包列表。
     * @param string $bundleFile 输出文件名。
     * @throws \yii\console\Exception 失败。
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
        $version = date('Y-m-d H:i:s');
        $bundleFileContent = <<<EOD
<?php
/**
 * This file is generated by the "yii {$this->id}" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version {$version}
 */
return {$array};
EOD;
        if (!file_put_contents($bundleFile, $bundleFileContent, LOCK_EX)) {
            throw new Exception("Unable to write output bundle configuration at '{$bundleFile}'.");
        }
        $this->stdout("Output bundle configuration created at '{$bundleFile}'.\n", Console::FG_GREEN);
    }

    /**
     * 压缩给定的 JavaScript 文件并将他们合并到一个文件里面。
     * @param array $inputFiles 源文件名字列表。
     * @param string $outputFile 输出文件名。
     * @throws \yii\console\Exception 失败
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
     * 压缩给定的 CSS 文件并将他们合并到一个文件里面。
     * @param array $inputFiles 源文件名字列表。
     * @param string $outputFile 输出文件名。
     * @throws \yii\console\Exception 失败
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
     * 将 JavaScript 文件合并到一个里面。
     * @param array $inputFiles 源文件名字。
     * @param string $outputFile 输出文件名。
     * @throws \yii\console\Exception 失败。
     */
    public function combineJsFiles($inputFiles, $outputFile)
    {
        $content = '';
        foreach ($inputFiles as $file) {
            // Add a semicolon to source code if trailing semicolon missing.
            // Notice: It needs a new line before `;` to avoid affection of line comment. (// ...;)
            $fileContent = rtrim(file_get_contents($file));
            if (substr($fileContent, -1) !== ';') {
                $fileContent .= "\n;";
            }
            $content .= "/*** BEGIN FILE: $file ***/\n"
                . $fileContent . "\n"
                . "/*** END FILE: $file ***/\n";
        }
        if (!file_put_contents($outputFile, $content)) {
            throw new Exception("Unable to write output JavaScript file '{$outputFile}'.");
        }
    }

    /**
     * 将 CSS 文件合并到一个里面。
     * @param array $inputFiles 源文件名字。
     * @param string $outputFile 输出文件名。
     * @throws \yii\console\Exception 失败。
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
     * 调整 CSS 内容允许指向原始资源的 URL 引用。
     * @param string $cssContent 源 CSS 内容。
     * @param string $inputFilePath 输入 CSS 文件名。
     * @param string $outputFilePath 输出 CSS 文件名。
     * @return string 调整后的 CSS 内容。
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
        for ($i = 0; $i < $inputFilePathPartsCount && $i < $outputFilePathPartsCount; $i++) {
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

            if (strncmp($inputUrl, '/', 1) === 0 || strncmp($inputUrl, '#', 1) === 0 || preg_match('/^https?:\/\//i', $inputUrl) || preg_match('/^data:/i', $inputUrl)) {
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
     * 创建配置文件模板给 [[actionCompress]]。
     * @param string $configFile 输出文件名。
     * @return int CLI 退出码。
     * @throws \yii\console\Exception 失败。
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
                return ExitCode::OK;
            }
        }
        if (!file_put_contents($configFile, $template, LOCK_EX)) {
            throw new Exception("Unable to write template file '{$configFile}'.");
        }

        $this->stdout("Configuration file template created at '{$configFile}'.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * 返回规范化的绝对路径名。
     * 不同于常规的 `realpath()` 这个方法不会扩展符号链接也不会检查路径是否存在。
     * @param string $path 原始路径
     * @return string canonicalized 绝对路径名
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
     * @return bool 是否是外部的资源包
     */
    private function isBundleExternal($bundle)
    {
        return empty($bundle->sourcePath) && empty($bundle->basePath);
    }

    /**
     * @param AssetBundle $bundle 资源包实例。
     * @return array 包配置。
     */
    private function composeBundleConfig($bundle)
    {
        $config = Yii::getObjectVars($bundle);
        $config['class'] = get_class($bundle);
        return $config;
    }

    /**
     * 编写包循环依赖项的跟踪信息。
     * @param string $circularDependencyName 具有循环依赖项的包名称。
     * @param array $registered 检测循环依赖时注册的包列表。
     * @return string 绑定循环依赖项跟踪字符串。
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
     * 删除已经从 `sourcePath` 发布的资源包文件。
     * @param \yii\web\AssetBundle[] $bundles 要处理的资源包。
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
