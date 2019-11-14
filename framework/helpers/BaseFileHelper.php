<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * BaseFileHelper 为 [[FileHelper]] 提供了具体的实现方法。
 *
 * 不要使用 BaseFileHelper 类。使用 [[FileHelper]] 类来代替。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseFileHelper
{
    const PATTERN_NODIR = 1;
    const PATTERN_ENDSWITH = 4;
    const PATTERN_MUSTBEDIR = 8;
    const PATTERN_NEGATIVE = 16;
    const PATTERN_CASE_INSENSITIVE = 32;

    /**
     * @var string 包含 MIME 类型信息的 PHP 文件的路径（或别名）。
     */
    public static $mimeMagicFile = '@yii/helpers/mimeTypes.php';
    /**
     * @var string 包含 MIME 别名的 PHP 文件的路径（或别名）。
     * @since 2.0.14
     */
    public static $mimeAliasesFile = '@yii/helpers/mimeAliases.php';


    /**
     * 规范化文件/目录路径。
     *
     * 规范化做了以下工作：
     *
     * - 将所有目录分隔符转换为 `DIRECTORY_SEPARATOR`（e.g. "\a/b\c" becomes "/a/b/c"）
     * - 删除末尾的目录分隔符（e.g. "/a/b/c/" becomes "/a/b/c"）
     * - 将多个连续斜杠转换为单个斜杠（e.g. "/a///b/c" becomes "/a/b/c"）
     * - 移除 ".." 和 "." 基于它们的定义（e.g. "/a/./b/../c" becomes "/a/c"）
     *
     * @param string $path 要标准化的文件/目录路径
     * @param string $ds 要在规范化结果中使用的目录分隔符。默认是 `DIRECTORY_SEPARATOR`。
     * @return string 规范化文件/目录路径
     */
    public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = rtrim(strtr($path, '/\\', $ds . $ds), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }
        // the path may contain ".", ".." or double slashes, need to clean them up
        if (strpos($path, "{$ds}{$ds}") === 0 && $ds == '\\') {
            $parts = [$ds];
        } else {
            $parts = [];
        }
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || $part === '' && !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }
        $path = implode($ds, $parts);
        return $path === '' ? '.' : $path;
    }

    /**
     * 返回指定文件的本地化版本。
     *
     * 基于指定的语言代码进行搜索。
     * 特别是，将在子目录下查找同名的文件
     * 它的名字与语言代码一样。比如说，找到某个文件 "path/to/view.php"
     * 包含语言代码 "zh-CN"，本地化文件将在 "path/to/zh-CN/view.php" 这里被查找。
     * 如果这个文件没有被找到，它将尝试使用 "zh" 下的语言代码进行备用，
     * 例如 "path/to/zh/view.php"。如果找不到，将返回原始文件。
     *
     * 如果目标语言代码和源语言代码相同，
     * 原始文件将被返回。
     *
     * @param string $file 原始文件
     * @param string $language 文件应该本地化到的目标语言。
     * 如果没有去设置，将使用 [[\yii\base\Application::language]] 的值。
     * @param string $sourceLanguage 原始文件所包含的语言。
     * 如果没有去设置，将使用 [[\yii\base\Application::sourceLanguage]] 的值。
     * @return string 匹配的本地化文件，如果本地文件未找到可以使用原始文件。
     * 如果目标语言代码和源语言代码相同，将返回原始文件。
     */
    public static function localize($file, $language = null, $sourceLanguage = null)
    {
        if ($language === null) {
            $language = Yii::$app->language;
        }
        if ($sourceLanguage === null) {
            $sourceLanguage = Yii::$app->sourceLanguage;
        }
        if ($language === $sourceLanguage) {
            return $file;
        }
        $desiredFile = dirname($file) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . basename($file);
        if (is_file($desiredFile)) {
            return $desiredFile;
        }

        $language = substr($language, 0, 2);
        if ($language === $sourceLanguage) {
            return $file;
        }
        $desiredFile = dirname($file) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . basename($file);

        return is_file($desiredFile) ? $desiredFile : $file;
    }

    /**
     * 确定指定文件的 MIME 类型。
     * 此方法首先尝试基于 [finfo_open](http://php.net/manual/en/function.finfo-open.php) 确定 MIME 类型。
     * 如果 `fileinfo` 扩展未安装，
     * 当 `$checkExtension` 设置 true 的时候它将基于 [[getMimeTypeByExtension()]]。
     * @param string $file 文件名称。
     * @param string $magicFile 可选的魔术数据库文件名（或别名），通常类似 `/path/to/magic.mime`。
     * 这将作为第二个参数传递给 [finfo_open()](http://php.net/manual/en/function.finfo-open.php)
     * 当 `fileinfo` 扩展被安装时。如果 MIME 类型是基于 [[getMimeTypeByExtension()]]
     * 并且为 null，它将通过 [[mimeMagicFile]] 使用指定的文件。
     * @param bool $checkExtension 在 `finfo_open()` 无法确定 MIME 类型的情况下，
     * 是否使用文件扩展名来确定 MIME 类型。
     * @return string MIME 类型（e.g. `text/plain`）。如果无法确定MIME类型，则返回 Null。
     * @throws InvalidConfigException 当 `fileinfo` PHP 扩展没有被安装并且 `$checkExtension` 设置 `false`。
     */
    public static function getMimeType($file, $magicFile = null, $checkExtension = true)
    {
        if ($magicFile !== null) {
            $magicFile = Yii::getAlias($magicFile);
        }
        if (!extension_loaded('fileinfo')) {
            if ($checkExtension) {
                return static::getMimeTypeByExtension($file, $magicFile);
            }

            throw new InvalidConfigException('The fileinfo PHP extension is not installed.');
        }
        $info = finfo_open(FILEINFO_MIME_TYPE, $magicFile);

        if ($info) {
            $result = finfo_file($info, $file);
            finfo_close($info);

            if ($result !== false) {
                return $result;
            }
        }

        return $checkExtension ? static::getMimeTypeByExtension($file, $magicFile) : null;
    }

    /**
     * 根据指定文件的扩展名确定 MIME 类型。
     * 该方法将使用扩展名和 MIME 类型之间的本地映射。
     * @param string $file 文件的名字。
     * @param string $magicFile 包含所有可用 MIME 类型信息的文件的路径（或别名）。
     * 如果没有设置，将使用 [[mimeMagicFile]] 指定的文件。
     * @return string|null MIME 类型。如果无法确定 MIME 类型，则返回 Null。
     */
    public static function getMimeTypeByExtension($file, $magicFile = null)
    {
        $mimeTypes = static::loadMimeTypes($magicFile);

        if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
            $ext = strtolower($ext);
            if (isset($mimeTypes[$ext])) {
                return $mimeTypes[$ext];
            }
        }

        return null;
    }

    /**
     * 根据给定 MIME 类型确定扩展。
     * 该方法将使用扩展名和 MIME 类型之间的本地映射。
     * @param string $mimeType 文件的 MIME 类型。
     * @param string $magicFile 包含所有可用 MIME 类型信息的文件的路径（或别名）。
     * 如果没有设置，将使用 [[mimeMagicFile]] 指定的文件。
     * @return array 对应于指定 MIME 类型的扩展
     */
    public static function getExtensionsByMimeType($mimeType, $magicFile = null)
    {
        $aliases = static::loadMimeAliases(static::$mimeAliasesFile);
        if (isset($aliases[$mimeType])) {
            $mimeType = $aliases[$mimeType];
        }

        $mimeTypes = static::loadMimeTypes($magicFile);
        return array_keys($mimeTypes, mb_strtolower($mimeType, 'UTF-8'), true);
    }

    private static $_mimeTypes = [];

    /**
     * 从指定的文件加载 MIME 类型。
     * @param string $magicFile 包含所有可用 MIME 类型信息的文件的路径（或别名）。
     * 如果没有设置，将使用 [[mimeMagicFile]] 指定的文件。
     * @return array 从文件扩展名到 MIME 类型的映射
     */
    protected static function loadMimeTypes($magicFile)
    {
        if ($magicFile === null) {
            $magicFile = static::$mimeMagicFile;
        }
        $magicFile = Yii::getAlias($magicFile);
        if (!isset(self::$_mimeTypes[$magicFile])) {
            self::$_mimeTypes[$magicFile] = require $magicFile;
        }

        return self::$_mimeTypes[$magicFile];
    }

    private static $_mimeAliases = [];

    /**
     * 从指定文件加载 MIME 别名。
     * @param string $aliasesFile 包含 MIME 类型别名的文件的路径（或别名）。
     * 如果没有设置，将使用 [[mimeAliasesFile]] 指定的文件。
     * @return array 从文件扩展名到 MIME 类型的映射
     * @since 2.0.14
     */
    protected static function loadMimeAliases($aliasesFile)
    {
        if ($aliasesFile === null) {
            $aliasesFile = static::$mimeAliasesFile;
        }
        $aliasesFile = Yii::getAlias($aliasesFile);
        if (!isset(self::$_mimeAliases[$aliasesFile])) {
            self::$_mimeAliases[$aliasesFile] = require $aliasesFile;
        }

        return self::$_mimeAliases[$aliasesFile];
    }

    /**
     * 将整个目录复制为另一个目录。
     * 文件和子目录也将被复制。
     * @param string $src 源目录
     * @param string $dst 目标目录
     * @param array $options 目录复制选项。有效的选项是：
     *
     * - dirMode：整型，为新复制的目录设置的权限。默认为 0775。
     * - fileMode：整型，为新复制的文件设置的权限。默认设置为当前环境设置。
     * - filter：回调方法，为每个目录或文件调用的PHP回调。
     *   回调的签名应该是：`function ($path)`，`$path` 表示要过滤的完整路径。
     *   回调可以返回以下值：
     *
     *   * true：目录或文件将被复制（"only" 和 "except" 选项将被忽略）
     *   * false：目录或文件不会被复制（"only" 和 "except" 选项将被忽略）
     *   * null："only" 和 "except" 选项将决定是否复制目录或文件
     *
     * - only：数组，如果文件路径想要被复制，它们应该匹配的模式列表。
     *   如果路径末尾包含模式字符串，则路径与模式匹配。
     *   比如说，'.php' 匹配所有以 '.php' 结尾的文件路径。
     *   注意，'/' 模式中的字符在路径中同时匹配 '/' 和 '\'。
     *   如果文件路径与两者中的模式匹配 "only" 和 "except"，它不会被复制。
     * - except：数组，如果文件或目录希望不被复制，它们应该匹配的模式的列表。
     *   如果路径末尾包含模式字符串，则路径与模式匹配。
     *   模式以 '/' 仅适用于目录路径，以及不以 '/' 结尾的模式适用于文件路径。
     *   比如，'/a/b' 匹配以 '/a/b' 结尾的所有文件路径；
     *   以及 '.svn/' 匹配以 '.svn' 结尾的目录路径。
     *   注意，模式中的 '/' 字符匹配路径中的 '/' 和 '\' 匹配。
     * - caseSensitive：布尔类型，"only" 或 "except" 模式指定是否应该区分大小写。默认设置 true。
     * - recursive：布尔类型，子目录下的文件是否也应该被复制。默认值为 true。
     * - beforeCopy：回调类型，在复制每个子目录或文件之前调用的 PHP 回调。
     *   如果回调返回 false，子目录或文件的复制操作将被取消。
     *   回调的签名应该是：`function ($from, $to)`，`$from` 要复制的子目录或文件，
     *   而 `$to` 是复制目标。
     * - afterCopy：回调类型，成功复制每个子目录或文件后调用的 PHP 回调。
     *   回调的签名应该是：`function ($from, $to)`，`$from` 要复制的子目录或文件，
     *   而 `$to` 是复制目标。
     * - copyEmptyDirectories：布尔类型，是否复制空目录。设置为 false 以避免创建不包含文件的目录。
     *   这将影响最初不包含文件的目录和包含文件的目录
     *   以及目标目的地不包含文件的目录，因为文件是通过 `only` 和 `except` 筛选的。
     *   默认设置为 true。此选项从版本 2.0.12 开始可用。2.0.12 之前版本空目录依然可以复制。
     * @throws InvalidArgumentException 如果无法打开目录抛出异常
     */
    public static function copyDirectory($src, $dst, $options = [])
    {
        $src = static::normalizePath($src);
        $dst = static::normalizePath($dst);

        if ($src === $dst || strpos($dst, $src . DIRECTORY_SEPARATOR) === 0) {
            throw new InvalidArgumentException('Trying to copy a directory to itself or a subdirectory.');
        }
        $dstExists = is_dir($dst);
        if (!$dstExists && (!isset($options['copyEmptyDirectories']) || $options['copyEmptyDirectories'])) {
            static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
            $dstExists = true;
        }

        $handle = opendir($src);
        if ($handle === false) {
            throw new InvalidArgumentException("Unable to open directory: $src");
        }
        if (!isset($options['basePath'])) {
            // this should be done only once
            $options['basePath'] = realpath($src);
            $options = static::normalizeOptions($options);
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $file;
            $to = $dst . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($from, $options)) {
                if (isset($options['beforeCopy']) && !call_user_func($options['beforeCopy'], $from, $to)) {
                    continue;
                }
                if (is_file($from)) {
                    if (!$dstExists) {
                        // delay creation of destination directory until the first file is copied to avoid creating empty directories
                        static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
                        $dstExists = true;
                    }
                    copy($from, $to);
                    if (isset($options['fileMode'])) {
                        @chmod($to, $options['fileMode']);
                    }
                } else {
                    // recursive copy, defaults to true
                    if (!isset($options['recursive']) || $options['recursive']) {
                        static::copyDirectory($from, $to, $options);
                    }
                }
                if (isset($options['afterCopy'])) {
                    call_user_func($options['afterCopy'], $from, $to);
                }
            }
        }
        closedir($handle);
    }

    /**
     * 递归地删除一个目录（及其所有内容）。
     *
     * @param string $dir 递归删除的目录。
     * @param array $options 目录删除选项。有效的选项是：
     *
     * - traverseSymlinks：布尔型，是否遍历符号链接的目录。
     *   默认设置 `false`，这意味着符号链接目录的内容不会被删除。
     *   默认情况下只有符号链接会被删除。
     *
     * @throws ErrorException 失败时抛出的异常
     */
    public static function removeDirectory($dir, $options = [])
    {
        if (!is_dir($dir)) {
            return;
        }
        if (!empty($options['traverseSymlinks']) || !is_link($dir)) {
            if (!($handle = opendir($dir))) {
                return;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::removeDirectory($path, $options);
                } else {
                    static::unlink($path);
                }
            }
            closedir($handle);
        }
        if (is_link($dir)) {
            static::unlink($dir);
        } else {
            rmdir($dir);
        }
    }

    /**
     * 以跨平台的方式删除文件或符号链接
     *
     * @param string $path
     * @return bool
     *
     * @since 2.0.14
     */
    public static function unlink($path)
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if (!$isWindows) {
            return unlink($path);
        }

        if (is_link($path) && is_dir($path)) {
            return rmdir($path);
        }

        try {
            return unlink($path);
        } catch (ErrorException $e) {
            // last resort measure for Windows
            if (function_exists('exec') && file_exists($path)) {
                exec('DEL /F/Q ' . escapeshellarg($path));

                return !file_exists($path);
            }

            return false;
        }
    }

    /**
     * 返回在指定目录和子目录下找到的文件。
     * @param string $dir 将在其中查找文件的目录。
     * @param array $options 目录搜索选项。有效选项是：
     *
     * - `filter`：回调类型，为每个目录或文件调用的 PHP 回调。
     *   回调的签名应该是：`function ($path)`，`$path` 指的是要过滤的完整路径。
     *   回调可以返回以下值之一：
     *
     *   * `true`：将返回的文件或目录（`only` 和 `except` 选项将被忽略）
     *   * `false`：不会返回目录或文件（`only` 和 `except` 选项将被忽略）
     *   * `null`：`only` 和 `except` 选项将决定文件或目录是否被返回
     *
     * - `except`：数组，从结果匹配文件或目录路径中排除的模式列表。
     *   以斜杠 ('/') 结尾的模式仅适用于目录路径, 
     *   模式不以 '/' 结尾仅适用于文件路径。
     *   例如，'/a/b' 匹配所有以 '/a/b' 结尾的文件路径；以及 `.svn/` 匹配以 `.svn` 结尾的目录路径。
     *   如果模式不包含斜杠（`/`），则将其视为 shell glob 模式
     *   并检查相对于 `$dir` 的路径名的匹配。
     *   否则，该模式被视为适合由 `fnmatch(3)` 使用的 shell glob 
     *   使用 `FNM_PATHNAME` 标志：模式中的通配符与路径名中的 `/` 不匹配。
     *   例如，`views/*.php` 匹配 `views/index.php` 但不匹配 `views/controller/index.php`。
     *   前导斜杠与路径名的开头匹配。例如，`/*.php` 匹配 `index.php` 但不匹配 `views/start/index.php`。
     *   一个可选的前缀 `!` 它否定了模式；之前模式排除的任何匹配文件将再次包含在内。
     *   如果否定模式匹配，则将覆盖较低优先级模式源。在第一个 `!` 前放一个反斜杠 (`\`) 
     *   对于以文字 `!` 开头的模式，例如，`\!important!.txt`。
     *   注意，模式中的 '/' 字符与路径中的 '/' 和 '\' 匹配。
     * - `only`：数组，文件路径在返回时应匹配的模式列表。
     *   目录路径未经过检查。使用与 `except` 选项中相同的模式匹配规则。
     *   如果文件路径与 `only` 和 `except`，中的模式匹配，则不会返回。
     * - `caseSensitive`：布尔型，在 `only` 或 `except` 指定的模式下是否应区分大小写。默认为 `true`。
     * - `recursive`：布尔型，是否需要查找子目录下的文件。默认为 `true`。
     * @return array 在目录下找到的数组文件，没有特别的顺序。排序取决于使用的文件系统。
     * @throws InvalidArgumentException 如果目录无效则抛出异常。
     */
    public static function findFiles($dir, $options = [])
    {
        $dir = self::clearDir($dir);
        $options = self::setBasePath($dir, $options);
        $list = [];
        $handle = self::openDir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($path, $options)) {
                if (is_file($path)) {
                    $list[] = $path;
                } elseif (is_dir($path) && (!isset($options['recursive']) || $options['recursive'])) {
                    $list = array_merge($list, static::findFiles($path, $options));
                }
            }
        }
        closedir($handle);

        return $list;
    }

    /**
     * 返回在指定目录和子目录下找到的目录。
     * @param string $dir 将在其中查找文件的目录。
     * @param array $options 目录搜索选项。有效选项是：
     *
     * - `filter`：回调，为每个目录或文件调用的 PHP 回调。
     *   回调的签名应该是：`function ($path)`，`$path` 表示要过滤的完整路径。
     *   回调可以返回以下值之一：
     *
     *   * `true`：目录将被返回
     *   * `false`：该目录将不会被返回
     *
     * - `recursive`：布尔型，是否还应该查找子目录下的文件。默认 `true`。
     * @return array 目录下找到的目录，没有特别的顺序。排序取决于所使用的文件系统。
     * @throws InvalidArgumentException 如果目录无效抛出异常。
     * @since 2.0.14
     */
    public static function findDirectories($dir, $options = [])
    {
        $dir = self::clearDir($dir);
        $options = self::setBasePath($dir, $options);
        $list = [];
        $handle = self::openDir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path) && static::filterPath($path, $options)) {
                $list[] = $path;
                if (!isset($options['recursive']) || $options['recursive']) {
                    $list = array_merge($list, static::findDirectories($path, $options));
                }
            }
        }
        closedir($handle);

        return $list;
    }

    /**
     * @param string $dir
     */
    private static function setBasePath($dir, $options)
    {
        if (!isset($options['basePath'])) {
            // this should be done only once
            $options['basePath'] = realpath($dir);
            $options = static::normalizeOptions($options);
        }

        return $options;
    }

    /**
     * @param string $dir
     */
    private static function openDir($dir)
    {
        $handle = opendir($dir);
        if ($handle === false) {
            throw new InvalidArgumentException("Unable to open directory: $dir");
        }
        return $handle;
    }

    /**
     * @param string $dir
     */
    private static function clearDir($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("The dir argument must be a directory: $dir");
        }
        return rtrim($dir, DIRECTORY_SEPARATOR);
    }

    /**
     * 检查给定的文件路径是否满足过滤选项。
     * @param string $path 要检查的文件或目录的路径
     * @param array $options 过滤选项。
     * 有关支持的选项的解释请参考 [[findFiles()]]。
     * @return bool 文件或目录是否满足筛选选项。
     */
    public static function filterPath($path, $options)
    {
        if (isset($options['filter'])) {
            $result = call_user_func($options['filter'], $path);
            if (is_bool($result)) {
                return $result;
            }
        }

        if (empty($options['except']) && empty($options['only'])) {
            return true;
        }

        $path = str_replace('\\', '/', $path);

        if (!empty($options['except'])) {
            if (($except = self::lastExcludeMatchingFromList($options['basePath'], $path, $options['except'])) !== null) {
                return $except['flags'] & self::PATTERN_NEGATIVE;
            }
        }

        if (!empty($options['only']) && !is_dir($path)) {
            if (($except = self::lastExcludeMatchingFromList($options['basePath'], $path, $options['only'])) !== null) {
                // don't check PATTERN_NEGATIVE since those entries are not prefixed with !
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * 创建一个新目录。
     *
     * 这个方法类似于 PHP `mkdir()` 函数，
     * 只不过它使用 `chmod()` 来设置创建目录的权限，
     * 以避免 `umask` 设置的影响。
     *
     * @param string $path 要创建的目录的路径。
     * @param int $mode 为创建的目录设置的权限。
     * @param bool $recursive 如果父目录不存在是否需要创建它们。
     * @return bool whether 目录创建成功
     * @throws \yii\base\Exception 如果无法创建目录（例如 php 错误导致并行修改）
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {// https://github.com/yiisoft/yii2/issues/9288
                throw new \yii\base\Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \yii\base\Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 执行文件或目录名称的简单比较。
     *
     * 基于 git 1.8.5.3 源代码的 dir.c 中的 match_basename()。
     *
     * @param string $baseName 文件或目录名称与模式进行比较
     * @param string $pattern 将与 $baseName 进行比较的模式
     * @param int|bool $firstWildcard 在 $pattern 中第一个通配符的位置
     * @param int $flags 匹配标识
     * @return bool whether 名称与模式匹配
     */
    private static function matchBasename($baseName, $pattern, $firstWildcard, $flags)
    {
        if ($firstWildcard === false) {
            if ($pattern === $baseName) {
                return true;
            }
        } elseif ($flags & self::PATTERN_ENDSWITH) {
            /* "*literal" matching against "fooliteral" */
            $n = StringHelper::byteLength($pattern);
            if (StringHelper::byteSubstr($pattern, 1, $n) === StringHelper::byteSubstr($baseName, -$n, $n)) {
                return true;
            }
        }

        $matchOptions = [];
        if ($flags & self::PATTERN_CASE_INSENSITIVE) {
            $matchOptions['caseSensitive'] = false;
        }

        return StringHelper::matchWildcard($pattern, $baseName, $matchOptions);
    }

    /**
     * 将路径部分与带有可选通配符的模式进行比较。
     *
     * 基于 git 1.8.5.3 源代码的 dir.c 中的 match_basename()。
     *
     * @param string $path 比较的完整路径
     * @param string $basePath 基础路径将不进行比较
     * @param string $pattern 将针对一部分路径进行匹配模式比较
     * @param int|bool $firstWildcard 在 $pattern 第一个通配符的位置
     * @param int $flags 匹配标识
     * @return bool 是否针对部分路径进行模式匹配
     */
    private static function matchPathname($path, $basePath, $pattern, $firstWildcard, $flags)
    {
        // match with FNM_PATHNAME; the pattern has base implicitly in front of it.
        if (isset($pattern[0]) && $pattern[0] === '/') {
            $pattern = StringHelper::byteSubstr($pattern, 1, StringHelper::byteLength($pattern));
            if ($firstWildcard !== false && $firstWildcard !== 0) {
                $firstWildcard--;
            }
        }

        $namelen = StringHelper::byteLength($path) - (empty($basePath) ? 0 : StringHelper::byteLength($basePath) + 1);
        $name = StringHelper::byteSubstr($path, -$namelen, $namelen);

        if ($firstWildcard !== 0) {
            if ($firstWildcard === false) {
                $firstWildcard = StringHelper::byteLength($pattern);
            }
            // if the non-wildcard part is longer than the remaining pathname, surely it cannot match.
            if ($firstWildcard > $namelen) {
                return false;
            }

            if (strncmp($pattern, $name, $firstWildcard)) {
                return false;
            }
            $pattern = StringHelper::byteSubstr($pattern, $firstWildcard, StringHelper::byteLength($pattern));
            $name = StringHelper::byteSubstr($name, $firstWildcard, $namelen);

            // If the whole pattern did not have a wildcard, then our prefix match is all we need; we do not need to call fnmatch at all.
            if (empty($pattern) && empty($name)) {
                return true;
            }
        }

        $matchOptions = [
            'filePath' => true
        ];
        if ($flags & self::PATTERN_CASE_INSENSITIVE) {
            $matchOptions['caseSensitive'] = false;
        }

        return StringHelper::matchWildcard($pattern, $name, $matchOptions);
    }

    /**
     * 扫描非排除列表以便查看路径名是否被忽略。
     * 第一个匹配（例如 列表内的最后一个），如果有任何可能性，
     * 决定不同的结果。返回匹配的元素，
     * 或者返回 null。
     *
     * 基于 git 1.8.5.3 源代码的 dir.c 中的 last_exclude_matching_from_list()。
     *
     * @param string $basePath
     * @param string $path
     * @param array $excludes 列出要与 $path 对应的模式
     * @return array|null null 或者 $excludes 中的一个作为键数组排除项：'pattern'，'flags'
     * @throws InvalidArgumentException 如果任何排除模式不是带有键的字符串或者数组：模式，标志，firstWildcard。
     */
    private static function lastExcludeMatchingFromList($basePath, $path, $excludes)
    {
        foreach (array_reverse($excludes) as $exclude) {
            if (is_string($exclude)) {
                $exclude = self::parseExcludePattern($exclude, false);
            }
            if (!isset($exclude['pattern']) || !isset($exclude['flags']) || !isset($exclude['firstWildcard'])) {
                throw new InvalidArgumentException('If exclude/include pattern is an array it must contain the pattern, flags and firstWildcard keys.');
            }
            if ($exclude['flags'] & self::PATTERN_MUSTBEDIR && !is_dir($path)) {
                continue;
            }

            if ($exclude['flags'] & self::PATTERN_NODIR) {
                if (self::matchBasename(basename($path), $exclude['pattern'], $exclude['firstWildcard'], $exclude['flags'])) {
                    return $exclude;
                }
                continue;
            }

            if (self::matchPathname($path, $basePath, $exclude['pattern'], $exclude['firstWildcard'], $exclude['flags'])) {
                return $exclude;
            }
        }

        return null;
    }

    /**
     * 处理模式，剥离特殊字符，如 / 和 ! 从开头和设置标志代替。
     * @param string $pattern
     * @param bool $caseSensitive
     * @throws InvalidArgumentException
     * @return array 使用键：(string) 模式，(int) 标志，(int|bool) firstWildcard
     */
    private static function parseExcludePattern($pattern, $caseSensitive)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Exclude/include pattern must be a string.');
        }

        $result = [
            'pattern' => $pattern,
            'flags' => 0,
            'firstWildcard' => false,
        ];

        if (!$caseSensitive) {
            $result['flags'] |= self::PATTERN_CASE_INSENSITIVE;
        }

        if (!isset($pattern[0])) {
            return $result;
        }

        if ($pattern[0] === '!') {
            $result['flags'] |= self::PATTERN_NEGATIVE;
            $pattern = StringHelper::byteSubstr($pattern, 1, StringHelper::byteLength($pattern));
        }
        if (StringHelper::byteLength($pattern) && StringHelper::byteSubstr($pattern, -1, 1) === '/') {
            $pattern = StringHelper::byteSubstr($pattern, 0, -1);
            $result['flags'] |= self::PATTERN_MUSTBEDIR;
        }
        if (strpos($pattern, '/') === false) {
            $result['flags'] |= self::PATTERN_NODIR;
        }
        $result['firstWildcard'] = self::firstWildcardInPattern($pattern);
        if ($pattern[0] === '*' && self::firstWildcardInPattern(StringHelper::byteSubstr($pattern, 1, StringHelper::byteLength($pattern))) === false) {
            $result['flags'] |= self::PATTERN_ENDSWITH;
        }
        $result['pattern'] = $pattern;

        return $result;
    }

    /**
     * 搜索模式中的第一个通配符。
     * @param string $pattern 搜索的模式
     * @return int|bool 返回第一个通配符的位置，没找到则返回 false
     */
    private static function firstWildcardInPattern($pattern)
    {
        $wildcards = ['*', '?', '[', '\\'];
        $wildcardSearch = function ($r, $c) use ($pattern) {
            $p = strpos($pattern, $c);

            return $r === false ? $p : ($p === false ? $r : min($r, $p));
        };

        return array_reduce($wildcards, $wildcardSearch, false);
    }

    /**
     * @param array $options 原始选项
     * @return array 标准化选项
     * @since 2.0.12
     */
    protected static function normalizeOptions(array $options)
    {
        if (!array_key_exists('caseSensitive', $options)) {
            $options['caseSensitive'] = true;
        }
        if (isset($options['except'])) {
            foreach ($options['except'] as $key => $value) {
                if (is_string($value)) {
                    $options['except'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }
        if (isset($options['only'])) {
            foreach ($options['only'] as $key => $value) {
                if (is_string($value)) {
                    $options['only'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }

        return $options;
    }
}
