<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\helpers\FileHelper;

/**
 * FileCache 是使用文件系统实现的缓存组件。
 *
 * 对于每个要被缓存的数据值，FileCache 都用单独的文件存储。 
 * 这个缓存文件放在 [[cachePath]] 目录下。FileCache 也会自动执行垃圾回收，
 * 删除过期的缓存文件。
 *
 * 可以参考 [[Cache]] 查看 FileCache 支持的通用的缓存操作方法。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileCache extends Cache
{
    /**
     * @var string 一个缓存键的前缀字符串。当你想在同一个目录 [[cachePath]] 下，
     * 给不同的应用存储缓存数据时，如果要避免冲突，
     * 就会用到它。
     *
     * 为了确保互用性，前缀应该只包含数字和字母。
     */
    public $keyPrefix = '';
    /**
     * @var string 存储缓存文件的目录。这里可以使用 [path alias](guide:concept-aliases)。
     * 如果没有设置，默认就是应用 runtime 目录的 "cache" 子目录。
     */
    public $cachePath = '@runtime/cache';
    /**
     * @var string 缓存文件后缀。默认是 '.bin'。
     */
    public $cacheFileSuffix = '.bin';
    /**
     * @var int 存储缓存文件的子目录层级。默认是 1。
     * 如果你的系统有大量的缓存文件（比如一百万），你可以设置一个较大的值，
     * （通常不大于 3 ）。使用子目录的目的，
     * 主要确保文件系统不要把太多的文件都单独放在一个目录里。
     */
    public $directoryLevel = 1;
    /**
     * @var int 当把一片数据存入缓存时，
     * 触发垃圾回收的可能性（百万分之一）。默认是 10，意味着 0.001% 的机会。
     * 这个数字的范围应该是 0 到 1000000。0 表示关闭垃圾回收机制。
     */
    public $gcProbability = 10;
    /**
     * @var int 新生成的缓存文件的权限。
     * 这个值将作为 PHP chmod() 函数的参数使用。不使用 umask。
     * 如果不设置，那么文件的权限由当前的环境决定。
     */
    public $fileMode;
    /**
     * @var int 新生成的缓存目录的权限。
     * 这个值将作为 PHP chmod() 函数的参数使用。不使用 umask。
     * 默认是 0775，也就是说可以被拥有者和所属组读写，
     * 但其它用户只读。
     */
    public $dirMode = 0775;


    /**
     * 初始化组件，确保缓存目录的存在。
     */
    public function init()
    {
        parent::init();
        $this->cachePath = Yii::getAlias($this->cachePath);
        if (!is_dir($this->cachePath)) {
            FileHelper::createDirectory($this->cachePath, $this->dirMode, true);
        }
    }

    /**
     * 检测指定的键是否存在缓存中。
     * 如果缓存数据量大的话，这比从缓存中直接获取值稍快些。
     * 注意，如果缓存数据有缓存依赖，
     * 该方法不会检测缓存依赖是否发生变化。所以有可能调用 [[get]] 方法返回 false，
     * 而调用该方法返回 true。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果缓存值存在返回 true，如果缓存值不存在或者已经过期则返回 false。
     */
    public function exists($key)
    {
        $cacheFile = $this->getCacheFile($this->buildKey($key));

        return @filemtime($cacheFile) > time();
    }

    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return string|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $cacheValue;
            }
        }

        return false;
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param string $value 要缓存的值。如果是其它的数据类型（如果禁用了 [[serializer]] 方法），
     * 那么在后续 [[getValue()]] 方法中不能正确地获取到值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        $this->gc();
        $cacheFile = $this->getCacheFile($key);
        if ($this->directoryLevel > 0) {
            @FileHelper::createDirectory(dirname($cacheFile), $this->dirMode, true);
        }
        // If ownership differs the touch call will fail, so we try to
        // rebuild the file from scratch by deleting it first
        // https://github.com/yiisoft/yii2/pull/16120
        if (is_file($cacheFile) && function_exists('posix_geteuid') && fileowner($cacheFile) !== posix_geteuid()) {
            @unlink($cacheFile);
        }
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($cacheFile, $this->fileMode);
            }
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }

            return @touch($cacheFile, $duration + time());
        }

        $error = error_get_last();
        Yii::warning("Unable to write cache file '{$cacheFile}': {$error['message']}", __METHOD__);
        return false;
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param string $value 要缓存的值。如果是其它的数据类型（如果禁用了 [[serializer]] 方法），
     * 那么在后续 [[getValue()]] 方法中不能正确地获取到值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        $cacheFile = $this->getCacheFile($key);
        if (@filemtime($cacheFile) > time()) {
            return false;
        }

        return $this->setValue($key, $value, $duration);
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        return @unlink($cacheFile);
    }

    /**
     * 根据缓存键返回缓存文件。
     * @param string $key 缓存键。
     * @return string 缓存文件的路径。
     */
    protected function getCacheFile($key)
    {
        if ($this->directoryLevel > 0) {
            $base = $this->cachePath;
            for ($i = 0; $i < $this->directoryLevel; ++$i) {
                if (($prefix = substr($key, $i + $i, 2)) !== false) {
                    $base .= DIRECTORY_SEPARATOR . $prefix;
                }
            }

            return $base . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
        }

        return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        $this->gc(true, false);

        return true;
    }

    /**
     * 删除过期的缓存文件。
     * @param bool $force 是否强制执行垃圾回收，不论 [[gcProbability]] 概率。
     * 默认是 false，意味着是否发生垃圾回收还得参考由 [[gcProbability]] 指明的可能性概率。
     * @param bool $expiredOnly 是否只删除过期的缓存文件。
     * 如果是 false，所有 [[cachePath]] 下的缓存文件都将被删除。
     */
    public function gc($force = false, $expiredOnly = true)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->gcRecursive($this->cachePath, $expiredOnly);
        }
    }

    /**
     * 递归地在指定目录下删除过期的缓存文件。
     * 该方法主要在 [[gc()]] 中调用。
     * @param string $path 该目录下所有过期的缓存文件都会被删除。
     * @param bool $expiredOnly 是否只删除过期的缓存文件。
     * 如果是 false，所有 `$path` 下的缓存文件都将被删除。
     */
    protected function gcRecursive($path, $expiredOnly)
    {
        if (($handle = opendir($path)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] === '.') {
                    continue;
                }
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->gcRecursive($fullPath, $expiredOnly);
                    if (!$expiredOnly) {
                        if (!@rmdir($fullPath)) {
                            $error = error_get_last();
                            Yii::warning("Unable to remove directory '{$fullPath}': {$error['message']}", __METHOD__);
                        }
                    }
                } elseif (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time()) {
                    if (!@unlink($fullPath)) {
                        $error = error_get_last();
                        Yii::warning("Unable to remove file '{$fullPath}': {$error['message']}", __METHOD__);
                    }
                }
            }
            closedir($handle);
        }
    }
}
