<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * Exception represents an exception that is caused by some Caching-related operations.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class Exception extends \yii\base\Exception implements \Psr\SimpleCache\CacheException
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Cache Exception';
    }
}