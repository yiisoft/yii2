<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\caching\ApcCache;
use yii\caching\CacheInterface;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * 允许你刷新缓存。
 *
 * 查看要刷新的可用组件列表：
 *
 *     yii cache
 *
 * 刷新由其名称指定的特定组件：
 *
 *     yii cache/flush first second third
 *
 * 刷新可在系统中找到的所有缓存组件
 *
 *     yii cache/flush-all
 *
 * 请注意该命令使用控制台应用程序配置文件中定义的缓存组件。如果配置的组件
 * 与 web 应用程序不同，web 应用程序缓存不会被清除。
 * 为了解决它请在控制台配置中复制 web 应用程序缓存组件。你可以使用任何组件名称。
 *
 * PHP 进程之间不共享 APC，因此从命令行刷新缓存对 web 没有影响。
 * 刷新Web缓存可以通过以下方式完成：
 *
 * - 将一个 php 文件放在 web 根目录下并通过 HTTP 调用它
 * - 使用 [Cachetool](http://gordalina.github.io/cachetool/)
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class CacheController extends Controller
{
    /**
     * 列出可以刷新的缓存。
     */
    public function actionIndex()
    {
        $caches = $this->findCaches();

        if (!empty($caches)) {
            $this->notifyCachesCanBeFlushed($caches);
        } else {
            $this->notifyNoCachesFound();
        }
    }

    /**
     * 刷新给定的缓存组件。
     *
     * 例如，
     *
     * ```
     * # 刷新其id指定的缓存："first"，"second"，"third"
     * yii cache/flush first second third
     * ```
     */
    public function actionFlush()
    {
        $cachesInput = func_get_args();

        if (empty($cachesInput)) {
            throw new Exception('You should specify cache components names');
        }

        $caches = $this->findCaches($cachesInput);
        $cachesInfo = [];

        $foundCaches = array_keys($caches);
        $notFoundCaches = array_diff($cachesInput, array_keys($caches));

        if ($notFoundCaches) {
            $this->notifyNotFoundCaches($notFoundCaches);
        }

        if (!$foundCaches) {
            $this->notifyNoCachesFound();
            return ExitCode::OK;
        }

        if (!$this->confirmFlush($foundCaches)) {
            return ExitCode::OK;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' => $this->canBeFlushed($class) ? Yii::$app->get($name)->flush() : false,
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    /**
     * 刷新系统中注册的所有缓存。
     */
    public function actionFlushAll()
    {
        $caches = $this->findCaches();
        $cachesInfo = [];

        if (empty($caches)) {
            $this->notifyNoCachesFound();
            return ExitCode::OK;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' => $this->canBeFlushed($class) ? Yii::$app->get($name)->flush() : false,
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    /**
     * 清除给定连接组件的数据库架构缓存。
     *
     * ```
     * # 清除指定的缓存模式通过组件 id："db"
     * yii cache/flush-schema db
     * ```
     *
     * @param string $db id 连接组件
     * @return int 退出码
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     *
     * @since 2.0.1
     */
    public function actionFlushSchema($db = 'db')
    {
        $connection = Yii::$app->get($db, false);
        if ($connection === null) {
            $this->stdout("Unknown component \"$db\".\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$connection instanceof \yii\db\Connection) {
            $this->stdout("\"$db\" component doesn't inherit \\yii\\db\\Connection.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        } elseif (!$this->confirm("Flush cache schema for \"$db\" connection?")) {
            return ExitCode::OK;
        }

        try {
            $schema = $connection->getSchema();
            $schema->refresh();
            $this->stdout("Schema cache for component \"$db\", was flushed.\n\n", Console::FG_GREEN);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . "\n\n", Console::FG_RED);
        }
    }

    /**
     * 通知用户已找到给定的缓存并且可以刷新。
     * @param array $caches 缓存组件类的数组
     */
    private function notifyCachesCanBeFlushed($caches)
    {
        $this->stdout("The following caches were found in the system:\n\n", Console::FG_YELLOW);

        foreach ($caches as $name => $class) {
            if ($this->canBeFlushed($class)) {
                $this->stdout("\t* $name ($class)\n", Console::FG_GREEN);
            } else {
                $this->stdout("\t* $name ($class) - can not be flushed via console\n", Console::FG_YELLOW);
            }
        }

        $this->stdout("\n");
    }

    /**
     * 通知用户系统中未找到任何缓存。
     */
    private function notifyNoCachesFound()
    {
        $this->stdout("No cache components were found in the system.\n", Console::FG_RED);
    }

    /**
     * 通知用户在系统中找不到给定的缓存组件。
     * @param array $cachesNames
     */
    private function notifyNotFoundCaches($cachesNames)
    {
        $this->stdout("The following cache components were NOT found:\n\n", Console::FG_RED);

        foreach ($cachesNames as $name) {
            $this->stdout("\t* $name \n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * @param array $caches
     */
    private function notifyFlushed($caches)
    {
        $this->stdout("The following cache components were processed:\n\n", Console::FG_YELLOW);

        foreach ($caches as $cache) {
            $this->stdout("\t* " . $cache['name'] . ' (' . $cache['class'] . ')', Console::FG_GREEN);

            if (!$cache['is_flushed']) {
                $this->stdout(" - not flushed\n", Console::FG_RED);
            } else {
                $this->stdout("\n");
            }
        }

        $this->stdout("\n");
    }

    /**
     * 如果应刷新缓存，则提示用户确认。
     * @param array $cachesNames
     * @return bool
     */
    private function confirmFlush($cachesNames)
    {
        $this->stdout("The following cache components will be flushed:\n\n", Console::FG_YELLOW);

        foreach ($cachesNames as $name) {
            $this->stdout("\t* $name \n", Console::FG_GREEN);
        }

        return $this->confirm("\nFlush above cache components?");
    }

    /**
     * 返回系统中的缓存数组，键是缓存组件名称，值是类名。
     * @param array $cachesNames 缓存可以找到
     * @return array
     */
    private function findCaches(array $cachesNames = [])
    {
        $caches = [];
        $components = Yii::$app->getComponents();
        $findAll = ($cachesNames === []);

        foreach ($components as $name => $component) {
            if (!$findAll && !in_array($name, $cachesNames, true)) {
                continue;
            }

            if ($component instanceof CacheInterface) {
                $caches[$name] = get_class($component);
            } elseif (is_array($component) && isset($component['class']) && $this->isCacheClass($component['class'])) {
                $caches[$name] = $component['class'];
            } elseif (is_string($component) && $this->isCacheClass($component)) {
                $caches[$name] = $component;
            } elseif ($component instanceof \Closure) {
                $cache = Yii::$app->get($name);
                if ($this->isCacheClass($cache)) {
                    $cacheClass = get_class($cache);
                    $caches[$name] = $cacheClass;
                }
            }
        }

        return $caches;
    }

    /**
     * 检查给定的类是否为 Cache 类。
     * @param string $className 类名。
     * @return bool
     */
    private function isCacheClass($className)
    {
        return is_subclass_of($className, 'yii\caching\CacheInterface') || $className === 'yii\caching\CacheInterface';
    }

    /**
     * 检查是否可以刷新某个类的缓存。
     * @param string $className 类名。
     * @return bool
     */
    private function canBeFlushed($className)
    {
        return !is_a($className, ApcCache::className(), true) || PHP_SAPI !== 'cli';
    }
}
