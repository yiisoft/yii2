<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Exception;

/**
 * HeadersAlreadySentException represents an exception caused by
 * any headers that were already sent before web response was sent.
 *
 * @author Dmitry Dorogin <dmirogin@ya.ru>
 * @since 2.0.14
 */
class HeadersAlreadySentException extends Exception
{
    /**
     * Create instance of exception depending on debug mode.
     *
     * @param string $file
     * @param int $line
     * @return HeadersAlreadySentException
     */
    public static function make($file, $line)
    {
        $message = YII_DEBUG ? "Headers already sent in {$file} on line {$line}." : 'Headers already sent.';
        return new static($message);
    }
}
