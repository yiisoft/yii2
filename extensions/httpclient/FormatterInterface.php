<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

/**
 * Interface FormatterInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface FormatterInterface
{
    /**
     * Formats given HTTP document.
     * @param DocumentInterface $httpDocument HTTP document instance.
     */
    public function format(DocumentInterface $httpDocument);
} 