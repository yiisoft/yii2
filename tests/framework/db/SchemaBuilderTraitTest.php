<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\SchemaBuilderTrait;
use yiiunit\TestCase;

class SchemaBuilderTraitTest extends TestCase
{
    private function createBuilder(): SchemaBuilderTraitStub
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('createColumnSchemaBuilder')
            ->willReturnCallback(function ($type, $length = null) {
                $builder = new ColumnSchemaBuilder($type, $length);
                return $builder;
            });

        $db = $this->createMock(Connection::class);
        $db->method('getSchema')->willReturn($schema);
        $db->method('getDriverName')->willReturn('mysql');

        return new SchemaBuilderTraitStub($db);
    }

    /**
     * @dataProvider simpleTypeProvider
     */
    public function testSimpleTypeMethods(string $method, string $expectedType): void
    {
        $builder = $this->createBuilder();
        $column = $builder->$method();

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $this->assertStringContainsString($expectedType, (string) $column);
    }

    public static function simpleTypeProvider(): array
    {
        return [
            'primaryKey' => ['primaryKey', 'pk'],
            'bigPrimaryKey' => ['bigPrimaryKey', 'bigpk'],
            'text' => ['text', 'text'],
            'date' => ['date', 'date'],
            'boolean' => ['boolean', 'boolean'],
            'json' => ['json', 'json'],
        ];
    }

    /**
     * @dataProvider parameterizedTypeProvider
     */
    public function testParameterizedTypeMethods(string $method, int $length, string $expectedType): void
    {
        $builder = $this->createBuilder();
        $column = $builder->$method($length);

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $result = (string) $column;
        $this->assertStringContainsString($expectedType, $result);
        $this->assertStringContainsString((string) $length, $result);
    }

    public static function parameterizedTypeProvider(): array
    {
        return [
            'char' => ['char', 1, 'char'],
            'string' => ['string', 64, 'string'],
            'tinyInteger' => ['tinyInteger', 3, 'tinyint'],
            'smallInteger' => ['smallInteger', 5, 'smallint'],
            'integer' => ['integer', 11, 'integer'],
            'bigInteger' => ['bigInteger', 20, 'bigint'],
            'float' => ['float', 8, 'float'],
            'double' => ['double', 16, 'double'],
            'dateTime' => ['dateTime', 6, 'datetime'],
            'timestamp' => ['timestamp', 6, 'timestamp'],
            'time' => ['time', 6, 'time'],
            'binary' => ['binary', 255, 'binary'],
        ];
    }

    /**
     * @dataProvider nullLengthTypeProvider
     */
    public function testNullLengthMethods(string $method, string $expectedType): void
    {
        $builder = $this->createBuilder();
        $column = $builder->$method();

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $this->assertStringContainsString($expectedType, (string) $column);
    }

    public static function nullLengthTypeProvider(): array
    {
        return [
            'char' => ['char', 'char'],
            'string' => ['string', 'string'],
            'tinyInteger' => ['tinyInteger', 'tinyint'],
            'smallInteger' => ['smallInteger', 'smallint'],
            'integer' => ['integer', 'integer'],
            'bigInteger' => ['bigInteger', 'bigint'],
            'float' => ['float', 'float'],
            'double' => ['double', 'double'],
            'dateTime' => ['dateTime', 'datetime'],
            'timestamp' => ['timestamp', 'timestamp'],
            'time' => ['time', 'time'],
            'binary' => ['binary', 'binary'],
        ];
    }

    public function testDecimalWithPrecisionAndScale(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->decimal(10, 2);

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $result = (string) $column;
        $this->assertStringContainsString('decimal', $result);
        $this->assertStringContainsString('10', $result);
        $this->assertStringContainsString('2', $result);
    }

    public function testDecimalWithPrecisionOnly(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->decimal(10);

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $result = (string) $column;
        $this->assertStringContainsString('decimal', $result);
        $this->assertStringContainsString('10', $result);
    }

    public function testDecimalWithNoArgs(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->decimal();

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $this->assertStringContainsString('decimal', (string) $column);
    }

    public function testMoneyWithPrecisionAndScale(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->money(19, 4);

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $result = (string) $column;
        $this->assertStringContainsString('money', $result);
        $this->assertStringContainsString('19', $result);
        $this->assertStringContainsString('4', $result);
    }

    public function testMoneyWithPrecisionOnly(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->money(19);

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $result = (string) $column;
        $this->assertStringContainsString('money', $result);
        $this->assertStringContainsString('19', $result);
    }

    public function testMoneyWithNoArgs(): void
    {
        $builder = $this->createBuilder();
        $column = $builder->money();

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $column);
        $this->assertStringContainsString('money', (string) $column);
    }
}

class SchemaBuilderTraitStub
{
    use SchemaBuilderTrait;

    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    protected function getDb()
    {
        return $this->db;
    }
}
