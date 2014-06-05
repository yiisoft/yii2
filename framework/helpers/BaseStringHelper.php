<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

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
     * @param integer $length the desired portion length
     * @return string the extracted part of string, or FALSE on failure or an empty string.
     * @see http://www.php.net/manual/en/function.substr.php
     */
    public static function byteSubstr($string, $start, $length)
    {
        return mb_substr($string, $start, $length, '8bit');
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
     * @return string the truncated string.
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null)
    {
        if (mb_strlen($string, $encoding ? : \Yii::$app->charset) > $length) {
            return trim(mb_substr($string, 0, $length, $encoding ? : \Yii::$app->charset)) . $suffix;
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
     * @return string the truncated string.
     */
    public static function truncateWords($string, $count, $suffix = '...')
    {
        $words = preg_split('/(\s+)/u', trim($string), null, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        } else {
            return $string;
        }
    }

    /**
     * Parse a uri to components
     *
     * This function can be used to parse a given uri string to the scheme, authority, path, query and fragment
     * components. The string is split according to rfc 2396 Appendix B.
     * This function should not be used to validate a given uri-string as it tries to get every component possible.
     * One may use this function to parse URIs that are not parsable by PHPs own parse_url (E.g. 'assets:///path').
     * Note: The authority component equals parse_urls host result. 'Authority' is however used in URI contexts in the
     * already named rfc.
     * @param string $uri The uri to be parsed
     * @param bool $appendMatches Determent if the original result from the regular expression used to parse the uri string
     * should be returned in a 'matches' key of the result array.
     * @return array The result array. This array **may** contain the following keys: ´scheme´, ´authority´, ´path´, ´query´,
     * and ´fragment´. None of this keys is guaranteed to be present.
     */
    public static function parseUri($uri, $appendMatches = false)
    {
        static $regex, $components;
        if ($regex === null) {
            // rfc 2396 Page 28 Appendix B
            $regex = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';
            $components = ['scheme' => 2, 'authority' => 4, 'path' => 5, 'query' => 7, 'fragment' => 9];
        }

        $matches = [];
        preg_match($regex, $uri, $matches);
        $result = [];

        foreach ($components as $componentName => $componentPosition) {
            if (!empty($matches[$componentPosition])) {
                $result[$componentName] = $matches[$componentPosition];
            }
        }

        if ($appendMatches) {
            $result['matches'] = $matches;
        }

        return $result;
    }
}
