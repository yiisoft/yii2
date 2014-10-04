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
     * @return string the truncated string.
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null, $asHtml = false)
    {
        if ($asHtml){
            return self::truncateHtml($string, $length, $suffix, true, $encoding);
        }

        if (mb_strlen($string, $encoding ?: \Yii::$app->charset) > $length) {
            return trim(mb_substr($string, 0, $length, $encoding ?: \Yii::$app->charset)) . $suffix;
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
    public static function truncateWords($string, $count, $suffix = '...', $asHtml = false)
    {
        if ($asHtml) {
            return self::truncateHtml($string, $count, $suffix, false);
        }

        $words = preg_split('/(\s+)/u', trim($string), null, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        } else {
            return $string;
        }
    }

    /**
     * Truncates a html string of the number of words or exact
     *
     * @param string $string
     * @param int $count
     * @param string $suffix
     * @param bool $exact
     * @return string
     */
    private static function truncateHtml($string, $count, $suffix, $exact = false, $encoding = null)
    {
        // if the plain text is shorter than the maximum length, return the whole text
        if (mb_strlen(preg_replace('/<.*?>/', '', $string)) <= $count) {
            return $string;
        }
        // splits all html-tags to scanable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $string, $lines, PREG_SET_ORDER);
        $totalCount = mb_strlen($suffix);
        $openTags = [];
        $truncate = '';
        foreach ($lines as $lineMap) {
            // if there is any html-tag in this line, handle it and add it (uncounted) to the output
            if (!empty($lineMap[1])) {
                // if it's an "empty element" with or without xhtml-conform closing slash
                if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $lineMap[1])) {
                    // do nothing if tag is a closing tag
                } elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $lineMap[1], $tagMap)) {
                    // delete tag from $open_tags list
                    $pos = array_search($tagMap[1], $openTags);
                    if ($pos !== false) {
                        ArrayHelper::remove($openTags, $pos);
                    }
                    // if tag is an opening tag
                } elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $lineMap[1], $tagMap)) {
                    // add tag to the beginning of $open_tags list
                    array_unshift($openTags, strtolower($tagMap[1]));
                }
                // add html-tag to $truncate'd text
                $truncate .= $lineMap[1];
            }
            // calculate the length of the plain text part of the line; handle entities as one character
            $stringLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $lineMap[2]));

            if (($totalCount + $stringLength) > $count) {
                // the number of characters which are left
                $left = $count - $totalCount;
                $entitiesCount = 0;
                // search for html entities
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $lineMap[2], $entities, PREG_OFFSET_CAPTURE)) {
                    // calculate the real length of all entities in the legal range
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entitiesCount <= $left) {
                            $left--;
                            $entitiesCount += mb_strlen($entity[0]);
                        } else {
                            // no more characters left
                            break;
                        }
                    }
                }
                $truncate .= mb_substr($lineMap[2], 0, $left + $entitiesCount);
                // maximum lenght is reached, so get off the loop
                break;
            } else {
                $truncate .=  $lineMap[2];
                $totalCount += $stringLength;
            }
            // if the maximum length is reached, get off the loop
            if ($totalCount >= $count) {
                break;
            }
        }

        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $space = strrpos($truncate, ' ');
            if (isset($space)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $space);
            }
        }
        // add the defined ending to the text
        $truncate .= $suffix;
        // close all unclosed html-tags
        foreach ($openTags as $tag) {
            $truncate .= Html::endTag($tag);
        }
        if(is_null($encoding)){
            return $truncate;
        }
        return mb_convert_encoding($truncate,$encoding);
    }

}
