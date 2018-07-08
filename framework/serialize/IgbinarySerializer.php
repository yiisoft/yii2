<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\serialize;

use yii\base\BaseObject;

/**
 * IgbinarySerializer uses [Igbinary PHP extension](http://pecl.php.net/package/igbinary) for serialization.
 * Make sure you have 'igbinary' PHP extension install at your system before attempt to use this serializer.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class IgbinarySerializer extends BaseObject implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        return igbinary_serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($value)
    {
        return igbinary_unserialize($value);
    }
}