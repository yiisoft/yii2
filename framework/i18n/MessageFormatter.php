<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * MessageFormatter 允许通过 [ICU message format](http://userguide.icu-project.org/formatparse/messages) 格式化消息。
 *
 * 此类增强了 PHP intl 扩展提供的消息格式化程序类。
 *
 * 提供以下增强功能：
 *
 * - 它接受命名参数和混合的数值参数和命名参数。
 * - 当提供的参数数量不足时，不会发出错误。
 *   相反，占位符不会被替换。
 * - 修复 PHP 5.5 奇怪的占位符替换，以防根本没有提供任何参数 (https://bugs.php.net/bug.php?id=65920)。
 * - 如果未安装 PHP intl 扩展，则对消息格式化提供有限支持。
 *   但是，如果要使用 MessageFormatter 功能，
 *   强烈建议您安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
 *
 *   回退实现仅支持以下消息格式：
 *   - 英语的复数格式（'one' 和 'other' 选择器）
 *   - 选择格式
 *   - 简单的参数
 *   - 整数参数
 *
 *   回退实现不支持 ['apostrophe-friendly' syntax](https://secure.php.net/manual/en/messageformatter.formatmessage.php)。
 *   使用回退实现的消息也不一定与 PHP intl MessageFormatter 兼容，
 *   因此如果能够安装 intl 扩展，则不要依赖回退。
 *
 * @property string $errorCode 最后一个错误的代码。 此属性是只读的。
 * @property string $errorMessage 最后一个错误的描述。 此属性是只读的。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MessageFormatter extends Component
{
    private $_errorCode = 0;
    private $_errorMessage = '';


    /**
     * 获得最后一个操作的错误代码。
     * @link https://secure.php.net/manual/en/messageformatter.geterrorcode.php
     * @return string 最后一个错误的代码。
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * 获得最后一个操作的错误信息。
     * @link https://secure.php.net/manual/en/messageformatter.geterrormessage.php
     * @return string 最后一个错误的描述。
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * 通过 [ICU message format](http://userguide.icu-project.org/formatparse/messages) 格式化一个消息。
     *
     * 它使用 PHP intl 扩展的 [MessageFormatter](https://secure.php.net/manual/en/class.messageformatter.php)
     * 并解决了一些问题。
     * 如果未安装 PHP intl，将使用支持 ICU 消息格式子集的回退。
     *
     * @param string $pattern 要插入参数的模式字符串。
     * @param array $params 要插入到格式字符串中的键值对数组。
     * @param string $language 用于格式化与区域设置相关的部件的区域设置
     * @return string|false 格式化的模式字符串，如果发生错误，则为 `false`
     */
    public function format($pattern, $params, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if ($params === []) {
            return $pattern;
        }

        if (!class_exists('MessageFormatter', false)) {
            return $this->fallbackFormat($pattern, $params, $language);
        }

        // replace named arguments (https://github.com/yiisoft/yii2/issues/9678)
        $newParams = [];
        $pattern = $this->replaceNamedArguments($pattern, $params, $newParams);
        $params = $newParams;

        try {
            $formatter = new \MessageFormatter($language, $pattern);

            if ($formatter === null) {
                // formatter may be null in PHP 5.x
                $this->_errorCode = intl_get_error_code();
                $this->_errorMessage = 'Message pattern is invalid: ' . intl_get_error_message();
                return false;
            }
        } catch (\IntlException $e) {
            // IntlException is thrown since PHP 7
            $this->_errorCode = $e->getCode();
            $this->_errorMessage = 'Message pattern is invalid: ' . $e->getMessage();
            return false;
        } catch (\Exception $e) {
            // Exception is thrown by HHVM
            $this->_errorCode = $e->getCode();
            $this->_errorMessage = 'Message pattern is invalid: ' . $e->getMessage();
            return false;
        }

        $result = $formatter->format($params);

        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();
            return false;
        }

        return $result;
    }

    /**
     * 根据 [ICU message format](http://userguide.icu-project.org/formatparse/messages) 模式解析输入字符串。
     *
     * 它使用 PHP intl 扩展的 [MessageFormatter::parse()](https://secure.php.net/manual/en/messageformatter.parsemessage.php)
     * 并添加对命名参数的支持。
     * 使用此方法需要安装 PHP intl 扩展。
     *
     * @param string $pattern 用于解析消息的模式。
     * @param string $message 符合模式的要解析的消息。
     * @param string $language 用于格式化与区域设置相关的部件的区域设置
     * @return array|bool 包含提取的项的数组，出错时为 `false`。
     * @throws \yii\base\NotSupportedException 如果 PHP intl 扩展未安装。
     */
    public function parse($pattern, $message, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if (!class_exists('MessageFormatter', false)) {
            throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
        }

        // replace named arguments
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        $map = [];
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                $param = trim($token[0]);
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                }
                $token[0] = $map[$param];
                $tokens[$i] = '{' . implode(',', $token) . '}';
            }
        }
        $pattern = implode('', $tokens);
        $map = array_flip($map);

        $formatter = new \MessageFormatter($language, $pattern);
        if ($formatter === null) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        $result = $formatter->parse($message);
        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();

            return false;
        }

        $values = [];
        foreach ($result as $key => $value) {
            $values[$map[$key]] = $value;
        }

        return $values;
    }

    /**
     * 用数字占位符替换为命名占位符并引用未使用的占位符。
     *
     * @param string $pattern 用于替换东西的模式字符串。
     * @param array $givenParams 要插入格式字符串的值数组。
     * @param array $resultingParams 修改过的参数数组。
     * @param array $map
     * @return string 替换了占位符的模式字符串。
     */
    private function replaceNamedArguments($pattern, $givenParams, &$resultingParams = [], &$map = [])
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            return false;
        }
        foreach ($tokens as $i => $token) {
            if (!is_array($token)) {
                continue;
            }
            $param = trim($token[0]);
            if (array_key_exists($param, $givenParams)) {
                // if param is given, replace it with a number
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                    // make sure only used params are passed to format method
                    $resultingParams[$map[$param]] = $givenParams[$param];
                }
                $token[0] = $map[$param];
                $quote = '';
            } else {
                // quote unused token
                $quote = "'";
            }
            $type = isset($token[1]) ? trim($token[1]) : 'none';
            // replace plural and select format recursively
            if ($type === 'plural' || $type === 'select') {
                if (!isset($token[2])) {
                    return false;
                }
                if (($subtokens = self::tokenizePattern($token[2])) === false) {
                    return false;
                }
                $c = count($subtokens);
                for ($k = 0; $k + 1 < $c; $k++) {
                    if (is_array($subtokens[$k]) || !is_array($subtokens[++$k])) {
                        return false;
                    }
                    $subpattern = $this->replaceNamedArguments(implode(',', $subtokens[$k]), $givenParams, $resultingParams, $map);
                    $subtokens[$k] = $quote . '{' . $quote . $subpattern . $quote . '}' . $quote;
                }
                $token[2] = implode('', $subtokens);
            }
            $tokens[$i] = $quote . '{' . $quote . implode(',', $token) . $quote . '}' . $quote;
        }

        return implode('', $tokens);
    }

    /**
     * MessageFormatter::formatMessage 的回退实现。
     * @param string $pattern 要插入内容的模式字符串。
     * @param array $args 要插入格式字符串的值数组
     * @param string $locale 用于格式化与区域设置相关的部件的区域设置
     * @return false|string 格式化的模式字符串，如果发生错误，则为 `false`
     */
    protected function fallbackFormat($pattern, $args, $locale)
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                if (($tokens[$i] = $this->parseToken($token, $args, $locale)) === false) {
                    $this->_errorCode = -1;
                    $this->_errorMessage = 'Message pattern is invalid.';

                    return false;
                }
            }
        }

        return implode('', $tokens);
    }

    /**
     * 通过将正常文本与可替换模式分开来对模式进行标记。
     * @param string $pattern 要标记化的模式
     * @return array|bool token 数组或失败时为假
     */
    private static function tokenizePattern($pattern)
    {
        $charset = Yii::$app ? Yii::$app->charset : 'UTF-8';
        $depth = 1;
        if (($start = $pos = mb_strpos($pattern, '{', 0, $charset)) === false) {
            return [$pattern];
        }
        $tokens = [mb_substr($pattern, 0, $pos, $charset)];
        while (true) {
            $open = mb_strpos($pattern, '{', $pos + 1, $charset);
            $close = mb_strpos($pattern, '}', $pos + 1, $charset);
            if ($open === false && $close === false) {
                break;
            }
            if ($open === false) {
                $open = mb_strlen($pattern, $charset);
            }
            if ($close > $open) {
                $depth++;
                $pos = $open;
            } else {
                $depth--;
                $pos = $close;
            }
            if ($depth === 0) {
                $tokens[] = explode(',', mb_substr($pattern, $start + 1, $pos - $start - 1, $charset), 3);
                $start = $pos + 1;
                $tokens[] = mb_substr($pattern, $start, $open - $start, $charset);
                $start = $open;
            }

            if ($depth !== 0 && ($open === false || $close === false)) {
                break;
            }
        }
        if ($depth !== 0) {
            return false;
        }

        return $tokens;
    }

    /**
     * 解析一个 token。
     * @param array $token 要解析的 token
     * @param array $args 要替换的参数
     * @param string $locale 用于格式化与区域设置相关的部件的区域设置
     * @return bool|string 解析的 token 或失败时假
     * @throws \yii\base\NotSupportedException 使用不受支持的格式时。
     */
    private function parseToken($token, $args, $locale)
    {
        // parsing pattern based on ICU grammar:
        // http://icu-project.org/apiref/icu4c/classMessageFormat.html#details
        $charset = Yii::$app ? Yii::$app->charset : 'UTF-8';
        $param = trim($token[0]);
        if (isset($args[$param])) {
            $arg = $args[$param];
        } else {
            return '{' . implode(',', $token) . '}';
        }
        $type = isset($token[1]) ? trim($token[1]) : 'none';
        switch ($type) {
            case 'date':
            case 'time':
            case 'spellout':
            case 'ordinal':
            case 'duration':
            case 'choice':
            case 'selectordinal':
                throw new NotSupportedException("Message format '$type' is not supported. You have to install PHP intl extension to use this feature.");
            case 'number':
                $format = isset($token[2]) ? trim($token[2]) : null;
                if (is_numeric($arg) && ($format === null || $format === 'integer')) {
                    $number = number_format($arg);
                    if ($format === null && ($pos = strpos($arg, '.')) !== false) {
                        // add decimals with unknown length
                        $number .= '.' . substr($arg, $pos + 1);
                    }

                    return $number;
                }
                throw new NotSupportedException("Message format 'number' is only supported for integer values. You have to install PHP intl extension to use this feature.");
            case 'none':
                return $arg;
            case 'select':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1SelectFormat.html
                selectStyle = (selector '{' message '}')+
                */
                if (!isset($token[2])) {
                    return false;
                }
                $select = self::tokenizePattern($token[2]);
                $c = count($select);
                $message = false;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($select[$i]) || !is_array($select[$i + 1])) {
                        return false;
                    }
                    $selector = trim($select[$i++]);
                    if ($message === false && $selector === 'other' || $selector == $arg) {
                        $message = implode(',', $select[$i]);
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
                break;
            case 'plural':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1PluralFormat.html
                pluralStyle = [offsetValue] (selector '{' message '}')+
                offsetValue = "offset:" number
                selector = explicitValue | keyword
                explicitValue = '=' number  // adjacent, no white space in between
                keyword = [^[[:Pattern_Syntax:][:Pattern_White_Space:]]]+
                message: see MessageFormat
                */
                if (!isset($token[2])) {
                    return false;
                }
                $plural = self::tokenizePattern($token[2]);
                $c = count($plural);
                $message = false;
                $offset = 0;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($plural[$i]) || !is_array($plural[$i + 1])) {
                        return false;
                    }
                    $selector = trim($plural[$i++]);

                    if ($i == 1 && strncmp($selector, 'offset:', 7) === 0) {
                        $offset = (int) trim(mb_substr($selector, 7, ($pos = mb_strpos(str_replace(["\n", "\r", "\t"], ' ', $selector), ' ', 7, $charset)) - 7, $charset));
                        $selector = trim(mb_substr($selector, $pos + 1, mb_strlen($selector, $charset), $charset));
                    }
                    if ($message === false && $selector === 'other' ||
                        $selector[0] === '=' && (int) mb_substr($selector, 1, mb_strlen($selector, $charset), $charset) === $arg ||
                        $selector === 'one' && $arg - $offset == 1
                    ) {
                        $message = implode(',', str_replace('#', $arg - $offset, $plural[$i]));
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
                break;
        }

        return false;
    }
}
