<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\Component;

/**
 * Logger records logged messages in memory and sends them to different targets if [[dispatcher]] is set.
 *
 * A Logger instance can be accessed via `Yii::getLogger()`. You can call the method [[log()]] to record a single log message.
 * For convenience, a set of shortcut methods are provided for logging messages of various severity levels
 * via the [[Yii]] class:
 *
 * - [[Yii::trace()]]
 * - [[Yii::error()]]
 * - [[Yii::warning()]]
 * - [[Yii::info()]]
 * - [[Yii::beginProfile()]]
 * - [[Yii::endProfile()]]
 *
 * For more details and usage information on Logger, see the [guide article on logging](guide:runtime-logging).
 *
 * When the application ends or [[flushInterval]] is reached, Logger will call [[flush()]]
 * to send logged messages to different log targets, such as [[FileTarget|file]], [[EmailTarget|email]],
 * or [[DbTarget|database]], with the help of the [[dispatcher]].
 *
 * @property-read array $dbProfiling The first element indicates the number of SQL statements executed, and
 * the second element the total time spent in SQL execution.
 * @property-read float $elapsedTime The total elapsed time in seconds for current request.
 * @property-read array $profiling The profiling results. Each element is an array consisting of these
 * elements: `info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`. The `memory`
 * and `memoryDiff` values are available since version 2.0.11.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends Component
{
    /**
     * Error message level. An error message is one that indicates the abnormal termination of the
     * application and may require developer's handling.
     */
    const LEVEL_ERROR = 0x01;
    /**
     * Warning message level. A warning message is one that indicates some abnormal happens but
     * the application is able to continue to run. Developers should pay attention to this message.
     */
    const LEVEL_WARNING = 0x02;
    /**
     * Informational message level. An informational message is one that includes certain information
     * for developers to review.
     */
    const LEVEL_INFO = 0x04;
    /**
     * Tracing message level. A tracing message is one that reveals the code execution flow.
     */
    const LEVEL_TRACE = 0x08;
    /**
     * Profiling message level. This indicates the message is for profiling purpose.
     */
    const LEVEL_PROFILE = 0x40;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the beginning
     * of a profiling block.
     */
    const LEVEL_PROFILE_BEGIN = 0x50;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the end
     * of a profiling block.
     */
    const LEVEL_PROFILE_END = 0x60;

    /**
     * @var array logged messages. This property is managed by [[log()]] and [[flush()]].
     * Each log message is of the following structure:
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
     * @var int how many messages should be logged before they are flushed from memory and sent to targets.
     * Defaults to 1000, meaning the [[flush()]] method will be invoked once every 1000 messages logged.
     * Set this property to be 0 if you don't want to flush messages until the application terminates.
     * This property mainly affects how much memory will be taken by the logged messages.
     * A smaller value means less memory, but will increase the execution time due to the overhead of [[flush()]].
     */
    public $flushInterval = 1000;
    /**
     * @var int how much call stack information (file name and line number) should be logged for each message.
     * If it is greater than 0, at most that number of call stacks will be logged. Note that only application
     * call stacks are counted.
     */
    public $traceLevel = 0;
    /**
     * @var Dispatcher the message dispatcher.
     */
    public $dispatcher;
    /**
     * @var array of event names used to get statistical results of DB queries.
     * @since 2.0.41
     */
    public $dbEventNames = ['yii\db\Command::query', 'yii\db\Command::execute'];
    /**
     * @var bool whether the profiling-aware mode should be switched on.
     * If on, [[flush()]] makes sure that profiling blocks are flushed in pairs. In case that any dangling messages are
     * detected these are kept for the next flush interval to find their pair. To prevent memory leaks, when number of
     * dangling messages reaches flushInterval value, logger flushes them immediately and triggers a warning.
     * Keep in mind that profiling-aware mode is more time and memory consuming.
     * @since 2.0.43
     */
    public $profilingAware = false;


    /**
     * Initializes the logger by registering [[flush()]] as a shutdown function.
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
     * Logs a message with the given type and category.
     * If [[traceLevel]] is greater than 0, additional call stack information about
     * the application code will be logged as well.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure that will be handled by a [[Target|log target]].
     * @param int $level the level of the message. This must be one of the following:
     * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`, `Logger::LEVEL_PROFILE`,
     * `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`.
     * @param string $category the category of the message.
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
        $data = [$message, $level, $category, $time, $traces, memory_get_usage()];
        if ($this->profilingAware && in_array($level, [self::LEVEL_PROFILE_BEGIN, self::LEVEL_PROFILE_END])) {
            $this->messages[($level == self::LEVEL_PROFILE_BEGIN ? 'begin-' : 'end-') . md5(json_encode($message))] = $data;
        } else {
            $this->messages[] = $data;
        }

        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }

    /**
     * Flushes log messages from memory to targets.
     * @param bool $final whether this is a final call during a request.
     */
    public function flush($final = false)
    {
        if ($this->profilingAware) {
            $keep = [];
            $messages = [];
            foreach ($this->messages as $index => $message) {
                if (is_int($index)) {
                    $messages[] = $message;
                } else {
                    if (strncmp($index, 'begin-', 6) === 0) {
                        $oppositeProfile = 'end-' . substr($index, 6);
                    } else {
                        $oppositeProfile = 'begin-' . substr($index, 4);
                    }
                    if (array_key_exists($oppositeProfile, $this->messages)) {
                        $messages[] = $message;
                    } else {
                        $keep[$index] = $message;
                    }
                }
            }
            if ($this->flushInterval > 0 && count($keep) >= $this->flushInterval) {
                $this->messages = [];
                $this->log(
                    'Number of dangling profiling block messages reached flushInterval value and therefore these were flushed. Please consider setting higher flushInterval value or making profiling blocks shorter.',
                    self::LEVEL_WARNING
                );
                $messages = array_merge($messages, array_values($keep));
            } else {
                $this->messages = $keep;
            }
        } else {
            $messages = $this->messages;
            $this->messages = [];
        }

        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }

    /**
     * Returns the total elapsed time since the start of the current request.
     * This method calculates the difference between now and the timestamp
     * defined by constant `YII_BEGIN_TIME` which is evaluated at the beginning
     * of [[\yii\BaseYii]] class file.
     * @return float the total elapsed time in seconds for current request.
     */
    public function getElapsedTime()
    {
        return microtime(true) - YII_BEGIN_TIME;
    }

    /**
     * Returns the profiling results.
     *
     * By default, all profiling results will be returned. You may provide
     * `$categories` and `$excludeCategories` as parameters to retrieve the
     * results that you are interested in.
     *
     * @param array $categories list of categories that you are interested in.
     * You can use an asterisk at the end of a category to do a prefix match.
     * For example, 'yii\db\*' will match categories starting with 'yii\db\',
     * such as 'yii\db\Connection'.
     * @param array $excludeCategories list of categories that you want to exclude
     * @return array the profiling results. Each element is an array consisting of these elements:
     * `info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`.
     * The `memory` and `memoryDiff` values are available since version 2.0.11.
     */
    public function getProfiling($categories = [], $excludeCategories = [])
    {
        $timings = $this->calculateTimings($this->messages);
        if (empty($categories) && empty($excludeCategories)) {
            return $timings;
        }

        foreach ($timings as $outerIndex => $outerTimingItem) {
            $currentIndex = $outerIndex;
            $matched = empty($categories);
            foreach ($categories as $category) {
                $prefix = rtrim($category, '*');
                if (
                    ($outerTimingItem['category'] === $category || $prefix !== $category)
                    && strpos($outerTimingItem['category'], $prefix) === 0
                ) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($excludeCategories as $category) {
                    $prefix = rtrim($category, '*');
                    foreach ($timings as $innerIndex => $innerTimingItem) {
                        $currentIndex = $innerIndex;
                        if (
                            ($innerTimingItem['category'] === $category || $prefix !== $category)
                            && strpos($innerTimingItem['category'], $prefix) === 0
                        ) {
                            $matched = false;
                            break;
                        }
                    }
                }
            }

            if (!$matched) {
                unset($timings[$currentIndex]);
            }
        }

        return array_values($timings);
    }

    /**
     * Returns the statistical results of DB queries.
     * The results returned include the number of SQL statements executed and
     * the total time spent.
     * @return array the first element indicates the number of SQL statements executed,
     * and the second element the total time spent in SQL execution.
     */
    public function getDbProfiling()
    {
        $timings = $this->getProfiling($this->dbEventNames);
        $count = count($timings);
        $time = 0;
        foreach ($timings as $timing) {
            $time += $timing['duration'];
        }

        return [$count, $time];
    }

    /**
     * Calculates the elapsed time for the given log messages.
     * @param array $messages the log messages obtained from profiling
     * @return array timings. Each element is an array consisting of these elements:
     * `info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`.
     * The `memory` and `memoryDiff` values are available since version 2.0.11.
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
     * Returns the text display of the specified level.
     * @param int $level the message level, e.g. [[LEVEL_ERROR]], [[LEVEL_WARNING]].
     * @return string the text display of the level
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
