<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\serialize;

use yii\base\BaseObject;

/**
 * CallbackSerializer serializes data via custom PHP callback.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class CallbackSerializer extends BaseObject implements SerializerInterface
{
    /**
     * @var callable PHP callback, which should be used to serialize value.
     */
    public $serialize;
    /**
     * @var callable PHP callback, which should be used to unserialize value.
     */
    public $unserialize;


    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        return call_user_func($this->serialize, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($value)
    {
        return call_user_func($this->unserialize, $value);
    }
}