<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseStringHelper 为 [[StringHelper]] 提供了具体的实现。
 *
 * 不要使用 BaseStringHelper。使用 [[StringHelper]]。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseStringHelper
{
    /**
     * 返回给定字符串中的字节数。
     * 该方法使用 `mb_strlen()` 确保字符串被视为字节数组。
     * @param string $string 字符串的长度
     * @return int 给定字符串中的字节数。
     */
    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * 返回由起始和长度参数指定的字符串部分。
     * 该方法使用 `mb_strlen()` 确保字符串被视为字节数组。
     * @param string $string 输入字符串。必须是一个字符或更长。
     * @param int $start 起始位置
     * @param int $length 所需的部分长度。
     * 如果没有特殊指定或者为 `null` 时，则长度没有限制。也就是说输出字符串直到末尾结束。
     * @return string 提取的部分字符串，或在失败时为 FALSE 或为空字符串。
     * @see http://www.php.net/manual/en/function.substr.php
     */
    public static function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    /**
     * 返回指定的跟踪组件的路径。
     * 这个方法类似于 php 函数 `basename()`，
     * 除此之外它还同时将 \ 和 / 作为目录分隔符，独立于操作系统。
     * 该方法主要用于处理 php 命名空间 php。
     * 在处理实际文件路径时，php 的 `basename()` 应该可以很好地工作。
     * Note: 此方法不知道实际的文件系统路径，或路径组件诸如 ".."。
     *
     * @param string $path 一个路径字符串。
     * @param string $suffix 即使这个名称组件以后缀结尾也会将其删除。
     * @return string 给定追踪名称组件的路径。
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) === $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    /**
     * 返回父目录的路径。
     * 这个方法类似于 `dirname()`，
     * 除此之外它还同时将 \ 和 / 作为目录分隔符，独立于操作系统之外。
     *
     * @param string $path 一个路径字符串。
     * @return string 父目录的路径。
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function dirname($path)
    {
        $pos = mb_strrpos(str_replace('\\', '/', $path), '/');
        if ($pos !== false) {
            return mb_substr($path, 0, $pos);
        }

        return '';
    }

    /**
     * 将字符串截取为指定的字符数。
     *
     * @param string $string 要截取的字符串。
     * @param int $length 从原始字符串包含到截取字符串中的字符数。
     * @param string $suffix 附加到截取字符串的末尾。
     * @param string $encoding 要使用的字符集，默认为应用程序当前使用的字符集。
     * @param bool $asHtml 是否将被截取的字符串视为 HTML 并保留适当的 HTML 标记。
     * 这个参数在 2.0.1 版之后就可用了。
     * @return string 截取字符串。
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null, $asHtml = false)
    {
        if ($encoding === null) {
            $encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        }
        if ($asHtml) {
            return static::truncateHtml($string, $length, $suffix, $encoding);
        }

        if (mb_strlen($string, $encoding) > $length) {
            return rtrim(mb_substr($string, 0, $length, $encoding)) . $suffix;
        }

        return $string;
    }

    /**
     * 将字符串截取为指定的单词数。
     *
     * @param string $string 要截取的字符串。
     * @param int $count 从原始字符串中包含多少个单词到截取的字符串中。
     * @param string $suffix 附加到截取字符串的末尾。
     * @param bool $asHtml 是否将被截取的字符串视为 HTML 并保留适当的 HTML 标记。
     * 这个参数在 2.0.1 版之后就可用了。
     * @return string 截取的字符串。
     */
    public static function truncateWords($string, $count, $suffix = '...', $asHtml = false)
    {
        if ($asHtml) {
            return static::truncateHtml($string, $count, $suffix);
        }

        $words = preg_split('/(\s+)/u', trim($string), null, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        }

        return $string;
    }

    /**
     * 在保留 HTML 的同时截取字符串。
     *
     * @param string $string 要截取的字符串
     * @param int $count
     * @param string $suffix 将指定字符串附加到截取的字符串末尾。
     * @param string|bool $encoding
     * @return string
     * @since 2.0.1
     */
    protected static function truncateHtml($string, $count, $suffix, $encoding = false)
    {
        $config = \HTMLPurifier_Config::create(null);
        if (Yii::$app !== null) {
            $config->set('Cache.SerializerPath', Yii::$app->getRuntimePath());
        }
        $lexer = \HTMLPurifier_Lexer::create($config);
        $tokens = $lexer->tokenizeHTML($string, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $depth = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens[$depth] = $token->name;
                $truncated[] = $token;
                ++$depth;
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                if (false === $encoding) {
                    preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['', ''];
                    $token->data = $prefixSpace[1] . self::truncateWords(ltrim($token->data), $count - $totalCount, '');
                    $currentCount = self::countWords($token->data);
                } else {
                    $token->data = self::truncate($token->data, $count - $totalCount, '', $encoding);
                    $currentCount = mb_strlen($token->data, $encoding);
                }
                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if ($token->name === $openTokens[$depth - 1]) {
                    --$depth;
                    unset($openTokens[$depth]);
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if ($totalCount >= $count) {
                if (0 < count($openTokens)) {
                    krsort($openTokens);
                    foreach ($openTokens as $name) {
                        $truncated[] = new \HTMLPurifier_Token_End($name);
                    }
                }
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);
        return $generator->generateFromTokens($truncated) . ($totalCount >= $count ? $suffix : '');
    }

    /**
     * 检查给定字符串是否以指定的子字符串开始。
     * 二进制和多字节安全。
     *
     * @param string $string 输入字符串
     * @param string $with 部分用于在 $string 中搜索
     * @param bool $caseSensitive 大小写敏感的搜索。默认是 true。当启用区分大小写时，$with 必须与字符串的开头完全匹配，才能获得一个真正的值。
     * @return bool 如果第一个输入以第二个输入开始，则返回 true，否则返回 false
     */
    public static function startsWith($string, $with, $caseSensitive = true)
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            return strncmp($string, $with, $bytes) === 0;

        }
        $encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        return mb_strtolower(mb_substr($string, 0, $bytes, '8bit'), $encoding) === mb_strtolower($with, $encoding);
    }

    /**
     * 检查给定字符串是否以指定的子字符串结束。
     * 二进制和多字节安全。
     *
     * @param string $string 要检查的输入字符串
     * @param string $with 部分用于搜索 $string 的内部。
     * @param bool $caseSensitive 大小写敏感的搜索。默认是 true。当启用区分大小写时，$with 必须与字符串的结尾完全匹配，才能获得一个真正的值。
     * @return bool 如果第一个输入以第二个输入结束，则返回 true，否则返回 false
     */
    public static function endsWith($string, $with, $caseSensitive = true)
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            // Warning check, see http://php.net/manual/en/function.substr-compare.php#refsect1-function.substr-compare-returnvalues
            if (static::byteLength($string) < $bytes) {
                return false;
            }

            return substr_compare($string, $with, -$bytes, $bytes) === 0;
        }

        $encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        return mb_strtolower(mb_substr($string, -$bytes, mb_strlen($string, '8bit'), '8bit'), $encoding) === mb_strtolower($with, $encoding);
    }

    /**
     * 将字符串分割为数组，可选地删除值并跳过空值。
     *
     * @param string $string 要分割的字符串。
     * @param string $delimiter 分隔符。默认设置是 ','。
     * @param mixed $trim 是否清除每个元素。可以是：
     *   - boolean - 正常清除；
     *   - string - 指定字符串进行清除。将作为第二个参数传递给 `trim()` 函数。
     *   - callable - 将调用每个值而不是 trim。给定指定的参数 - 值。
     * @param bool $skipEmpty 是否跳过分隔符之间的空字符串。默认是 false。
     * @return array
     * @since 2.0.4
     */
    public static function explode($string, $delimiter = ',', $trim = true, $skipEmpty = false)
    {
        $result = explode($delimiter, $string);
        if ($trim !== false) {
            if ($trim === true) {
                $trim = 'trim';
            } elseif (!is_callable($trim)) {
                $trim = function ($v) use ($trim) {
                    return trim($v, $trim);
                };
            }
            $result = array_map($trim, $result);
        }
        if ($skipEmpty) {
            // Wrapped with array_values to make array keys sequential after empty values removing
            $result = array_values(array_filter($result, function ($value) {
                return $value !== '';
            }));
        }

        return $result;
    }

    /**
     * 统计字符串中的单词。
     * @since 2.0.8
     *
     * @param string $string
     * @return int
     */
    public static function countWords($string)
    {
        return count(preg_split('/\s+/u', $string, null, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * 如果小数点，则返回数字值的字符串表示形式，
     * 并将逗号替换为点。
     * @param int|float|string $value
     * @return string
     * @since 2.0.11
     */
    public static function normalizeNumber($value)
    {
        $value = (string)$value;

        $localeInfo = localeconv();
        $decimalSeparator = isset($localeInfo['decimal_point']) ? $localeInfo['decimal_point'] : null;

        if ($decimalSeparator !== null && $decimalSeparator !== '.') {
            $value = str_replace($decimalSeparator, '.', $value);
        }

        return $value;
    }

    /**
     * 将字符串编码为 "Base 64 Encoding with URL and Filename Safe Alphabet" (RFC 4648)。
     *
     * > Note：Base 64 padding `=` 可能位于返回的字符串的末尾。
     * > `=` 对 URL 编码不透明。
     *
     * @see https://tools.ietf.org/html/rfc4648#page-7
     * @param string $input 要编码的字符串。
     * @return string 编码后的字符串。
     * @since 2.0.12
     */
    public static function base64UrlEncode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * 解码 "Base 64 Encoding with URL and Filename Safe Alphabet" (RFC 4648)。
     *
     * @see https://tools.ietf.org/html/rfc4648#page-7
     * @param string $input 编码字符串。
     * @return string 解码后的字符。
     * @since 2.0.12
     */
    public static function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * 安全地将 float 转换为字符串，与当前语言环境无关。
     *
     * 小数分隔符将始终为 `.`。
     * @param float|int $number 浮点数或整数。
     * @return string 数字的字符串表示形式。
     * @since 2.0.13
     */
    public static function floatToString($number)
    {
        // . and , are the only decimal separators known in ICU data,
        // so its safe to call str_replace here
        return str_replace(',', '.', (string) $number);
    }

    /**
     * 检查传递的字符串是否与给定的 shell 通配符模式匹配。
     * 此函数使用 PCRE 模拟 [[fnmatch()]]，这在某些环境中可能不可用。
     * @param string $pattern shell 通配符模式。
     * @param string $string 测试过的字符串。
     * @param array $options 匹配选项。有效选项包括：
     *
     * - caseSensitive：bool，模式是否应区分大小写。默认是 `true`。
     * - escape：bool，是否启用了反斜杠转义。默认是 `true`。
     * - filePath：bool，字符串中的斜杠是否仅与给定模式中的斜杠匹配。默认是 `false`。
     *
     * @return bool 字符串是否匹配模式。
     * @since 2.0.14
     */
    public static function matchWildcard($pattern, $string, $options = [])
    {
        if ($pattern === '*' && empty($options['filePath'])) {
            return true;
        }

        $replacements = [
            '\\\\\\\\' => '\\\\',
            '\\\\\\*' => '[*]',
            '\\\\\\?' => '[?]',
            '\*' => '.*',
            '\?' => '.',
            '\[\!' => '[^',
            '\[' => '[',
            '\]' => ']',
            '\-' => '-',
        ];

        if (isset($options['escape']) && !$options['escape']) {
            unset($replacements['\\\\\\\\']);
            unset($replacements['\\\\\\*']);
            unset($replacements['\\\\\\?']);
        }

        if (!empty($options['filePath'])) {
            $replacements['\*'] = '[^/\\\\]*';
            $replacements['\?'] = '[^/\\\\]';
        }

        $pattern = strtr(preg_quote($pattern, '#'), $replacements);
        $pattern = '#^' . $pattern . '$#us';

        if (isset($options['caseSensitive']) && !$options['caseSensitive']) {
            $pattern .= 'i';
        }

        return preg_match($pattern, $string) === 1;
    }

    /**
     * 这个方法提供了内置 PHP 函数 `ucfirst()` 的 unicode-safe 实现。
     *
     * @param string $string 提取的字符串
     * @param string $encoding 可选项，默认是 "UTF-8"
     * @return string
     * @see http://php.net/manual/en/function.ucfirst.php
     * @since 2.0.16
     */
    public static function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, null, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $rest;
    }

    /**
     * 这个方法提供了内置 PHP 函数 `ucwords()` 的 unicode-safe 实现。
     *
     * @param string $string 指定进行的字符串
     * @param string $encoding 可选项，默认是 "UTF-8"
     * @see http://php.net/manual/en/function.ucwords.php
     * @return string
     */
    public static function mb_ucwords($string, $encoding = 'UTF-8')
    {
        $words = preg_split("/\s/u", $string, -1, PREG_SPLIT_NO_EMPTY);

        $titelized = array_map(function ($word) use ($encoding) {
            return static::mb_ucfirst($word, $encoding);
        }, $words);

        return implode(' ', $titelized);
    }
}
