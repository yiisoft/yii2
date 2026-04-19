<?php

namespace yiiunit\framework\db;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yiiunit\framework\db\stubs\IntBackedStatus;
use yiiunit\framework\db\stubs\StringBackedStatus;
use yiiunit\TestCase;

class ColumnSchemaBackedEnumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/stubs/BackedEnumStubs.php';
    }

    public function testCastToStringFromBackedEnum(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_STRING;
        $column->phpType = 'string';

        $result = $column->phpTypecast(StringBackedStatus::Active);
        $this->assertSame('active', $result);
    }

    public function testCastToIntegerFromBackedEnum(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_INTEGER;
        $column->phpType = 'integer';

        $result = $column->phpTypecast(IntBackedStatus::On);
        $this->assertSame(1, $result);
    }

    public function testCastToIntegerFromBackedEnumZero(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_INTEGER;
        $column->phpType = 'integer';

        $result = $column->phpTypecast(IntBackedStatus::Off);
        $this->assertSame(0, $result);
    }
}
