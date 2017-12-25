<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\serialize;

use yiiunit\TestCase;

/**
 * @group serialize
 */
abstract class SerializerTest extends TestCase
{
    /**
     * Creates serializer instance for the tests.
     * @return \yii\serialize\SerializerInterface
     */
    abstract protected function createSerializer();

    /**
     * Data provider for [[testSerialize()]]
     * @return array test data.
     */
    public function dataProviderSerialize()
    {
        return [
            ['some-string'],
            [345],
            [56.89],
            [['some' => 'array']],
        ];
    }

    /**
     * @dataProvider dataProviderSerialize
     *
     * @param mixed $value
     */
    public function testSerialize($value)
    {
        $serializer = $this->createSerializer();

        $serialized = $serializer->serialize($value);
        $this->assertTrue(is_string($serialized));

        $this->assertEquals($value, $serializer->unserialize($serialized));
    }
}