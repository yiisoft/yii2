<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\Component;
use yii\base\ErrorHandler;

/**
 * Dispatcher 管理一组 [[Target|log targets]]。
 *
 * Dispatcher 实现方法 [[dispatch()]] 将日志消息从 [[Logger]]
 * 转发到 [[targets]]。
 *
 * Dispatcher 的一个实例被注册为核心应用程序组件，可以使用 `Yii::$app->log` 进行访问。
 *
 * 您可以在应用程序配置中配置目标，如下所示：
 *
 * ```php
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
 * ```
 *
 * 每个日志目标都可以有一个名称，可以通过 [[targets]] 属性引用，如下所示：
 *
 * ```php
 * Yii::$app->log->targets['file']->enabled = false;
 * ```
 *
 * @property int $flushInterval 在将消息发送到目标之前应记录多少条消息。
 * 此方法返回 [[Logger::flushInterval]] 的值。
 * @property Logger $logger 记录器。如果未设置，将使用 [[\Yii::getLogger()]]。
 * 请注意，此属性的类型在 getter 和 setter 中有所不同。有关详细信息，请参见 [[getLogger()]] 和 [[setLogger()]]。
 * @property int $traceLevel 每个消息应记录多少应用程序调用堆栈。
 * 此方法返回 [[Logger::traceLevel]] 的值。默认为 0。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Dispatcher extends Component
{
    /**
     * @var array|Target[] 日志目标。
     * 每个数组元素表示一个 [[Target|log target]] 实例或用于创建日志目标实例的配置。
     */
    public $targets = [];

    /**
     * @var Logger 记录器
     */
    private $_logger;


    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        // ensure logger gets set before any other config option
        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
            unset($config['logger']);
        }
        // connect logger and dispatcher
        $this->getLogger();

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
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
     * 获取连接的记录器。
     * 如果未设置，将使用 [[\Yii::getLogger()]]。
     * @property Logger 记录器。如果未设置，将使用 [[\Yii::getLogger()]]。
     * @return Logger 记录器
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->setLogger(Yii::getLogger());
        }

        return $this->_logger;
    }

    /**
     * 设置连接的记录器。
     * @param Logger|string|array $value 要使用的记录器。
     * 这可以是记录器实例，也可以是用于使用 [[Yii::createObject()]] 创建一个的配置。
     */
    public function setLogger($value)
    {
        if (is_string($value) || is_array($value)) {
            $value = Yii::createObject($value);
        }
        $this->_logger = $value;
        $this->_logger->dispatcher = $this;
    }

    /**
     * @return int 每个消息应记录多少应用程序调用堆栈。
     * 此方法返回 [[Logger::traceLevel]] 的值。默认为 0。
     */
    public function getTraceLevel()
    {
        return $this->getLogger()->traceLevel;
    }

    /**
     * @param int $value 每个消息应记录多少应用程序调用堆栈。
     * 此方法将设置 [[Logger::traceLevel]] 的值。如果该值大于 0，表示将记录该调用堆栈的最大数量。
     * 请注意，只计算应用程序调用堆栈。
     * 默认为 0。
     */
    public function setTraceLevel($value)
    {
        $this->getLogger()->traceLevel = $value;
    }

    /**
     * @return int 记录多少消息后才将消息发送到目标。
     * 此方法返回 [[Logger::flushInterval]] 的值。
     */
    public function getFlushInterval()
    {
        return $this->getLogger()->flushInterval;
    }

    /**
     * @param int $value 记录多少消息后才将消息发送到目标。
     * 此方法设置 [[Logger::flushInterval]] 的值。
     * 默认为 1000，表示每 1000 条消息执行一次 [[Logger::flush()]] 方法。
     * 如果您不希望在应用程序终止之前发送消息，请将此属性设置为 0。
     * 此属性主要影响记录消息占用的内存量。
     * 值越小意味着内存越少，但由于更加频繁的执行 [[Logger::flush()]]，会增加执行时间。
     */
    public function setFlushInterval($value)
    {
        $this->getLogger()->flushInterval = $value;
    }

    /**
     * 将记录的消息调度到 [[targets]]。
     * @param array $messages 记录的消息
     * @param bool $final 是否在当前应用程序结束时调用此方法
     */
    public function dispatch($messages, $final)
    {
        $targetErrors = [];
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                try {
                    $target->collect($messages, $final);
                } catch (\Exception $e) {
                    $target->enabled = false;
                    $targetErrors[] = [
                        'Unable to send log via ' . get_class($target) . ': ' . ErrorHandler::convertExceptionToVerboseString($e),
                        Logger::LEVEL_WARNING,
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
}
