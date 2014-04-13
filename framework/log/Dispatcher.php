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
 * Dispatcher manages a set of [[Target|log targets]].
 *
 * Dispatcher implements [[dispatch()]] that forwards the log messages from [[Logger]] to
 * the registered log [[targets]].
 *
 * Dispatcher is registered as a core application component and can be accessed using `Yii::$app->log`.
 *
 * You may configure the targets in application configuration, like the following:
 *
 * ~~~
 * [
 *     'components' => [
 *         'log' => [
 *             'targets' => [
 *                 'file' => [
 *                     'class' => 'yii\log\FileTarget',
 *                     'levels' => ['trace', 'info'],
 *                     'categories' => ['yii\*'],
 *                 ],
 *                 'email' => [
 *                     'class' => 'yii\log\EmailTarget',
 *                     'levels' => ['error', 'warning'],
 *                     'message' => [
 *                         'to' => 'admin@example.com',
 *                     ],
 *                 ],
 *             ],
 *         ],
 *     ],
 * ]
 * ~~~
 *
 * Each log target can have a name and can be referenced via the [[targets]] property
 * as follows:
 *
 * ~~~
 * Yii::$app->log->targets['file']->enabled = false;
 * ~~~
 *
 * @property integer $flushInterval How many messages should be logged before they are sent to targets. This
 * method returns the value of [[Logger::flushInterval]].
 * @property Logger $logger The logger. If not set, [[\Yii::getLogger()]] will be used.
 * @property integer $traceLevel How many application call stacks should be logged together with each message.
 * This method returns the value of [[Logger::traceLevel]]. Defaults to 0.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Dispatcher extends Component
{
    /**
     * @var array|Target[] the log targets. Each array element represents a single [[Target|log target]] instance
     * or the configuration for creating the log target instance.
     */
    public $targets = [];
    /**
     * @var Logger the logger.
     */
    private $_logger;


    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
            unset($config['logger']);
        }
        // connect logger and dispatcher
        $this->getLogger();

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->targets as $name => $target) {
            if (!$target instanceof Target) {
                $this->targets[$name] = Yii::createObject($target);
            }
        }
    }

    /**
     * Gets the connected logger.
     * If not set, [[\Yii::getLogger()]] will be used.
     * @property Logger the logger. If not set, [[\Yii::getLogger()]] will be used.
     * @return Logger the logger.
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->setLogger(Yii::getLogger());
        }
        return $this->_logger;
    }

    /**
     * Sets the connected logger.
     * @param Logger $value the logger.
     */
    public function setLogger($value)
    {
        $this->_logger = $value;
        $this->_logger->dispatcher = $this;
    }

    /**
     * @return integer how many application call stacks should be logged together with each message.
     * This method returns the value of [[Logger::traceLevel]]. Defaults to 0.
     */
    public function getTraceLevel()
    {
        return $this->getLogger()->traceLevel;
    }

    /**
     * @param integer $value how many application call stacks should be logged together with each message.
     * This method will set the value of [[Logger::traceLevel]]. If the value is greater than 0,
     * at most that number of call stacks will be logged. Note that only application call stacks are counted.
     * Defaults to 0.
     */
    public function setTraceLevel($value)
    {
        $this->getLogger()->traceLevel = $value;
    }

    /**
     * @return integer how many messages should be logged before they are sent to targets.
     * This method returns the value of [[Logger::flushInterval]].
     */
    public function getFlushInterval()
    {
        return $this->getLogger()->flushInterval;
    }

    /**
     * @param integer $value how many messages should be logged before they are sent to targets.
     * This method will set the value of [[Logger::flushInterval]].
     * Defaults to 1000, meaning the [[Logger::flush()]] method will be invoked once every 1000 messages logged.
     * Set this property to be 0 if you don't want to flush messages until the application terminates.
     * This property mainly affects how much memory will be taken by the logged messages.
     * A smaller value means less memory, but will increase the execution time due to the overhead of [[Logger::flush()]].
     */
    public function setFlushInterval($value)
    {
        $this->getLogger()->flushInterval = $value;
    }

    /**
     * Dispatches the logged messages to [[targets]].
     * @param array $messages the logged messages
     * @param boolean $final whether this method is called at the end of the current application
     */
    public function dispatch($messages, $final)
    {
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                $target->collect($messages, $final);
            }
        }
    }
}
