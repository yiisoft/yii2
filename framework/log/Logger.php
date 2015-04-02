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
 * When the application ends or [[flushInterval]] is reached, Logger will call [[flush()]]
 * to send logged messages to different log targets, such as [[FileTarget|file]], [[EmailTarget|email]],
 * or [[DbTarget|database]], with the help of the [[dispatcher]].
 *
 * @property array $dbProfiling The first element indicates the number of SQL statements executed, and the
 * second element the total time spent in SQL execution. This property is read-only.
 * @property float $elapsedTime The total elapsed time in seconds for current request. This property is
 * read-only.
 * @property array $profiling The profiling results. Each element is an array consisting of these elements:
 * `info`, `category`, `timestamp`, `trace`, `level`, `duration`. This property is read-only.
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
     * Tracing message level. An tracing message is one that reveals the code execution flow.
     */
    const LEVEL_TRACE = 0x08;
    /**
     * Profiling message level. This indicates the message is for profiling purpose.
     */
    const LEVEL_PROFILE = 0x40;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the
     * beginning of a profiling block.
     */
    const LEVEL_PROFILE_BEGIN = 0x50;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the
     * end of a profiling block.
     */
    const LEVEL_PROFILE_END = 0x60;

    /**
     * @var array logged messages. This property is managed by [[log()]] and [[flush()]].
     * Each log message is of the following structure:
     *
     * ~~~
     * [
     *   [0] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [1] => level (integer)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true))
     *   [4] => traces (array, debug backtrace, contains the application code call stacks)
     * ]
     * ~~~
     */
    public $messages = [];
    /**
     * @var integer how many messages should be logged before they are flushed from memory and sent to targets.
     * Defaults to 1000, meaning the [[flush]] method will be invoked once every 1000 messages logged.
     * Set this property to be 0 if you don't want to flush messages until the application terminates.
     * This property mainly affects how much memory will be taken by the logged messages.
     * A smaller value means less memory, but will increase the execution time due to the overhead of [[flush()]].
     */
    public $flushInterval = 1000;
    /**
     * @var integer how much call stack information (file name and line number) should be logged for each message.
     * If it is greater than 0, at most that number of call stacks will be logged. Note that only application
     * call stacks are counted.
     */
    public $traceLevel = 0;
    /**
     * @var Dispatcher the message dispatcher
     */
    public $dispatcher;


    /**
     * Initializes the logger by registering [[flush()]] as a shutdown function.
     */
    public function init()
    {
        parent::init();
        register_shutdown_function(function () {
            // make sure "flush()" is called last when there are multiple shutdown functions
            register_shutdown_function([$this, 'flush'], true);
        });
    }

    /**
     * Logs a message with the given type and category.
     * If [[traceLevel]] is greater than 0, additional call stack information about
     * the application code will be logged as well.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure that will be handled by a [[Target|log target]].
     * @param integer $level the level of the message. This must be one of the following:
     * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
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
        $this->messages[] = [$message, $level, $category, $time, $traces];
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }

    /**
     * Flushes log messages from memory to targets.
     * @param boolean $final whether this is a final call during a request.
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
     * `info`, `category`, `timestamp`, `trace`, `level`, `duration`.
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
     * Returns the statistical results of DB queries.
     * The results returned include the number of SQL statements executed and
     * the total time spent.
     * @return array the first element indicates the number of SQL statements executed,
     * and the second element the total time spent in SQL execution.
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
     * Calculates the elapsed time for the given log messages.
     * @param array $messages the log messages obtained from profiling
     * @return array timings. Each element is an array consisting of these elements:
     * `info`, `category`, `timestamp`, `trace`, `level`, `duration`.
     */
    public function calculateTimings($messages)
    {
        $timings = [];
        $stack = [];

        foreach ($messages as $i => $log) {
            list($token, $level, $category, $timestamp, $traces) = $log;
            $log[5] = $i;
            if ($level == Logger::LEVEL_PROFILE_BEGIN) {
                $stack[] = $log;
            } elseif ($level == Logger::LEVEL_PROFILE_END) {
                if (($last = array_pop($stack)) !== null && $last[0] === $token) {
                    $timings[$last[5]] = [
                        'info' => $last[0],
                        'category' => $last[2],
                        'timestamp' => $last[3],
                        'trace' => $last[4],
                        'level' => count($stack),
                        'duration' => $timestamp - $last[3],
                    ];
                }
            }
        }

        ksort($timings);

        return array_values($timings);
    }


    /**
     * Returns the text display of the specified level.
     * @param integer $level the message level, e.g. [[LEVEL_ERROR]], [[LEVEL_WARNING]].
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
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
}
