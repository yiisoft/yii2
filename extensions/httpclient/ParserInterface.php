<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

/**
 * Interface ParserInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ParserInterface
{
    /**
     * Parses given HTTP document.
     * @param DocumentInterface $httpDocument HTTP document instance.
     * @return array parsed content data.
     */
    public function parse(DocumentInterface $httpDocument);
}