<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseStringHelper provides concrete implementation for [[StringHelper]].
 *
 * Do not use BaseStringHelper. Use [[StringHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseStringHelper
{
    /**
     * Returns the number of bytes in the given string.
     * This method ensures the string is treated as a byte array by using `mb_strlen()`.
     * @param string $string the string being measured for length
     * @return integer the number of bytes in the given string.
     */
    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     * This method ensures the string is treated as a byte array by using `mb_substr()`.
     * @param string $string the input string. Must be one character or longer.
     * @param integer $start the starting position
     * @param integer $length the desired portion length. If not specified or `null`, there will be
     * no limit on length i.e. the output will be until the end of the string.
     * @return string the extracted part of string, or FALSE on failure or an empty string.
     * @see http://www.php.net/manual/en/function.substr.php
     */
    public static function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    /**
     * Returns the trailing name component of a path.
     * This method is similar to the php function `basename()` except that it will
     * treat both \ and / as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) == $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    /**
     * Returns parent directory's path.
     * This method is similar to `dirname()` except that it will treat
     * both \ and / as directory separators, independent of the operating system.
     *
     * @param string $path A path string.
     * @return string the parent directory's path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function dirname($path)
    {
        $pos = mb_strrpos(str_replace('\\', '/', $path), '/');
        if ($pos !== false) {
            return mb_substr($path, 0, $pos);
        } else {
            return '';
        }
    }
    
    /**
     * Truncates a string to the number of characters specified.
     *
     * @param string $string The string to truncate.
     * @param integer $length How many characters from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @param boolean $asHtml Whether to treat the string being truncated as HTML and preserve proper HTML tags.
     * This parameter is available since version 2.0.1.
     * @return string the truncated string.
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null, $asHtml = false)
    {
        if ($asHtml) {
            return self::truncateHtml($string, $length, $suffix, $encoding ?: Yii::$app->charset);
        }
        
        if (mb_strlen($string, $encoding ?: Yii::$app->charset) > $length) {
            return trim(mb_substr($string, 0, $length, $encoding ?: Yii::$app->charset)) . $suffix;
        } else {
            return $string;
        }
    }
    
    /**
     * Truncates a string to the number of words specified.
     *
     * @param string $string The string to truncate.
     * @param integer $count How many words from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @param boolean $asHtml Whether to treat the string being truncated as HTML and preserve proper HTML tags.
     * This parameter is available since version 2.0.1.
     * @return string the truncated string.
     */
    public static function truncateWords($string, $count, $suffix = '...', $asHtml = false)
    {
        if ($asHtml) {
            return self::truncateHtml($string, $count, $suffix);
        }

        $words = preg_split('/(\s+)/u', trim($string), null, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        } else {
            return $string;
        }
    }
    
    /**
     * Truncate a string while preserving the HTML.
     * 
     * @param string $string The string to truncate
     * @param integer $count
     * @param string $suffix String to append to the end of the truncated string.
     * @param string|boolean $encoding
     * @return string
     * @since 2.0.1
     */
    protected static function truncateHtml($string, $count, $suffix, $encoding = false)
    {
        $config = \HTMLPurifier_Config::create(null);
        $lexer = \HTMLPurifier_Lexer::create($config);
        $tokens = $lexer->tokenizeHTML($string, $config, null);
        $openTokens = 0;
        $totalCount = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens++;
                $truncated[] = $token;
            } else if ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                if (false === $encoding) {
                    $token->data = self::truncateWords($token->data, $count - $totalCount, '');
                    $currentCount = str_word_count($token->data);
                } else {
                    $token->data = self::truncate($token->data, $count - $totalCount, '', $encoding) . ' ';
                    $currentCount = mb_strlen($token->data, $encoding);
                }
                $totalCount += $currentCount;
                if (1 === $currentCount) {
                    $token->data = ' ' . $token->data;
                }
                $truncated[] = $token;
            } else if ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                $openTokens--;
                $truncated[] = $token;
            } else if ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if (0 === $openTokens && $totalCount >= $count) {
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);
        return $generator->generateFromTokens($truncated) . $suffix;
    }

    /**
     * Check if given string starts with specified substring.
     * Binary and multibyte safe.
     *
     * @param string $string Input string
     * @param string $with Part to search
     * @param boolean $caseSensitive Case sensitive search. Default is true.
     * @return boolean Returns true if first input starts with second input, false otherwise
     */
    public static function startsWith($string, $with, $caseSensitive = true)
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            return strncmp($string, $with, $bytes) === 0;
        } else {
            return mb_strtolower(mb_substr($string, 0, $bytes, '8bit'), Yii::$app->charset) === mb_strtolower($with, Yii::$app->charset);
        }
    }

    /**
     * Check if given string ends with specified substring.
     * Binary and multibyte safe.
     *
     * @param string $string
     * @param string $with
     * @param boolean $caseSensitive Case sensitive search. Default is true.
     * @return boolean Returns true if first input ends with second input, false otherwise
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
        } else {
            return mb_strtolower(mb_substr($string, -$bytes, null, '8bit'), Yii::$app->charset) === mb_strtolower($with, Yii::$app->charset);
        }
    }
}
