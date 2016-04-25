<?php

namespace yii\helpers;

/**
 * Mock of Console class
 * @package yii\helpers
 */
class Console extends BaseConsole
{
    /**
     * Prints a string to output buffer instead of STOUT
     *
     * @param string $string the string to print
     * @return integer|boolean Number of bytes printed or false on error
     */
    public static function stdout($string)
    {
        echo $string;
        return mb_strlen($string);
    }

    /**
     * Prints a string to output buffer instead of STDERR
     *
     * @param string $string the string to print
     * @return integer|boolean Number of bytes printed or false on error
     */
    public static function stderr($string)
    {
        echo $string;
        return mb_strlen($string);
    }
}
