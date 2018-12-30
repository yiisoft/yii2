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
     * @var string 存储缓存文件的目录。这里可以使用 [path alias](guide:concept-aliases) 。
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
     * Initializes this component by ensuring the existence of the cache path.
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
     * Checks whether a specified key exists in the cache.
     * This can be faster than getting the value from the cache if the data is big.
     * Note that this method does not check whether the dependency associated
     * with the cached data, if there is any, has changed. So a call to [[get]]
     * may return false while exists returns true.
     * @param mixed $key a key identifying the cached value. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @return bool true if a value exists in cache, false if the value is not in the cache or expired.
     */
    public function exists($key)
    {
        $cacheFile = $this->getCacheFile($this->buildKey($key));

        return @filemtime($cacheFile) > time();
    }

    /**
     * Retrieves a value from cache with a specified key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key a unique key identifying the cached value
     * @return string|false the value stored in cache, false if the value is not in the cache or expired.
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
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached. Other types (If you have disabled [[serializer]]) unable to get is
     * correct in [[getValue()]].
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
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
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached. Other types (if you have disabled [[serializer]]) unable to get is
     * correct in [[getValue()]].
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
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
     * Deletes a value with the specified key from cache
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key of the value to be deleted
     * @return bool if no error happens during deletion
     */
    protected function deleteValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        return @unlink($cacheFile);
    }

    /**
     * Returns the cache file path given the cache key.
     * @param string $key cache key
     * @return string the cache file path
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
     * Deletes all values from cache.
     * This is the implementation of the method declared in the parent class.
     * @return bool whether the flush operation was successful.
     */
    protected function flushValues()
    {
        $this->gc(true, false);

        return true;
    }

    /**
     * Removes expired cache files.
     * @param bool $force whether to enforce the garbage collection regardless of [[gcProbability]].
     * Defaults to false, meaning the actual deletion happens with the probability as specified by [[gcProbability]].
     * @param bool $expiredOnly whether to removed expired cache files only.
     * If false, all cache files under [[cachePath]] will be removed.
     */
    public function gc($force = false, $expiredOnly = true)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->gcRecursive($this->cachePath, $expiredOnly);
        }
    }

    /**
     * Recursively removing expired cache files under a directory.
     * This method is mainly used by [[gc()]].
     * @param string $path the directory under which expired cache files are removed.
     * @param bool $expiredOnly whether to only remove expired cache files. If false, all files
     * under `$path` will be removed.
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
