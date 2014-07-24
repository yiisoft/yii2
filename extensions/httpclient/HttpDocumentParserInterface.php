<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

/**
 * Interface HttpDocumentParserInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface HttpDocumentParserInterface
{
    public function parse(HttpDocumentInterface $httpDocument);
} 