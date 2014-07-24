<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

/**
 * Interface HttpDocumentFormatterInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface HttpDocumentFormatterInterface
{
    public function format(HttpDocumentInterface $httpDocument);
} 