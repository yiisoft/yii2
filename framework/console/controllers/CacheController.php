<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\caching\Cache;
use yii\helpers\Console;
use yii\console\Exception;

/**
 * Allows you to flush cache.
 *
 * see list of available components to flush:
 *
 *     yii cache
 *
 * flush particular components specified by their names:
 *
 *     yii cache/flush first second third
 *
 * flush all cache components that can be found in the system
 *
 *     yii cache/flush-all
 *
 * Note that the command uses cache components defined in your console application configuration file. If components
 * configured are different from web application, web application cache won't be cleared. In order to fix it please
 * duplicate web application cache components in console config. You can use any component names.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class CacheController extends Controller
{
    /**
     * Lists the caches that can be flushed.
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
     * Flushes given cache components.
     * For example,
     *
     * ~~~
     * # flushes caches specified by their id: "first", "second", "third"
     * yii cache/flush first second third
     * ~~~
     * 
     */
    public function actionFlush()
    {
        $cachesInput = func_get_args();
        
        if (empty($cachesInput)) {
            throw new Exception("You should specify cache components names");
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
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmFlush($foundCaches)) {
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' =>  Yii::$app->get($name)->flush(),
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    /**
     * Flushes all caches registered in the system.
     */
    public function actionFlushAll()
    {
        $caches = $this->findCaches();
        $cachesInfo = [];

        if (empty($caches)) {
            $this->notifyNoCachesFound();
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_flushed' =>  Yii::$app->get($name)->flush(),
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }

    /**
     * Clears DB schema cache for a given connection component.
     *
     * ~~~
     * # clears cache schema specified by component id: "db"
     * yii cache/flush-schema db
     * ~~~
     *
     * @param string $db id connection component
     * @return int exit code
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
            return self::EXIT_CODE_ERROR;
        }

        if (!$connection instanceof \yii\db\Connection) {
            $this->stdout("\"$db\" component doesn't inherit \\yii\\db\\Connection.\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        } else if (!$this->confirm("Flush cache schema for \"$db\" connection?")) {
            return static::EXIT_CODE_NORMAL;
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
     * Notifies user that given caches are found and can be flushed.
     * @param array $caches array of cache component classes
     */
    private function notifyCachesCanBeFlushed($caches)
    {
        $this->stdout("The following caches were found in the system:\n\n", Console::FG_YELLOW);

        foreach ($caches as $name => $class) {
            $this->stdout("\t* $name ($class)\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * Notifies user that there was not found any cache in the system.
     */
    private function notifyNoCachesFound()
    {
        $this->stdout("No cache components were found in the system.\n", Console::FG_RED);
    }

    /**
     * Notifies user that given cache components were not found in the system.
     * @param array $cachesNames
     */
    private function notifyNotFoundCaches($cachesNames)
    {
        $this->stdout("The following cache components were NOT found:\n\n", Console::FG_RED);

        foreach ($cachesNames as $name) {
            $this->stdout("\t * $name \n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * 
     * @param array $caches
     */
    private function notifyFlushed($caches)
    {
        $this->stdout("The following cache components were processed:\n\n", Console::FG_YELLOW);

        foreach ($caches as $cache) {
            $this->stdout("\t* " . $cache['name'] ." (" . $cache['class'] . ")", Console::FG_GREEN);

            if (!$cache['is_flushed']) {
                $this->stdout(" - not flushed\n", Console::FG_RED);
            } else {
                $this->stdout("\n");
            }
        }

        $this->stdout("\n");
    }

    /**
     * Prompts user with confirmation if caches should be flushed.
     * @param array $cachesNames
     * @return boolean
     */
    private function confirmFlush($cachesNames)
    {
        $this->stdout("The following cache components will be flushed:\n\n", Console::FG_YELLOW);

        foreach ($cachesNames as $name) {
            $this->stdout("\t * $name \n", Console::FG_GREEN);
        }

        return $this->confirm("\nFlush above cache components?");
    }

    /**
     * Returns array of caches in the system, keys are cache components names, values are class names.
     * @param array $cachesNames caches to be found
     * @return array
     */
    private function findCaches(array $cachesNames = [])
    {
        $caches = [];
        $components = Yii::$app->getComponents();
        $findAll = ($cachesNames == []);

        foreach ($components as $name => $component) {
            if (!$findAll && !in_array($name, $cachesNames)) {
                continue;
            }

            if ($component instanceof Cache) {
                $caches[$name] = get_class($component);
            } elseif (is_array($component) && isset($component['class']) && $this->isCacheClass($component['class'])) {
                $caches[$name] = $component['class'];
            } elseif (is_string($component) && $this->isCacheClass($component)) {
                $caches[$name] = $component;
            }
        }

        return $caches;
    }

    /**
     * Checks if given class is a Cache class.
     * @param string $className class name.
     * @return boolean
     */
    private function isCacheClass($className)
    {
        return is_subclass_of($className, Cache::className());
    }

}
