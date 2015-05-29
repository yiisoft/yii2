<?php
namespace yiiunit\framework\db\pgsql;

use yiiunit\framework\db\DatabaseTestCase;
use yii\db\pgsql\ColumnSchema;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLEncoderTraitTest extends DatabaseTestCase
{
    public $driverName = 'pgsql';

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayIntEncode
     */
    public function testArrayIntEncode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals('{1,2,3}', $schema->arrayIntEncode([1, 2, 3]));
    }

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayIntDecode
     */
    public function testArrayIntDecode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals([1, 2, 3], $schema->arrayIntDecode('{1,2,3}'));
    }

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayTextEncode
     */
    public function testArrayTextEncode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals('{test 1,test2,test_3}', $schema->arrayTextEncode(['test 1', 'test2', 'test_3']));
    }

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayIntDecode
     */
    public function testArrayTextDecode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals(['test 1', 'test2', 'test_3'], $schema->arrayTextDecode('{test 1,test2,test_3}'));
    }

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayNumericEncode
     */
    public function arrayNumericEncode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals('{0.1,0.2,0.3}', $schema->arrayNumericEncode([1, 2, 3]));
    }

    /**
     * @covers \yii\db\pgsql\EncoderTrait::arrayNumericDecode
     */
    public function testArrayNumericDecode()
    {
        $schema = new ColumnSchema();
        $this->assertEquals([0.1, 0.2, 0.3], $schema->arrayNumericDecode('{0.1,0.2,0.3}'));
    }
}
