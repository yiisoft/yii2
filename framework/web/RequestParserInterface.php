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
     * Parses a given request instance determining its body parameters.
     * This method MUST return the array of body parameters detected from [[$request]] data.
     * However, this method MAY adjust the given [[Request]] instance directly for extra configuration.
     * @param Request $request the HTTP request instance to be parsed.
     * @return array parameters parsed from the request body.
     * @throws BadRequestHttpException in case request body format is invalid.
     */
    public function parse($request);
}
