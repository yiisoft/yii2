<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * AssetManager 管理资源包的配置和加载。
 *
 * AssetManager 已经默认在 [[\yii\web\Application]] 里配置到了应用配置。
 * 你可以通过 `Yii::$app->assetManager` 访问该实例
 *
 * 你仍可以修改其配置，在应用配置的 `components` 里添加数组,
 * 就像这样：
 *
 * ```php
 * 'assetManager' => [
 *     'bundles' => [
 *         // 在这里重新配置资源包
 *     ],
 * ]
 * ```
 *
 * 关于 AssetManager 的更多使用参考，请查看 [前端资源](guide:structure-assets)。
 *
 * @property AssetConverterInterface $converter 资源编译器。请注意此属性的
 * getter 和 setter 上的不同。具体细节请查看 [[getConverter()]] 和 [[setConverter()]] 方法。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetManager extends Component
{
    /**
     * @var array|bool 资源包配置列表。提供此属性是为了自定义资源包。
     * 在 [[getBundle()]] 方法里，当一个资源包被加载，如果它在此处有相应的配置，
     * 这些配置将应用于这个资源包。
     *
     * 数组的键是资源包名称，通常是资源包类名，没有反斜杠的那种。
     * 数组的值是相应的配置，如果值为 false，则意味这it means the corresponding asset
     * 这个资源包被禁用，[[getBundle()]] 返回为 null。
     *
     * 如果此属性为 false，则意味全部的资源包都被禁用，
     * [[getBundle()]] 会全部返回 null。
     *
     * 以下示例显示如何禁用 Bootstrap 小部件去使用 bootstrap 的 CSS 。
     * （由于你想使用自己的样式）：
     *
     * ```php
     * [
     *     'yii\bootstrap\BootstrapAsset' => [
     *         'css' => [],
     *     ],
     * ]
     * ```
     */
    public $bundles = [];
    /**
     * @var string 保存已发布的资源文件的根目录。
     */
    public $basePath = '@webroot/assets';
    /**
     * @var string 已发布资源文件可以访问的基链接。
     */
    public $baseUrl = '@web/assets';
    /**
     * @var array 源资源文件（键）和目标资源文件（值）的映射。
     *
     * 此属性用于支持在某些资源包中修复不正确的资源文件路径。
     * 当资源包在视图中注册时，其 [[AssetBundle::css|css]] 和 [[AssetBundle::js|js]] 中的每个相对资源文件
     * 都会被这个映射检查。如果找到相应的键，
     * 将作为资源文件的最后部分（如果可用，以 [[AssetBundle::sourcePath]] 为前缀），
     * 相应的值将替换资源，并被注册到视图中。
     * 例如，资源文件 `my/path/to/jquery.js` 匹配了 `jquery.js`。
     *
     * 请注意，目标资源文件必须为绝对 URL 、相对于域名的 URL（以“/”开头）或者是
     * 相对于 [[baseUrl]] 和 [[basePath]] 的路径。
     *
     * 在以下示例中，任何以 `jquery.min.js` 结尾的资源都会被替换成 `jquery/dist/jquery.js`，
     * 其相对路径是 [[baseUrl]] 和 [[basePath]]。
     *
     * ```php
     * [
     *     'jquery.min.js' => 'jquery/dist/jquery.js',
     * ]
     * ```
     *
     * 你还可以用别名指定映射的值，例如：
     *
     * ```php
     * [
     *     'jquery.min.js' => '@web/js/jquery/jquery.js',
     * ]
     * ```
     */
    public $assetMap = [];
    /**
     * @var bool 是否使用符号链接发布资源文件。默认为 false，意味着
     * 资源文件件被复制到 [[basePath]]。使用符号链接有这样的好处：发布的资源永远和
     * 源文件一致，并且不需要复制操作。
     * 这在开发过程中特别有用。
     *
     * 但是，使用符号链接对主机环境有特殊要求。
     * 特别是，在 Linux/Unix，和 Windows Vista/2008 或更高版本上才支持符号链接。
     *
     * 此外，需要正确配置某些 Web 服务器，以便可以访问链接过的资源能被 Web 用户访问。
     * 例如，对于 Apache Web 服务器，应添加以下的配置指令到 Web 文件夹：
     *
     *
     * ```apache
     * Options FollowSymLinks
     * ```
     */
    public $linkAssets = false;
    /**
     * @var int 新发布的资源文件的权限。
     * 此值将被 PHP 函数 chmod() 所使用。不设掩码（umask）。
     * 如果未设置，权限将由当前环境确定。
     */
    public $fileMode;
    /**
     * @var int 新创建的资源目录的权限。
     * 此值将被 PHP 函数 chmod() 所使用。不设掩码（umask）。
     * 默认值为 0775，意味着目录可以被拥有者和拥有组别读写，
     * 但是其他用户只读。
     */
    public $dirMode = 0775;
    /**
     * @var callback PHP 回调：在复制每个子目录或文件之前调用。
     * 此选项仅在发布目录时使用。如果回调返回 false，
     * 则复制子目录或文件的操作将被取消。
     *
     * 回调的形式：`function ($from, $to)`，其中 `$from` 是子目录或者
     * 要复制的文件，而 `$to` 是复制目标。
     *
     * 这个回调作为参数 `beforeCopy` 传递给 [[\yii\helpers\FileHelper::copyDirectory()]]。
     */
    public $beforeCopy;
    /**
     * @var callback PHP 回调：在复制每个子目录或文件成功之后调用。
     * 此选项仅在发布目录时使用。回调的形式和 [[beforeCopy]] 一样。
     *
     * 这个回调作为参数 `afterCopy` 传递给 [[\yii\helpers\FileHelper::copyDirectory()]]。
     */
    public $afterCopy;
    /**
     * @var bool 当目标目录已存在，正发布的目录是否应发布。
     * 此选项仅在发布目录时使用。
     * 你可能希望在开发阶段将其设置为 `true` 以确保已发布目录始终是最新的。
     * 不要在生产服务器设置此属性，
     * 它会显着降低性能。
     */
    public $forceCopy = false;
    /**
     * @var bool 是否将时间戳附加到每个已发布资源的 URL 上。
     * 如果为 true，已发布资源的 URL 就会像 `/path/to/asset?v=timestamp`，
     * 其中 `timestamp` 是已发布文件的最后修改时间。
     * 通常情况下，你为资源启用 HTTP 缓存时，可将此属性设置为 true，
     * 因为它会在你更新资源文件时刷新缓存。
     * @since 2.0.3
     */
    public $appendTimestamp = false;
    /**
     * @var callable PHP 回调：该回调函数将被调用以生成资源目录的哈希值。
     * 回调的形式如下：
     *
     * ```
     * function ($path)
     * ```
     *
     * 其中 `$path` 资源路径。请注意，`$path` 可以是资源目录，也可以是单个文件。
     * 对于在 `url()` 中使用的相对路径的 CSS 文件，
     * 哈希实现应该使用文件的目录路径而不是复制中的资源文件的相对路径。
     *
     *
     * 如果未设置，资源管理器将在 `hash` 方法中使用 CRC32 和 filemtime。
     *
     *
     * 用 MD4 哈希的一个实现例子：
     *
     * ```php
     * function ($path) {
     *     return hash('md4', $path);
     * }
     * ```
     *
     * @since 2.0.6
     */
    public $hashCallback;

    private $_dummyBundles = [];


    /**
     * 初始化组件
     * @throws InvalidConfigException 如果 [[basePath]] 无效
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);
        if (!is_dir($this->basePath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        } elseif (!is_writable($this->basePath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
        }

        $this->basePath = realpath($this->basePath);
        $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
    }

    /**
     * 返回所找的资源包对象。
     *
     * 这个方法首先会在 [[bundles]] 你查找。如找不到
     * 它会将 `$name` 当作资源包的类，并创建一个新实例。
     *
     * @param string $name 资源包的类名称（没有反斜杠前缀）
     * @param bool $publish 是否在返回资源包之前发布资源包中的资源文件。
     * 如果将此设置为 false，则必须手动调用 `AssetBundle::publish()` 来发布资源文件。
     * @return AssetBundle 资源包对象实例
     * @throws InvalidConfigException 如果 $name 没有指向任何合法资源包
     */
    public function getBundle($name, $publish = true)
    {
        if ($this->bundles === false) {
            return $this->loadDummyBundle($name);
        } elseif (!isset($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, [], $publish);
        } elseif ($this->bundles[$name] instanceof AssetBundle) {
            return $this->bundles[$name];
        } elseif (is_array($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, $this->bundles[$name], $publish);
        } elseif ($this->bundles[$name] === false) {
            return $this->loadDummyBundle($name);
        }

        throw new InvalidConfigException("Invalid asset bundle configuration: $name");
    }

    /**
     * 根据名称加载资源包。
     *
     * @param string $name 资源包名称
     * @param array $config 资源包对象的配置
     * @param bool $publish 是否发布资源包
     * @return AssetBundle
     * @throws InvalidConfigException 如果配置无效
     */
    protected function loadBundle($name, $config = [], $publish = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = $name;
        }
        /* @var $bundle AssetBundle */
        $bundle = Yii::createObject($config);
        if ($publish) {
            $bundle->publish($this);
        }

        return $bundle;
    }

    /**
     * 按名称加载虚拟资源包。
     *
     * @param string $name
     * @return AssetBundle
     */
    protected function loadDummyBundle($name)
    {
        if (!isset($this->_dummyBundles[$name])) {
            $this->_dummyBundles[$name] = $this->loadBundle($name, [
                'sourcePath' => null,
                'js' => [],
                'css' => [],
                'depends' => [],
            ]);
        }

        return $this->_dummyBundles[$name];
    }

    /**
     * 返回给定资源的实际 URL。
     * 实际的 URL 是通过将指定的资源路径，拼接上 [[AssetBundle::$baseUrl]] 或者 [[AssetManager::$baseUrl]] 前缀获得的。
     * @param AssetBundle $bundle 资源文件所属的资源包
     * @param string $asset 资源路径。必须是 [[AssetBundle::$js]] 或者 [[AssetBundle::$css]] 列表里的资源文件。
     * @return string 给定资源的实际 URL。
     */
    public function getAssetUrl($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            if (strncmp($actualAsset, '@web/', 5) === 0) {
                $asset = substr($actualAsset, 5);
                $basePath = Yii::getAlias('@webroot');
                $baseUrl = Yii::getAlias('@web');
            } else {
                $asset = Yii::getAlias($actualAsset);
                $basePath = $this->basePath;
                $baseUrl = $this->baseUrl;
            }
        } else {
            $basePath = $bundle->basePath;
            $baseUrl = $bundle->baseUrl;
        }

        if (!Url::isRelative($asset) || strncmp($asset, '/', 1) === 0) {
            return $asset;
        }

        if ($this->appendTimestamp && ($timestamp = @filemtime("$basePath/$asset")) > 0) {
            return "$baseUrl/$asset?v=$timestamp";
        }

        return "$baseUrl/$asset";
    }

    /**
     * 返回给定资源的实际文件路径。
     * @param AssetBundle $bundle 资源文件所属的资源包
     * @param string $asset 资源路径。必须是 [[AssetBundle::$js]] 或者 [[AssetBundle::$css]] 列表里的资源文件。
     * @return string|false 实际的文件路径，如果所给资源是是一个绝对 URL，则返回  `false`
     */
    public function getAssetPath($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            return Url::isRelative($actualAsset) ? $this->basePath . '/' . $actualAsset : false;
        }

        return Url::isRelative($asset) ? $bundle->basePath . '/' . $asset : false;
    }

    /**
     * @param AssetBundle $bundle
     * @param string $asset
     * @return string|bool
     */
    protected function resolveAsset($bundle, $asset)
    {
        if (isset($this->assetMap[$asset])) {
            return $this->assetMap[$asset];
        }
        if ($bundle->sourcePath !== null && Url::isRelative($asset)) {
            $asset = $bundle->sourcePath . '/' . $asset;
        }

        $n = mb_strlen($asset, Yii::$app->charset);
        foreach ($this->assetMap as $from => $to) {
            $n2 = mb_strlen($from, Yii::$app->charset);
            if ($n2 <= $n && substr_compare($asset, $from, $n - $n2, $n2) === 0) {
                return $to;
            }
        }

        return false;
    }

    private $_converter;

    /**
     * 返回资源编译器。
     * @return AssetConverterInterface 资源编译器。
     */
    public function getConverter()
    {
        if ($this->_converter === null) {
            $this->_converter = Yii::createObject(AssetConverter::className());
        } elseif (is_array($this->_converter) || is_string($this->_converter)) {
            if (is_array($this->_converter) && !isset($this->_converter['class'])) {
                $this->_converter['class'] = AssetConverter::className();
            }
            $this->_converter = Yii::createObject($this->_converter);
        }

        return $this->_converter;
    }

    /**
     * 设置资源编译器。
     * @param array|AssetConverterInterface $value 资源编译器。可以是个
     * 实现了 [[AssetConverterInterface]] 的对象，也可以是
     * 用来创建编译器对象的数组配置。
     */
    public function setConverter($value)
    {
        $this->_converter = $value;
    }

    /**
     * @var array 已发布的资源
     */
    private $_published = [];

    /**
     * 发布文件或目录。
     *
     * 此方法将指定的文件或目录复制到 [[basePath]]， so that
     * 以便可以通过Web服务器访问它们。
     *
     * 资源文件将检查其修改时间以避免不必要的文件复制。
     *
     *
     * 资源文件目录则会，其下的所有文件和子目录将以递归方式发布。
     * 注意，如果 $forceCopy 为 false，则该方法仅检查目标文件夹是否存在，
     * 以避免重复复制（这是非常昂贵的）。
     *
     * 默认情况下，以 "." 开头的目录，子目录和文件都不会被发布。
     * 如果要更改此行为，可以设置 "beforeCopy" 选项，
     * 如 `$options` 参数中所述。
     *
     * Note: 在极端场景下，可能会形成竞争条件，导致在创建已发布的资源文件的目录时，
     * 产生非关键问题的一次性表现。（就是并发请求同时触发布的问题）
     * 但可以完全避免这个问题，
     * 先发一个 “请求”，以触发所有会调用 'publish()' 的资源的发布，
     * 在应用程序部署阶段，在系统上线之前就先这么做。
     * 关于此问题更多的讨论请查看: http://code.google.com/p/yii/issues/detail?id=2579
     *
     * @param string $path 要发布的资源文件或目录
     * @param array $options 发布目录时要应用的选项。
     * 支持以下选项：
     *
     * - only: array，允许被复制的文件路径的匹配模式列表。
     * - except: array，不允许被复制的文件路径的匹配模式列表。
     * - caseSensitive: boolean，指定为 “only” 或 “except” 的匹配模式是否区分大小写。默认为 true。
     * - beforeCopy: callback, 一个在复制每个子目录或文件之前调用的 PHP 回调。
     *   如果设置了，则覆盖 [[beforeCopy]] 属性。
     * - afterCopy: callback, 在成功复制子目录或文件后调用的 PHP 回调。
     *   如果设置了，则覆盖 [[afterCopy]] 属性。
     * - forceCopy: boolean, 如果目标目录要发布的文件已存在，是否要强制复制。
     *   此选项仅在发布目录时使用。
     *   如果设置了，则覆盖 [[forceCopy]] 属性。
     *
     * @return array 已发布的目录或者文件的路径和 URL 地址。
     * @throws InvalidArgumentException 如果要发布的资源不存在。
     */
    public function publish($path, $options = [])
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path];
        }

        if (!is_string($path) || ($src = realpath($path)) === false) {
            throw new InvalidArgumentException("The file or directory to be published does not exist: $path");
        }

        if (is_file($src)) {
            return $this->_published[$path] = $this->publishFile($src);
        }

        return $this->_published[$path] = $this->publishDirectory($src, $options);
    }

    /**
     * 发布文件。
     * @param string $src 将要发布的资源文件
     * @return string[] 已发布好的资源文件路径和 URL。
     * @throws InvalidArgumentException 如果要发布的资源不存在。
     */
    protected function publishFile($src)
    {
        $dir = $this->hash($src);
        $fileName = basename($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($dstDir)) {
            FileHelper::createDirectory($dstDir, $this->dirMode, true);
        }

        if ($this->linkAssets) {
            if (!is_file($dstFile)) {
                try { // fix #6226 symlinking multi threaded
                    symlink($src, $dstFile);
                } catch (\Exception $e) {
                    if (!is_file($dstFile)) {
                        throw $e;
                    }
                }
            }
        } elseif (@filemtime($dstFile) < @filemtime($src)) {
            copy($src, $dstFile);
            if ($this->fileMode !== null) {
                @chmod($dstFile, $this->fileMode);
            }
        }

        return [$dstFile, $this->baseUrl . "/$dir/$fileName"];
    }

    /**
     * 发布目录。
     * @param string $src 将要发布的目录
     * @param array $options 发布目录时要应用的选项。
     * 支持以下选项：
     *
     * - only: array，允许被复制的文件路径的匹配模式列表。
     * - except: array，不允许被复制的文件路径的匹配模式列表。
     * - caseSensitive: boolean，指定为 “only” 或 “except” 的匹配模式是否区分大小写。默认为 true。
     * - beforeCopy: callback, 一个在复制每个子目录或文件之前调用的 PHP 回调。
     *   如果设置了，则覆盖 [[beforeCopy]] 属性。
     * - afterCopy: callback, 在成功复制子目录或文件后调用的 PHP 回调。
     *   如果设置了，则覆盖 [[afterCopy]] 属性。
     * - forceCopy: boolean, 如果目标目录要发布的文件已存在，是否要强制复制。
     *   此选项仅在发布目录时使用。
     *   如果设置了，则覆盖 [[forceCopy]] 属性。
     *
     * @return string[] 已发布的目录的路径和 URL 地址。
     * @throws InvalidArgumentException 如果要发布的资源不存在。
     */
    protected function publishDirectory($src, $options)
    {
        $dir = $this->hash($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        if ($this->linkAssets) {
            if (!is_dir($dstDir)) {
                FileHelper::createDirectory(dirname($dstDir), $this->dirMode, true);
                try { // fix #6226 symlinking multi threaded
                    symlink($src, $dstDir);
                } catch (\Exception $e) {
                    if (!is_dir($dstDir)) {
                        throw $e;
                    }
                }
            }
        } elseif (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !is_dir($dstDir)) {
            $opts = array_merge(
                $options,
                [
                    'dirMode' => $this->dirMode,
                    'fileMode' => $this->fileMode,
                    'copyEmptyDirectories' => false,
                ]
            );
            if (!isset($opts['beforeCopy'])) {
                if ($this->beforeCopy !== null) {
                    $opts['beforeCopy'] = $this->beforeCopy;
                } else {
                    $opts['beforeCopy'] = function ($from, $to) {
                        return strncmp(basename($from), '.', 1) !== 0;
                    };
                }
            }
            if (!isset($opts['afterCopy']) && $this->afterCopy !== null) {
                $opts['afterCopy'] = $this->afterCopy;
            }
            FileHelper::copyDirectory($src, $dstDir, $opts);
        }

        return [$dstDir, $this->baseUrl . '/' . $dir];
    }

    /**
     * 返回文件的发布后的路径。
     * 这个方法没有执行任何发布动作，
     * 它仅仅告诉你这个文件或目录是否发布了，以及它发布到了哪里。
     * @param string $path 要发布的资源文件或目录
     * @return string|false string：已发布的路径。False：如果文件或者目录不存在。
     */
    public function getPublishedPath($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][0];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->basePath . DIRECTORY_SEPARATOR . $this->hash($path) . (is_file($path) ? DIRECTORY_SEPARATOR . basename($path) : '');
        }

        return false;
    }

    /**
     * 返回文件的发布后的 URL 地址。
     * 这个方法没有执行任何发布动作，
     * 它仅仅告诉你这个文件或目录是否发布了，以及它发布到了哪里。
     * @param string $path 要发布的资源文件或目录
     * @return string|false string：已发布的 URL。False：如果文件或者目录不存在。
     */
    public function getPublishedUrl($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][1];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->baseUrl . '/' . $this->hash($path) . (is_file($path) ? '/' . basename($path) : '');
        }

        return false;
    }

    /**
     * 给目录生成 CRC32 哈希值。
     * 冲突会高于 MD5，但生成的哈希字符串要小得多。
     * @param string $path 将要被哈希的字符串。
     * @return string 哈希字符串
     */
    protected function hash($path)
    {
        if (is_callable($this->hashCallback)) {
            return call_user_func($this->hashCallback, $path);
        }
        $path = (is_file($path) ? dirname($path) : $path) . filemtime($path);
        return sprintf('%x', crc32($path . Yii::getVersion() . '|' . $this->linkAssets));
    }
}
