<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\serialize;

/**
 * SerializerInterface defines serializer interface.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
interface SerializerInterface
{
    /**
     * Serializes given value.
     * @param mixed $value value to be serialized
     * @return string serialized value.
     */
    public function serialize($value);

    /**
     * Restores value from its serialized representations
     * @param string $value serialized string.
     * @return mixed restored value
     */
    public function unserialize($value);
}