<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\Component;

/**
 * Logger 如果设置了 [[dispatcher]]，它将把日志消息记在内存并且发送到所设置的日志 [[dispatcher]]。
 *
 * 可以通过 `Yii::getLogger()` 获取 Logger 实例，并调用当前实例的 [[log()]] 方法去记录一条日志消息。
 * 为了方便起见，
 * [[Yii]] 类提供了一组用于记录各种级别消息的快捷方法。
 *
 * - [[Yii::trace()]]
 * - [[Yii::error()]]
 * - [[Yii::warning()]]
 * - [[Yii::info()]]
 * - [[Yii::beginProfile()]]
 * - [[Yii::endProfile()]]
 *
 * 有关于 Logger 的详细信息和使用方法，请参考权威指南的 Logger 章节。
 *
 * 当应用程序结束或者执行到 [[flushInterval]] 时 Logger 将会自动调用 [[flush()]]
 * 从而通过 [[dispatcher]] 把消息记录到如 [[FileTarget|file]]，[[EmailTarget|email]]，
 * 或者 [[DbTarget|database]] 不同的目标。
 *
 * @property array $dbProfiling 该数组的第一个元素当前执行的 sql，
 * 第二个元素为当前 sql 所消耗的时间。该属性只读。
 * @property float $elapsedTime 当前请求的总耗时（以秒为单位）。
 * 该属性只读。
 * @property array $profiling 分析结果。每个元素都是由这些元素组成的数组：
 * `info`，`category`，`timestamp`，`trace`，`level`，`duration`，`memory`，`memoryDiff`。
 * 从版本 2.0.11 起，添加 `memory` 和 `memoryDiff` 元素。该属性只读。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends Component
{
    /**
     * Error 消息等级。错误消息表示应用程序异常终止。
     * 可能需要开发人员处理。
     */
    const LEVEL_ERROR = 0x01;
    /**
     * Warning 消息等级。警告消息是指一些异常发生，
     * 但是应用程序能够继续运行，开发人员应该注意这个消息。
     */
    const LEVEL_WARNING = 0x02;
    /**
     * Informational 消息等级。信息性消息是指包含特定信息的消息，
     * 供开发人员评审。
     */
    const LEVEL_INFO = 0x04;
    /**
     * Tracing 消息等级。跟踪消息是揭示代码执行流的消息。
     */
    const LEVEL_TRACE = 0x08;
    /**
     * Profiling 消息等级。这表明消息是用于分析。
     */
    const LEVEL_PROFILE = 0x40;
    /**
     * Profiling 消息等级。这表明消息是用于分析目的的，
     * 标志着分析块的开头。
     */
    const LEVEL_PROFILE_BEGIN = 0x50;
    /**
     * Profiling 消息等级。这表明消息是用于分析目的的，
     * 标志着分析块的结束。
     */
    const LEVEL_PROFILE_END = 0x60;

    /**
     * @var array 日志消息。该属性由 [[log()]] 和 [[flush()]] 操作。
     * 下面是日志消息的结构：
     *
     * ```
     * [
     *   [0] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [1] => level (integer)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true))
     *   [4] => traces (array, debug backtrace, contains the application code call stacks)
     *   [5] => memory usage in bytes (int, obtained by memory_get_usage()), available since version 2.0.11.
     * ]
     * ```
     */
    public $messages = [];
    /**
     * @var int 在从内存刷新并发送到目标之前，应该记录多少消息。
     * 默认值 1000，这意味着每记录 1000 条消息，就会调用 [[flush]] 方法一次。
     * 如果此属性设置为 0，程序终止之前将不会刷新消息。
     * 此属性主要影响日志消息将占用多少内存。
     * 较小的值意味着占用更少的内存，但是由于 [[flush()] 的开销，会增加执行时间。
     */
    public $flushInterval = 1000;
    /**
     * @var int 应该为每个消息记录多少调用堆栈信息（文件名和行号）。
     * 如果大于 0，最多记录调用堆栈的数量。
     * 注意，只计算应用程序调用的堆栈信息。
     */
    public $traceLevel = 0;
    /**
     * @var Dispatcher 消息调度器。
     */
    public $dispatcher;


    /**
     * 通过注册 [[flush()]] 作为一个关闭函数进行初始化 logger。
     */
    public function init()
    {
        parent::init();
        register_shutdown_function(function () {
            // make regular flush before other shutdown functions, which allows session data collection and so on
            $this->flush();
            // make sure log entries written by shutdown functions are also flushed
            // ensure "flush()" is called last when there are multiple shutdown functions
            register_shutdown_function([$this, 'flush'], true);
        });
    }

    /**
     * 使用给定的类型和类别记录消息。
     * 如果 [[traceLevel]] 是一个大于 0 的值，
     * 有关应用程序代码的其他调用堆栈信息也将被记录。
     * @param string|array $message 要被记录的消息。它可以是一个简单的字符串或者一个复杂的数据结构并使用指定的
     * [[Target|log target]] 进行处理。
     * @param int $level 消息等级。必须是以下类型之一：
     * `Logger::LEVEL_ERROR`，`Logger::LEVEL_WARNING`，`Logger::LEVEL_INFO`，`Logger::LEVEL_TRACE`，
     * `Logger::LEVEL_PROFILE_BEGIN`，`Logger::LEVEL_PROFILE_END`。
     * @param string $category 当前消息类型分类。
     */
    public function log($message, $level, $category = 'application')
    {
        $time = microtime(true);
        $traces = [];
        if ($this->traceLevel > 0) {
            $count = 0;
            $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_pop($ts); // remove the last trace since it would be the entry script, not very useful
            foreach ($ts as $trace) {
                if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII2_PATH) !== 0) {
                    unset($trace['object'], $trace['args']);
                    $traces[] = $trace;
                    if (++$count >= $this->traceLevel) {
                        break;
                    }
                }
            }
        }
        $this->messages[] = [$message, $level, $category, $time, $traces, memory_get_usage()];
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }

    /**
     * 将消息从内存中发送到指定目标。
     * @param bool $final 是否为请求期间的最终调用。
     */
    public function flush($final = false)
    {
        $messages = $this->messages;
        // https://github.com/yiisoft/yii2/issues/5619
        // new messages could be logged while the existing ones are being handled by targets
        $this->messages = [];
        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }

    /**
     * 返回自当前请求开始以来经过的总时间。
     * 该方法计算当前与在 [[\yii\BaseYii]] 类文件常量
     * `YII_BEGIN_TIME`
     * 定义的时间戳之间的差异。
     * @return float 当前请求的总耗时（以秒为单位）。
     */
    public function getElapsedTime()
    {
        return microtime(true) - YII_BEGIN_TIME;
    }

    /**
     * 返回分析结果。
     *
     * 默认情况下，将返回所有分析结果。你可以提供
     * `$categories` 和 `$excludeCategories` 作为参数来获取
     * 你需要关注的结果。
     *
     * @param array $categories 您感兴趣的类别的列表。
     * 您可以在类别的末尾使用星号来匹配前缀。
     * 'yii\db\*' 将匹配以 'yii\db\' 开头的类别，
     * 例如 'yii\db\Connection'。
     * @param array $excludeCategories 要排除的类别的列表
     * @return array 分析的结果。每个元素都是由这些元素组成的数组：
     * `info`，`category`，`timestamp`，`trace`，`level`，`duration`，`memory`，`memoryDiff`，
     * 从版本 2.0.11 起，添加 `memory` 和 `memoryDiff` 元素。
     */
    public function getProfiling($categories = [], $excludeCategories = [])
    {
        $timings = $this->calculateTimings($this->messages);
        if (empty($categories) && empty($excludeCategories)) {
            return $timings;
        }

        foreach ($timings as $i => $timing) {
            $matched = empty($categories);
            foreach ($categories as $category) {
                $prefix = rtrim($category, '*');
                if (($timing['category'] === $category || $prefix !== $category) && strpos($timing['category'], $prefix) === 0) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($excludeCategories as $category) {
                    $prefix = rtrim($category, '*');
                    foreach ($timings as $i => $timing) {
                        if (($timing['category'] === $category || $prefix !== $category) && strpos($timing['category'], $prefix) === 0) {
                            $matched = false;
                            break;
                        }
                    }
                }
            }

            if (!$matched) {
                unset($timings[$i]);
            }
        }

        return array_values($timings);
    }

    /**
     * 返回数据库查询的统计结果。
     * 返回的结果包括执行的 SQL
     * 语句的数量和花费的总时间。
     * @return array 第一个元素表示执行的 SQL 语句的数量，
     * 第二个元素是 SQL 执行花费的总时间。
     */
    public function getDbProfiling()
    {
        $timings = $this->getProfiling(['yii\db\Command::query', 'yii\db\Command::execute']);
        $count = count($timings);
        $time = 0;
        foreach ($timings as $timing) {
            $time += $timing['duration'];
        }

        return [$count, $time];
    }

    /**
     * 计算给定日志消息的运行时间。
     * @param array $messages 从分析中获得的日志消息
     * @return array timings。每个元素都是由这些元素组成的数组：
     * `info`，`category`，`timestamp`，`trace`，`level`，`duration`，`memory`，`memoryDiff`，
     * 从版本 2.0.11 起，添加 `memory` 和 `memoryDiff` 元素。
     */
    public function calculateTimings($messages)
    {
        $timings = [];
        $stack = [];

        foreach ($messages as $i => $log) {
            list($token, $level, $category, $timestamp, $traces) = $log;
            $memory = isset($log[5]) ? $log[5] : 0;
            $log[6] = $i;
            $hash = md5(json_encode($token));
            if ($level == self::LEVEL_PROFILE_BEGIN) {
                $stack[$hash] = $log;
            } elseif ($level == self::LEVEL_PROFILE_END) {
                if (isset($stack[$hash])) {
                    $timings[$stack[$hash][6]] = [
                        'info' => $stack[$hash][0],
                        'category' => $stack[$hash][2],
                        'timestamp' => $stack[$hash][3],
                        'trace' => $stack[$hash][4],
                        'level' => count($stack) - 1,
                        'duration' => $timestamp - $stack[$hash][3],
                        'memory' => $memory,
                        'memoryDiff' => $memory - (isset($stack[$hash][5]) ? $stack[$hash][5] : 0),
                    ];
                    unset($stack[$hash]);
                }
            }
        }

        ksort($timings);

        return array_values($timings);
    }


    /**
     * Returns 具体的等级文字描述。
     * @param int $level 消息等级，如 [[LEVEL_ERROR]]，[[LEVEL_WARNING]]。
     * @return string 返回当前等级的文字描述。
     */
    public static function getLevelName($level)
    {
        static $levels = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_INFO => 'info',
            self::LEVEL_TRACE => 'trace',
            self::LEVEL_PROFILE_BEGIN => 'profile begin',
            self::LEVEL_PROFILE_END => 'profile end',
            self::LEVEL_PROFILE => 'profile',
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
}
