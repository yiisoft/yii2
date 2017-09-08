<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * Interface for classes that parse the raw request body into a parameters array.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface RequestParserInterface
{
    /**
     * Parses a given request instance.
     * @param Request $request the HTTP request instance to be parsed.
     * @return array parameters parsed from the request body.
     */
    public function parse($request);
}
