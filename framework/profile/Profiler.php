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
 * Profiler provides profiling support.
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 2.1
 */
class Profiler extends Component implements ProfilerInterface
{
    /**
     * @var bool whether to profiler is enabled. Defaults to true.
     * You may use this field to disable writing of the profiling messages and thus save the memory usage.
     */
    public $enabled = true;
    /**
     * @var array complete profiling messages.
     */
    public $messages = [];

    /**
     * @var array pending profiling messages, e.g. the ones which have begun but not ended yet.
     */
    private $_pendingMessages = [];
    /**
     * @var array|Target[] the profiling targets. Each array element represents a single [[Target|profiling target]] instance
     * or the configuration for creating the profiling target instance.
     * @since 2.1
     */
    private $_targets = [];
    /**
     * @var bool whether [[targets]] have been initialized, e.g. ensured to be objects.
     * @since 2.1
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
     * {@inheritdoc}
     */
    public function begin($token, $category)
    {
        if (!$this->enabled) {
            return;
        }

        $message = [
            'token' => $token,
            'category' => $category,
            'beginTime' => microtime(true),
            'beginMemory' => memory_get_usage(),
        ];

        $this->_pendingMessages[$category][$token][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function end($token, $category)
    {
        if (!$this->enabled) {
            return;
        }

        if (empty($this->_pendingMessages[$category][$token])) {
            throw new InvalidArgumentException('Unexpected ' . get_called_class() . '::end() call for category "' . $category . '" token "' . $token . '". A matching begin() is not found.');
        }

        $message = array_pop($this->_pendingMessages[$category][$token]);
        $message['endTime'] = microtime(true);
        $message['endMemory'] = memory_get_usage();
        $message['duration'] = $message['endTime'] - $message['beginTime'];
        $message['memoryDiff'] = $message['endMemory'] - $message['beginMemory'];

        $this->messages[] = $message;
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