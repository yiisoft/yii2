<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Yii;
use yii\base\Component;
use yii\base\ErrorHandler;
use yii\profile\Profiler;

/**
 * Logger records logged messages in memory and sends them to different targets according to [[targets]].
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
 * or [[DbTarget|database]], according to the [[targets]].
 *
 * @property array|Target[] $targets the log targets. See [[setTargets()]] for details.
 * @property array $dbProfiling The first element indicates the number of SQL statements executed, and the
 * second element the total time spent in SQL execution. This property is read-only.
 * @property float $elapsedTime The total elapsed time in seconds for current request. This property is
 * read-only.
 * @property array $profiling The profiling results. Each element is an array consisting of these elements:
 * `info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`. The `memory` and
 * `memoryDiff` values are available since version 2.0.11. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends Component implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Error message level. An error message is one that indicates the abnormal termination of the
     * application and may require developer's handling.
     * @deprecated since 2.1.0 Use [[LogLevel::ERROR]] instead.
     */
    const LEVEL_ERROR = LogLevel::ERROR;
    /**
     * Warning message level. A warning message is one that indicates some abnormal happens but
     * the application is able to continue to run. Developers should pay attention to this message.
     * @deprecated since 2.1.0 Use [[LogLevel::WARNING]] instead.
     */
    const LEVEL_WARNING = LogLevel::WARNING;
    /**
     * Informational message level. An informational message is one that includes certain information
     * for developers to review.
     * @deprecated since 2.1.0 Use [[LogLevel::INFO]] instead.
     */
    const LEVEL_INFO = LogLevel::INFO;
    /**
     * Tracing message level. An tracing message is one that reveals the code execution flow.
     * @deprecated since 2.1.0 Use [[LogLevel::DEBUG]] instead.
     */
    const LEVEL_TRACE = LogLevel::DEBUG;

    /**
     * @var array logged messages. This property is managed by [[log()]] and [[flush()]].
     * Each log message is of the following structure:
     *
     * ```
     * [
     *   [0] => level (string)
     *   [1] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [2] => context (array)
     * ]
     * ```
     *
     * Message context has a following keys:
     *
     * - category: string, message category.
     * - time: float, message timestamp obtained by microtime(true).
     * - trace: array, debug backtrace, contains the application code call stacks.
     * - memory: int, memory usage in bytes, obtained by `memory_get_usage()`, available since version 2.0.11.
     */
    public $messages = [];
    /**
     * @var int how many messages should be logged before they are flushed from memory and sent to targets.
     * Defaults to 1000, meaning the [[flush]] method will be invoked once every 1000 messages logged.
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
     * @var array|Target[] the log targets. Each array element represents a single [[Target|log target]] instance
     * or the configuration for creating the log target instance.
     * @since 2.1
     */
    private $_targets = [];
    /**
     * @var bool whether [[targets]] have been initialized, e.g. ensured to be objects.
     * @since 2.1
     */
    private $_isTargetsInitialized = false;


    /**
     * @return Target[] the log targets. Each array element represents a single [[Target|log target]] instance.
     * @since 2.1
     */
    public function getTargets()
    {
        if (!$this->_isTargetsInitialized) {
            foreach ($this->_targets as $name => $target) {
                if (!$target instanceof Target) {
                    $this->_targets[$name] = Yii::createObject($target);
                }
            }
            $this->_isTargetsInitialized = true;
        }
        return $this->_targets;
    }

    /**
     * @param array|Target[] $targets the log targets. Each array element represents a single [[Target|log target]] instance
     * or the configuration for creating the log target instance.
     * @since 2.1
     */
    public function setTargets($targets)
    {
        $this->_targets = $targets;
        $this->_isTargetsInitialized = false;
    }

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
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        if (!isset($context['time'])) {
            $context['time'] = microtime(true);
        }
        if (!isset($context['trace'])) {
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
            $context['trace'] = $traces;
        }

        if (!isset($context['memory'])) {
            $context['memory'] = memory_get_usage();
        }

        if (!isset($context['category'])) {
            $context['category'] = 'application';
        }

        $this->messages[] = [$level, $message, $context];

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
        $messages = $this->messages;
        // https://github.com/yiisoft/yii2/issues/5619
        // new messages could be logged while the existing ones are being handled by targets
        $this->messages = [];

        $this->dispatch($messages, $final);
    }

    /**
     * Dispatches the logged messages to [[targets]].
     * @param array $messages the logged messages
     * @param bool $final whether this method is called at the end of the current application
     * @since 2.1
     */
    protected function dispatch($messages, $final)
    {
        $targetErrors = [];
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                try {
                    $target->collect($messages, $final);
                } catch (\Exception $e) {
                    $target->enabled = false;
                    $targetErrors[] = [
                        'Unable to send log via ' . get_class($target) . ': ' . ErrorHandler::convertExceptionToString($e),
                        LogLevel::WARNING,
                        __METHOD__,
                        microtime(true),
                        [],
                    ];
                }
            }
        }

        if (!empty($targetErrors)) {
            $this->dispatch($targetErrors, true);
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
     * Returns the text display of the specified level.
     * @param mixed $level the message level, e.g. [[LogLevel::ERROR]], [[LogLevel::WARNING]].
     * @return string the text display of the level
     */
    public static function getLevelName($level)
    {
        if (is_string($level)) {
            return $level;
        }
        return 'unknown';
    }
}
