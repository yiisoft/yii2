<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;


use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;
use yiiunit\TestCase;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder
 */
class ColumnSchemaBuilderTest extends TestCase
{
    /**
     * @param string $type
     * @param integer $length
     * @return ColumnSchemaBuilder
     */
    public function getColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }

    /**
     * @return array
     */
    public function unsignedProvider()
    {
        return [
            ['integer', Schema::TYPE_INTEGER, null, [
                ['unsigned'],
            ]],
            ['integer(10)', Schema::TYPE_INTEGER, 10, [
                ['unsigned'],
            ]],
        ];
    }

    /**
     * @dataProvider unsignedProvider
     */
    public function testUnsigned($expected, $type, $length, $calls)
    {
        $this->checkBuildString($expected, $type, $length, $calls);
    }

    /**
     * @param string $expected
     * @param string $type
     * @param integer $length
     * @param array $calls
     */
    public function checkBuildString($expected, $type, $length, $calls)
    {
        $builder = $this->getColumnSchemaBuilder($type, $length);
        foreach ($calls as $call) {
            $method = array_shift($call);
            call_user_func_array([$builder, $method], $call);
        }

        self::assertEquals($expected, $builder->__toString());
    }
}
