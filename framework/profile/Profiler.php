<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * Profiler provides profiling support. It stores profiling messages in the memory and sends them to different targets
 * according to [[targets]].
 *
 * A Profiler instance can be accessed via `Yii::getProfiler()`.
 *
 * For convenience, a set of shortcut methods are provided for profiling via the [[Yii]] class:
 *
 * - [[Yii::beginProfile()]]
 * - [[Yii::endProfile()]]
 *
 * For more details and usage information on Profiler, see the [guide article on profiling](guide:runtime-profiling)
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 3.0.0
 */
class Profiler extends Component implements ProfilerInterface
{
    /**
     * @var bool whether to profiler is enabled. Defaults to true.
     * You may use this field to disable writing of the profiling messages and thus save the memory usage.
     */
    public $enabled = true;
    /**
     * @var array[] complete profiling messages.
     * Each message has a following keys:
     *
     * - token: string, profiling token.
     * - category: string, message category.
     * - nestedLevel: int, profiling message nested level.
     * - beginTime: float, profiling begin timestamp obtained by microtime(true).
     * - endTime: float, profiling end timestamp obtained by microtime(true).
     * - duration: float, profiling block duration in milliseconds.
     * - beginMemory: int, memory usage at the beginning of profile block in bytes, obtained by `memory_get_usage()`.
     * - endMemory: int, memory usage at the end of profile block in bytes, obtained by `memory_get_usage()`.
     * - memoryDiff: int, a diff between 'endMemory' and 'beginMemory'.
     */
    public $messages = [];

    /**
     * @var array pending profiling messages, e.g. the ones which have begun but not ended yet.
     */
    private $_pendingMessages = [];
    /**
     * @var int current profiling messages nested level.
     */
    private $_nestedLevel = 0;
    /**
     * @var array|Target[] the profiling targets. Each array element represents a single [[Target|profiling target]] instance
     * or the configuration for creating the profiling target instance.
     */
    private $_targets = [];
    /**
     * @var bool whether [[targets]] have been initialized, e.g. ensured to be objects.
     */
    private $_isTargetsInitialized = false;


    /**
     * Initializes the profiler by registering [[flush()]] as a shutdown function.
     */
    public function init()
    {
        parent::init();
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * @return Target[] the profiling targets. Each array element represents a single [[Target|profiling target]] instance.
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
     * @param array|Target[] $targets the profiling targets. Each array element represents a single [[Target|profiling target]] instance
     * or the configuration for creating the profiling target instance.
     */
    public function setTargets($targets)
    {
        $this->_targets = $targets;
        $this->_isTargetsInitialized = false;
    }

    /**
     * Adds extra target to [[targets]].
     * @param Target|array $target the log target instance or its DI compatible configuration.
     * @param string|null $name array key to be used to store target, if `null` is given target will be append
     * to the end of the array by natural integer key.
     */
    public function addTarget($target, $name = null)
    {
        if (!$target instanceof Target) {
            $this->_isTargetsInitialized = false;
        }
        if ($name === null) {
            $this->_targets[] = $target;
        } else {
            $this->_targets[$name] = $target;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function begin($token, array $context = [])
    {
        if (!$this->enabled) {
            return;
        }

        $category = isset($context['category']) ?: 'application';

        $message = array_merge($context, [
            'token' => $token,
            'category' => $category,
            'nestedLevel' => $this->_nestedLevel,
            'beginTime' => microtime(true),
            'beginMemory' => memory_get_usage(),
        ]);

        $this->_pendingMessages[$category][$token][] = $message;
        $this->_nestedLevel++;
    }

    /**
     * {@inheritdoc}
     */
    public function end($token, array $context = [])
    {
        if (!$this->enabled) {
            return;
        }

        $category = isset($context['category']) ?: 'application';

        if (empty($this->_pendingMessages[$category][$token])) {
            throw new InvalidArgumentException('Unexpected ' . get_called_class() . '::end() call for category "' . $category . '" token "' . $token . '". A matching begin() is not found.');
        }

        $message = array_pop($this->_pendingMessages[$category][$token]);
        if (empty($this->_pendingMessages[$category][$token])) {
            unset($this->_pendingMessages[$category][$token]);
            if (empty($this->_pendingMessages[$category])) {
                unset($this->_pendingMessages[$category]);
            }
        }

        $message = array_merge(
            $message,
            $context,
            [
                'endTime' => microtime(true),
                'endMemory' => memory_get_usage(),
            ]
        );

        $message['duration'] = $message['endTime'] - $message['beginTime'];
        $message['memoryDiff'] = $message['endMemory'] - $message['beginMemory'];

        $this->messages[] = $message;
        $this->_nestedLevel--;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        foreach ($this->_pendingMessages as $category => $categoryMessages) {
            foreach ($categoryMessages as $token => $messages) {
                if (!empty($messages)) {
                    Yii::warning('Unclosed profiling entry detected: category "' . $category . '" token "' . $token . '"', __METHOD__);
                }
            }
        }
        $this->_pendingMessages = [];
        $this->_nestedLevel = 0;

        if (empty($this->messages)) {
            return;
        }

        $messages = $this->messages;
        // new messages could appear while the existing ones are being handled by targets
        $this->messages = [];

        $this->dispatch($messages);
    }

    /**
     * Dispatches the profiling messages to [[targets]].
     * @param array $messages the profiling messages.
     */
    protected function dispatch($messages)
    {
        foreach ($this->getTargets() as $target) {
            $target->collect($messages);
        }
    }
}