<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * I18N 提供与国际化（I18N）和本地化（L10N）相关的功能。
 *
 * 默认情况下，I18N 在 [[\yii\base\Application]] 中配置为应用程序组件。
 * 您可以通过 `Yii::$app->i18n` 访问该实例。
 *
 * @property MessageFormatter $messageFormatter 消息格式化程序，用于通过 ICU 消息格式格式化消息。
 * 请注意，此属性的类型在 getter 和 setter 中有所不同。
 * 详细信息请参见 [[getMessageFormatter()]] 和 [[setMessageFormatter()]]。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class I18N extends Component
{
    /**
     * @var array [[MessageSource]] 配置或对象的列表。
     * 数组键是消息类别模式，
     * 数组值是对应的 [[MessageSource]] 对象或用于创建 [[MessageSource]] 对象的配置。
     *
     * 消息类别模式可以在末尾包含通配符 `*`，以匹配具有相同前缀的多个类别。
     * 例如，`app/*` 匹配 `app/cat1` 和 `app/cat2`。
     *
     * 类别模式 `*` 将匹配与其他类别模式不匹配的所有类别。
     *
     * 如果希望在自己的命名空间中扩展注册自己的消息源，
     * 则可以动态修改此属性。
     *
     * 始终定义有类别 `yii` 和 `app`。
     * 前者指的是 Yii 核心框架代码中使用的消息类别，而后者指的是自定义应用程序代码的默认消息类别。
     * 默认情况下，这两个类别都使用 [[PhpMessageSource]]，
     * 相应的消息文件分别存储在 `@yii/messages` 和 `@app/messages` 下。
     *
     * 您可以重写两个类别的配置。
     */
    public $translations;


    /**
     * 通过配置默认消息类别来初始化组件。
     */
    public function init()
    {
        parent::init();
        if (!isset($this->translations['yii']) && !isset($this->translations['yii*'])) {
            $this->translations['yii'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@yii/messages',
            ];
        }

        if (!isset($this->translations['app']) && !isset($this->translations['app*'])) {
            $this->translations['app'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => Yii::$app->sourceLanguage,
                'basePath' => '@app/messages',
            ];
        }
    }

    /**
     * 翻译消息到指定的语言。
     *
     * 翻译后，如果消息包含 ICU 消息格式且 `$params` 不为空，
     * 则使用 [[MessageFormatter]] 格式化消息。
     *
     * @param string $category 消息类别。
     * @param string $message 要翻译的信息。
     * @param array $params 将用于替换消息中相应占位符的参数。
     * @param string $language 语言代码（例如 `en-US`，`en`）。
     * @return string 已经翻译和格式化的消息。
     */
    public function translate($category, $message, $params, $language)
    {
        $messageSource = $this->getMessageSource($category);
        $translation = $messageSource->translate($category, $message, $language);
        if ($translation === false) {
            return $this->format($message, $params, $messageSource->sourceLanguage);
        }

        return $this->format($translation, $params, $language);
    }

    /**
     * 使用 [[MessageFormatter]] 格式化消息。
     *
     * @param string $message 要格式化的消息。
     * @param array $params 将用于替换消息中相应占位符的参数。
     * @param string $language 语言代码（例如 `en-US`，`en`）。
     * @return string 格式化的消息。
     */
    public function format($message, $params, $language)
    {
        $params = (array) $params;
        if ($params === []) {
            return $message;
        }

        if (preg_match('~{\s*[\w.]+\s*,~u', $message)) {
            $formatter = $this->getMessageFormatter();
            $result = $formatter->format($message, $params, $language);
            if ($result === false) {
                $errorMessage = $formatter->getErrorMessage();
                Yii::warning("Formatting message for language '$language' failed with error: $errorMessage. The message being formatted was: $message.", __METHOD__);

                return $message;
            }

            return $result;
        }

        $p = [];
        foreach ($params as $name => $value) {
            $p['{' . $name . '}'] = $value;
        }

        return strtr($message, $p);
    }

    /**
     * @var string|array|MessageFormatter
     */
    private $_messageFormatter;

    /**
     * 返回消息格式化程序实例。
     * @return MessageFormatter 消息格式化程序实例，用于通过 ICU 消息格式格式化消息。
     */
    public function getMessageFormatter()
    {
        if ($this->_messageFormatter === null) {
            $this->_messageFormatter = new MessageFormatter();
        } elseif (is_array($this->_messageFormatter) || is_string($this->_messageFormatter)) {
            $this->_messageFormatter = Yii::createObject($this->_messageFormatter);
        }

        return $this->_messageFormatter;
    }

    /**
     * @param string|array|MessageFormatter $value 消息格式化程序，用于通过 ICU 消息格式格式化消息。
     * 可以配置为数组或字符串，
     * 它们将被赋予 [[Yii::createObject]] 以创建实例或 [[MessageFormatter]] 实例。
     */
    public function setMessageFormatter($value)
    {
        $this->_messageFormatter = $value;
    }

    /**
     * 返回给定类别的消息源。
     * @param string $category 类别名称。
     * @return MessageSource 给定类别的消息源。
     * @throws InvalidConfigException 如果没有可用于指定类别的消息源。
     */
    public function getMessageSource($category)
    {
        if (isset($this->translations[$category])) {
            $source = $this->translations[$category];
            if ($source instanceof MessageSource) {
                return $source;
            }

            return $this->translations[$category] = Yii::createObject($source);
        }
        // try wildcard matching
        foreach ($this->translations as $pattern => $source) {
            if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                if ($source instanceof MessageSource) {
                    return $source;
                }

                return $this->translations[$category] = $this->translations[$pattern] = Yii::createObject($source);
            }
        }

        // match '*' in the last
        if (isset($this->translations['*'])) {
            $source = $this->translations['*'];
            if ($source instanceof MessageSource) {
                return $source;
            }

            return $this->translations[$category] = $this->translations['*'] = Yii::createObject($source);
        }

        throw new InvalidConfigException("Unable to locate message source for category '$category'.");
    }
}
