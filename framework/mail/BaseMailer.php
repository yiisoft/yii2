<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\web\View;

/**
 * BaseMailer 充当基类，实现 [[MailerInterface]] 所需的基本功能。
 *
 * 具体的子类应该专注于实现 [[sendMessage()]] 方法。
 *
 * @see BaseMessage
 *
 * 了解更多关于 BaseMailer 的细节和用法，请参阅 [guide article on mailing](guide:tutorial-mailing)。
 *
 * @property View $view View 实例。请注意，此属性在 getter 和 setter 中有所不同。
 * 有关详细信息，请参阅　[[getView()]] 和 [[setView()]]。
 * @property string $viewPath 包含关于实现邮件消息的视图文件的目录默认为
 * '@app/mail'。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMailer extends Component implements MailerInterface, ViewContextInterface
{
    /**
     * @event MailEvent 发送前触发的事件。
     * 你可以将 [[MailEvent::isValid]] 设置为 false 以取消发送。
     */
    const EVENT_BEFORE_SEND = 'beforeSend';
    /**
     * @event MailEvent 发送后触发的事件。
     */
    const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @var string|bool HTML 布局视图名。这是用于渲染 HTML 邮件正文的布局。
     * 该属性可以使用以下值：
     *
     * - 相对视图名：[[viewPath]] 的相对视图文件，例如，'layouts/html'。
     * - [路径别名](guide:concept-aliases)：指定为路径别名的绝对视图文件路径，例如，'@app/mail/html'。
     * - 布尔值 false：禁用布局。
     */
    public $htmlLayout = 'layouts/html';
    /**
     * @var string|bool text 布局视图名。这是用于渲染 TEXT 邮件正文的布局。
     * 有关于此属性可以使用的值，请参阅 [[htmlLayout]]。
     */
    public $textLayout = 'layouts/text';
    /**
     * @var array 应由 [[createMessage()]] 或 [[compose()]] 应用于任何新创建的邮件实例的配置数组。
     * 可以配置 [[MessageInterface]] 定义任何有效的属性，
     * 例如 `from`，`to`，`subject`，`textBody`，`htmlBody`, 等等。
     *
     * 例如：
     *
     * ```php
     * [
     *     'charset' => 'UTF-8',
     *     'from' => 'noreply@mydomain.com',
     *     'bcc' => 'developer@mydomain.com',
     * ]
     * ```
     */
    public $messageConfig = [];
    /**
     * @var string [[createMessage()]] 创建的新消息实例的默认类名
     */
    public $messageClass = 'yii\mail\BaseMessage';
    /**
     * @var bool 是否将电子邮件保存为 [[fileTransportPath]] 下的文件，而不是将它们发送给实际的接收人。
     * 这通常在开发期间用于调试的时候使用。
     * @see fileTransportPath
     */
    public $useFileTransport = false;
    /**
     * @var string 当 [[useFileTransport]] 为 true 的时候保存的邮件目录。
     */
    public $fileTransportPath = '@runtime/mail';
    /**
     * @var callable 当 [[useFileTransport]] 为 true 的时候，由 [[send()]] 调用的 PHP 回调。
     * 回调应该返回一个文件名，用于保存电子邮件。
     * 如果没有设置，文件名将通过当前时间戳来生成。
     *
     * 回调的方法为：
     *
     * ```php
     * function ($mailer, $message)
     * ```
     */
    public $fileTransportCallback;

    /**
     * @var \yii\base\View|array view 实例或其数组的配置。
     */
    private $_view = [];
    /**
     * @var string 用于实现邮件消息的视图文件的目录。
     */
    private $_viewPath;


    /**
     * @param array|View $view view 实例或数组配置，
     * 用于渲染邮件正文。
     * @throws InvalidConfigException 使用无效的参数抛出的异常。
     */
    public function setView($view)
    {
        if (!is_array($view) && !is_object($view)) {
            throw new InvalidConfigException('"' . get_class($this) . '::view" should be either object or configuration array, "' . gettype($view) . '" given.');
        }
        $this->_view = $view;
    }

    /**
     * @return View 视图实例。
     */
    public function getView()
    {
        if (!is_object($this->_view)) {
            $this->_view = $this->createView($this->_view);
        }

        return $this->_view;
    }

    /**
     * 从给定的配置创建视图实例。
     * @param array $config 视图的配置。
     * @return View 视图的实例。
     */
    protected function createView(array $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = View::className();
        }

        return Yii::createObject($config);
    }

    private $_message;

    /**
     * 创建一个新的消息实例，并可选的通过视图渲染来组成主题内容。
     *
     * @param string|array|null $view 用于渲染邮件正文的视图。这可以是：
     *
     * - 字符串，表示用于渲染邮件的 HTML 正文视图名称或 [路径别名](guide:concept-aliases)。
     *   在这种情况下，文本主体将通过在 HTML 主体中应用 `strip_tags()` 来生成。
     * - 一个带有 'html' 和/或 'text' 元素的数组。'html' 元素指的是用于渲染 HTML 主体的视图名称或路径别名，
     *   而 'text' 元素是用于渲染正文的。例如，
     *   `['html' => 'contact-html', 'text' => 'contact-text']`。
     * - 空，表示是消息实例将在没有正文内容的情况下返回。
     *
     * 要渲染的视图可以使用以下格式之一指定：
     *
     * - 路径别名（例如 "@app/mail/contact"）：
     * - 位于 [[viewPath]] 下的相对视图名称（例如 "contact"）。
     *
     * @param array $params 将在视图文件中可用的参数（键值对）。
     * @return MessageInterface 消息实例。
     */
    public function compose($view = null, array $params = [])
    {
        $message = $this->createMessage();
        if ($view === null) {
            return $message;
        }

        if (!array_key_exists('message', $params)) {
            $params['message'] = $message;
        }

        $this->_message = $message;

        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $this->render($view['html'], $params, $this->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $this->render($view['text'], $params, $this->textLayout);
            }
        } else {
            $html = $this->render($view, $params, $this->htmlLayout);
        }


        $this->_message = null;

        if (isset($html)) {
            $message->setHtmlBody($html);
        }
        if (isset($text)) {
            $message->setTextBody($text);
        } elseif (isset($html)) {
            if (preg_match('~<body[^>]*>(.*?)</body>~is', $html, $match)) {
                $html = $match[1];
            }
            // remove style and script
            $html = preg_replace('~<((style|script))[^>]*>(.*?)</\1>~is', '', $html);
            // strip all HTML tags and decoded HTML entities
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, Yii::$app ? Yii::$app->charset : 'UTF-8');
            // improve whitespace
            $text = preg_replace("~^[ \t]+~m", '', trim($text));
            $text = preg_replace('~\R\R+~mu', "\n\n", $text);
            $message->setTextBody($text);
        }

        return $message;
    }

    /**
     * 创建一个新的消息实例。
     * 将使用 [[messageConfig]] 指定的配置初始化新创建的实例。
     * 如果配置没有指定 'class'，[[messageClass]] 将用作新消息
     * 实例的类。
     * @return MessageInterface 消息实例。
     */
    protected function createMessage()
    {
        $config = $this->messageConfig;
        if (!array_key_exists('class', $config)) {
            $config['class'] = $this->messageClass;
        }
        $config['mailer'] = $this;
        return Yii::createObject($config);
    }

    /**
     * 发送给定的电子邮件。
     * 此方法将记录关于正在发送的电子邮件的消息。
     * 如果 [[useFileTransport]] 为 true，它会将电子邮件保存为 [[fileTransportPath]] 下的文件。
     * 否则，将调用 [[sendMessage()]] 将电子邮件发送给收件人。
     * 子类应该用实际的电子邮件发送逻辑实现 [[sendMessage()]]。
     * @param MessageInterface $message 要发送的电子邮件实例
     * @return bool 消息是否成功的发送
     */
    public function send($message)
    {
        if (!$this->beforeSend($message)) {
            return false;
        }

        $address = $message->getTo();
        if (is_array($address)) {
            $address = implode(', ', array_keys($address));
        }
        Yii::info('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);

        if ($this->useFileTransport) {
            $isSuccessful = $this->saveMessage($message);
        } else {
            $isSuccessful = $this->sendMessage($message);
        }
        $this->afterSend($message, $isSuccessful);

        return $isSuccessful;
    }

    /**
     * 一次发送多条消息。
     *
     * 默认实现只需要多次调用 [[send()]]。
     * 子类可以重写此方法，
     * 实现更有效的方式发送多个消息。
     *
     * @param array $messages 应发送的电子邮件列表。
     * @return int 成功发送的邮件数。
     */
    public function sendMultiple(array $messages)
    {
        $successCount = 0;
        foreach ($messages as $message) {
            if ($this->send($message)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * 使用可选参数和布局渲染指定视图。
     * 视图将使用 [[view]] 组件渲染。
     * @param string $view 视图文件的视图名或 [路径别名](guide:concept-aliases)。
     * @param array $params 将在视图文件中使用的参数（键值对）。
     * @param string|bool $layout 布局视图名称或 [路径别名](guide:concept-aliases)。如果为 false，不会应用布局。
     * @return string 渲染的结果。
     */
    public function render($view, $params = [], $layout = false)
    {
        $output = $this->getView()->render($view, $params, $this);
        if ($layout !== false) {
            return $this->getView()->render($layout, ['content' => $output, 'message' => $this->_message], $this);
        }

        return $output;
    }

    /**
     * 发送指定消息。
     * 此方法应由具有实际电子邮件发送逻辑的子类实现。
     * @param MessageInterface $message 要发送的消息
     * @return bool 消息是否成功发送
     */
    abstract protected function sendMessage($message);

    /**
     * 将消息存为 [[fileTransportPath]] 下的文件。
     * @param MessageInterface $message
     * @return bool 消息是否成功发送
     */
    protected function saveMessage($message)
    {
        $path = Yii::getAlias($this->fileTransportPath);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if ($this->fileTransportCallback !== null) {
            $file = $path . '/' . call_user_func($this->fileTransportCallback, $this, $message);
        } else {
            $file = $path . '/' . $this->generateMessageFileName();
        }
        file_put_contents($file, $message->toString());

        return true;
    }

    /**
     * @return string 当 [[useFileTransport]] 为 true 时保存消息的文件名。
     */
    public function generateMessageFileName()
    {
        $time = microtime(true);

        return date('Ymd-His-', $time) . sprintf('%04d', (int) (($time - (int) $time) * 10000)) . '-' . sprintf('%04d', mt_rand(0, 10000)) . '.eml';
    }

    /**
     * @return string 包含关于实现邮件消息的视图文件的目录默认为
     * '@app/mail'。
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->setViewPath('@app/mail');
        }

        return $this->_viewPath;
    }

    /**
     * @param string $path 包含用于实现邮件消息的视图文件的目录
     * 可以指定为绝对路劲或 [路径别名](guide:concept-aliases)。
     */
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }

    /**
     * 在邮件发送之前调用此方法。
     * 你可以覆盖此方法以对邮件发送做最后的准备。
     * 如果重写此方法，请确保先调用父类方法。
     * @param MessageInterface $message
     * @return bool 是否继续发送邮件。
     */
    public function beforeSend($message)
    {
        $event = new MailEvent(['message' => $message]);
        $this->trigger(self::EVENT_BEFORE_SEND, $event);

        return $event->isValid;
    }

    /**
     * 发送邮件后立即调用此方法。
     * 你可以覆盖此方法以根据邮件发送状态执行一些后处理或记录。
     * 如果重写此方法，请确保先调用父类方法。
     * @param MessageInterface $message
     * @param bool $isSuccessful
     */
    public function afterSend($message, $isSuccessful)
    {
        $event = new MailEvent(['message' => $message, 'isSuccessful' => $isSuccessful]);
        $this->trigger(self::EVENT_AFTER_SEND, $event);
    }
}
