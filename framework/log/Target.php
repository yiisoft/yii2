<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\web\Request;

/**
 * Target 是所有日志目标类的基类。
 *
 * 日志目标对象将根据 [[level]] 和 [[categories]] 属性过滤 [[Logger]] 记录的消息。
 * 它还可以将过滤后的消息导出到目标定义的特定目标，
 * 例如电子邮件，文件。
 *
 * 级别过滤器和类别过滤器是组合的，
 * 即，仅处理满足两个过滤条件的消息。
 * 此外，您可以指定 [[except]] 以排除某些类别的消息。
 *
 * @property bool $enabled 指示是否启用此日志目标。默认为 true。
 * 请注意，此属性的类型在 getter 和 setter 中有所不同。有关详细信息，请参见 [[getEnabled()]] 和 [[setEnabled()]]。
 * @property int $levels 需要记录的消息级别。默认为 0，表示所有可用级别。
 * 请注意，此属性的类型在 getter 和 setter 中有所不同。
 * 有关详细信息，请参见 [[getLevels()]] 和 [[setLevels()]]。
 *
 * 有关 Target 的更多详细信息和使用信息，请参阅 [guide article on logging & targets](guide:runtime-logging)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Target extends Component
{
    /**
     * @var array 需要记录的消息类别列表。
     * 默认为空，表示所有类别。
     * 您可以在类别末尾使用星号，以便可以使用该类别来匹配共享相同公共前缀的类别。
     * 例如，'yii\db\*' 将匹配以 'yii\db\' 开头的类别，例如 'yii\db\Connection'。
     */
    public $categories = [];
    /**
     * @var array 需要排除的消息类别列表。
     * 默认为空，表示没有需要排除的消息。
     * 如果此属性不为空，则此处列出的任何类别都将从 [[categories]] 中排除。
     * 您可以在类别末尾使用星号，以便该类别可用于匹配共享相同公共前缀的类别。
     * 例如，'yii\db\*' 将匹配以 'yii\db\' 开头的类别，例如 'yii\db\Connection'。
     * @see categories
     */
    public $except = [];
    /**
     * @var array 需要记录在消息中的PHP预定义变量的列表。
     * 请注意，必须可以通过 `$GLOBALS` 访问变量。否则将不会记录。
     *
     * 默认是 `['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']`。
     *
     * 从版本 2.0.9 开始，可以使用其他语法：
     * 每个元素都可以指定为以下之一：
     *
     * - `var` - 会记录 `var`。
     * - `var.key` - 只会记录 `var[key]`。
     * - `!var.key` - 会排除 `var[key]`。
     *
     * 请注意，如果您需要记录 $_SESSION，无论是否使用了会话，
     * 您都必须在请求开始时立即打开它。
     *
     * @see \yii\helpers\ArrayHelper::filter()
     */
    public $logVars = ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'];
    /**
     * @var callable 一个可调用的函数，它返回一个字符串，以便为每个导出的消息添加前缀。
     *
     * 如果未设置，将使用 [[getMessagePrefix()]]，该消息在消息前面加上上下文信息。
     * 例如用户 IP，用户 ID 和会话 ID。
     *
     * 可调用的函数应该是 `function ($message)`。
     */
    public $prefix;
    /**
     * @var int 累积多少条消息后才导出。默认值为 1000。
     * 请注意，应用程序终止时将始终会导出消息。
     * 如果将此属性设置为 0，则只会在应用程序终止之时导出消息。
     */
    public $exportInterval = 1000;
    /**
     * @var array 此日志目标从记录器中获得的消息。
     * 请参阅 [[Logger::messages]] 以获取有关消息结构的详细信息。
     */
    public $messages = [];
    /**
     * @var bool 是否以微秒记录时间。
     * 默认是 false。
     * @since 2.0.13
     */
    public $microtime = false;

    private $_levels = 0;
    private $_enabled = true;


    /**
     * 将日志 [[messages]] 导出到特定目标。
     * 子类必须实现此方法。
     */
    abstract public function export();

    /**
     * 处理给定的日志消息。
     * 此方法将使用 [[levels]] 和 [[categories]] 过滤给定的消息。
     * 如果需要，它还会将过滤结果导出到特定介质（例如电子邮件）。
     * @param array $messages 记录要处理的消息。
     * 有关每条消息的结构，请参见 [[Logger::messages]]。
     * @param bool $final 是否在当前应用程序结束时调用此方法。
     */
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            if (($context = $this->getContextMessage()) !== '') {
                $this->messages[] = [$context, Logger::LEVEL_INFO, 'application', YII_BEGIN_TIME];
            }
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    /**
     * 生成要记录的上下文信息。
     * 默认会存储用户信息，系统变量等。
     * @return string 上下文信息。如果是空字符串，则表示没有上下文信息。
     */
    protected function getContextMessage()
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        $result = [];
        foreach ($context as $key => $value) {
            $result[] = "\${$key} = " . VarDumper::dumpAsString($value);
        }

        return implode("\n\n", $result);
    }

    /**
     * @return int 需要记录的消息级别。
     * 默认为 0，表示所有可用级别。
     */
    public function getLevels()
    {
        return $this->_levels;
    }

    /**
     * 设置此目标需要记录的消息级别。
     *
     * 参数可以是消息级别名称的数组，
     * 也可以是表示消息级别值的整数。
     * 有效级别名称包括：'error'，'warning'，'info'，'trace' 和 'profile'；
     * 有效级别值包括：[[Logger::LEVEL_ERROR]]，[[Logger::LEVEL_WARNING]]，[[Logger::LEVEL_INFO]]，
     * [[Logger::LEVEL_TRACE]] 和 [[Logger::LEVEL_PROFILE]]。
     *
     * 举个例子，
     *
     * ```php
     * ['error', 'warning']
     * // 另一种表达方式是：
     * Logger::LEVEL_ERROR | Logger::LEVEL_WARNING
     * ```
     *
     * @param array|int $levels 需要记录的消息级别。
     * @throws InvalidConfigException 如果 $levels 值不正确。
     */
    public function setLevels($levels)
    {
        static $levelMap = [
            'error' => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'info' => Logger::LEVEL_INFO,
            'trace' => Logger::LEVEL_TRACE,
            'profile' => Logger::LEVEL_PROFILE,
        ];
        if (is_array($levels)) {
            $this->_levels = 0;
            foreach ($levels as $level) {
                if (isset($levelMap[$level])) {
                    $this->_levels |= $levelMap[$level];
                } else {
                    throw new InvalidConfigException("Unrecognized level: $level");
                }
            }
        } else {
            $bitmapValues = array_reduce($levelMap, function ($carry, $item) {
                return $carry | $item;
            });
            if (!($bitmapValues & $levels) && $levels !== 0) {
                throw new InvalidConfigException("Incorrect $levels value");
            }
            $this->_levels = $levels;
        }
    }

    /**
     * 根据类别和级别过滤给定的消息。
     * @param array $messages 要过滤的消息。
     * 消息结构遵循 [[Logger::messages]] 中的消息结构。
     * @param int $levels 要过滤的消息级别。
     * 值 0 表示允许所有级别。
     * @param array $categories 要过滤的消息类别。如果为空，则表示允许所有类别。
     * @param array $except 要排除的消息类别。如果为空，则表示允许所有类别。
     * @return array 过滤后的消息。
     */
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = [])
    {
        foreach ($messages as $i => $message) {
            if ($levels && !($levels & $message[1])) {
                unset($messages[$i]);
                continue;
            }

            $matched = empty($categories);
            foreach ($categories as $category) {
                if ($message[2] === $category || !empty($category) && substr_compare($category, '*', -1, 1) === 0 && strpos($message[2], rtrim($category, '*')) === 0) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($except as $category) {
                    $prefix = rtrim($category, '*');
                    if (($message[2] === $category || $prefix !== $category) && strpos($message[2], $prefix) === 0) {
                        $matched = false;
                        break;
                    }
                }
            }

            if (!$matched) {
                unset($messages[$i]);
            }
        }

        return $messages;
    }

    /**
     * 为便于显示将日志消息格式化为字符串。
     * @param array $message 要格式化的日志消息。
     * 消息结构遵循 [[Logger::messages]] 中的消息结构。
     * @return string 格式化后的消息
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return $this->getTime($timestamp) . " {$prefix}[$level][$category] $text"
            . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }

    /**
     * 返回要添加到给定消息前缀的字符串。
     * 如果配置了 [[prefix]]，它将返回回调的结果。
     * 默认将返回用户 IP，用户 ID 和会话 ID 作为前缀。
     * @param array $message 正在导出的消息。
     * 消息结构遵循 [[Logger::messages]] 中的消息结构。
     * @return string 前缀字符串
     */
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        if (Yii::$app === null) {
            return '';
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        return "[$ip][$userID][$sessionID]";
    }

    /**
     * 设置是否启用此日志目标。
     * @param bool|callable $value 一个布尔值或一个可调用的函数返回值。
     * 从 2.0.13 版本开始，可以使用函数返回值作为参数。
     *
     * 函数返回值可用于动态设置是否启用日志目标。
     * 例如，要仅在当前用户登录时启用日志，
     * 您可以按如下方式配置目标：
     *
     * ```php
     * 'enabled' => function() {
     *     return !Yii::$app->user->isGuest;
     * }
     * ```
     */
    public function setEnabled($value)
    {
        $this->_enabled = $value;
    }

    /**
     * 检查日志目标是否已启用。
     * @property bool 指示是否启用此日志目标。默认为 true。
     * @return bool 一个指示是否启用此日志目标的布尔值。
     */
    public function getEnabled()
    {
        if (is_callable($this->_enabled)) {
            return call_user_func($this->_enabled, $this);
        }

        return $this->_enabled;
    }

    /**
     * 返回格式化以后的消息时间戳，格式为：'Y-m-d H:i:s'。
     * 如果 [[microtime]] 配置为 true，则格式为 'Y-m-d H:i:s.u'。
     * @param float $timestamp
     * @return string
     * @since 2.0.13
     */
    protected function getTime($timestamp)
    {
        $parts = explode('.', sprintf('%F', $timestamp));

        return date('Y-m-d H:i:s', $parts[0]) . ($this->microtime ? ('.' . $parts[1]) : '');
    }
}
