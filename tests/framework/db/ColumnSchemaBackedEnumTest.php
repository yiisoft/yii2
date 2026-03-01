<?php

namespace yiiunit\framework\db;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yiiunit\TestCase;

/**
 * @requires PHP >= 8.1
 */
class ColumnSchemaBackedEnumTest extends TestCase
{
    public function testCastToStringFromBackedEnum(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_STRING;
        $column->phpType = 'string';

        eval('enum StringBackedStatus: string { case Active = "active"; }');
        $result = $column->phpTypecast(\StringBackedStatus::Active);
        $this->assertSame('active', $result);
    }

    public function testCastToIntegerFromBackedEnum(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_INTEGER;
        $column->phpType = 'integer';

        eval('enum IntBackedStatus: int { case On = 1; }');
        $result = $column->phpTypecast(\IntBackedStatus::On);
        $this->assertSame(1, $result);
    }

    public function testCastToIntegerFromBackedEnumZero(): void
    {
        $column = new ColumnSchema();
        $column->type = Schema::TYPE_INTEGER;
        $column->phpType = 'integer';

        eval('enum IntBackedStatusZero: int { case Off = 0; }');
        $result = $column->phpTypecast(\IntBackedStatusZero::Off);
        $this->assertSame(0, $result);
    }
}
