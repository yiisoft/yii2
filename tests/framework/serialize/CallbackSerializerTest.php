<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\serialize;

use yii\serialize\CallbackSerializer;

/**
 * @group serialize
 */
class CallbackSerializerTest extends SerializerTest
{
    /**
     * {@inheritdoc}
     */
    protected function createSerializer()
    {
        return new CallbackSerializer([
            'serialize' => 'serialize',
            'unserialize' => 'unserialize',
        ]);
    }
}