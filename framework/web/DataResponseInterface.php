<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * DataResponseInterface specifies the interface needed to format a response before it is sent out.
 *
 * @author Edin Omeragic <edin.omeragic@gmail.com>
 * @since 2.0
 */
interface DataResponseInterface
{
    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     */
    public function format($response);
}
