<?php

namespace yiiunit\framework\db;

use yii\db\ColumnSchema;
use yii\db\Expression;
use yii\db\PdoValue;
use yii\db\Query;
use yii\db\Schema;
use yiiunit\TestCase;
use PDO;

class ColumnSchemaTest extends TestCase
{
    private function createColumn(array $config = []): ColumnSchema
    {
        $column = new ColumnSchema();
        foreach ($config as $property => $value) {
            $column->$property = $value;
        }
        return $column;
    }

    private function createStringColumn(string $type = Schema::TYPE_STRING): ColumnSchema
    {
        return $this->createColumn([
            'type' => $type,
            'phpType' => 'string',
        ]);
    }

    private function createIntegerColumn(): ColumnSchema
    {
        return $this->createColumn([
            'type' => Schema::TYPE_INTEGER,
            'phpType' => 'integer',
        ]);
    }

    private function createBooleanColumn(): ColumnSchema
    {
        return $this->createColumn([
            'type' => Schema::TYPE_BOOLEAN,
            'phpType' => 'boolean',
        ]);
    }

    private function createDoubleColumn(): ColumnSchema
    {
        return $this->createColumn([
            'type' => Schema::TYPE_FLOAT,
            'phpType' => 'double',
        ]);
    }

    public function testPhpTypecastDelegatesToTypecast(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertSame(42, $column->phpTypecast('42'));
    }

    public function testDbTypecastDelegatesToTypecast(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertSame(42, $column->dbTypecast('42'));
    }

    public function testNullPassthrough(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertNull($column->phpTypecast(null));
    }

    public function testExpressionPassthrough(): void
    {
        $column = $this->createIntegerColumn();
        $expression = new Expression('NOW()');
        $this->assertSame($expression, $column->phpTypecast($expression));
    }

    public function testQueryPassthrough(): void
    {
        $column = $this->createIntegerColumn();
        $query = new Query();
        $this->assertSame($query, $column->phpTypecast($query));
    }

    public function testSameTypePassthrough(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertSame(42, $column->phpTypecast(42));
    }

    /**
     * @dataProvider emptyStringToNullProvider
     */
    public function testEmptyStringToNullForNonTextTypes(string $type): void
    {
        $column = $this->createColumn([
            'type' => $type,
            'phpType' => 'integer',
        ]);
        $this->assertNull($column->phpTypecast(''));
    }

    public static function emptyStringToNullProvider(): array
    {
        return [
            'integer' => [Schema::TYPE_INTEGER],
            'boolean' => [Schema::TYPE_BOOLEAN],
            'float' => [Schema::TYPE_FLOAT],
            'decimal' => [Schema::TYPE_DECIMAL],
            'datetime' => [Schema::TYPE_DATETIME],
            'date' => [Schema::TYPE_DATE],
            'time' => [Schema::TYPE_TIME],
            'smallint' => [Schema::TYPE_SMALLINT],
            'bigint' => [Schema::TYPE_BIGINT],
            'money' => [Schema::TYPE_MONEY],
            'timestamp' => [Schema::TYPE_TIMESTAMP],
        ];
    }

    /**
     * @dataProvider emptyStringPreservedProvider
     */
    public function testEmptyStringPreservedForTextTypes(string $type): void
    {
        $column = $this->createColumn([
            'type' => $type,
            'phpType' => 'string',
        ]);
        $this->assertSame('', $column->phpTypecast(''));
    }

    public static function emptyStringPreservedProvider(): array
    {
        return [
            'text' => [Schema::TYPE_TEXT],
            'string' => [Schema::TYPE_STRING],
            'binary' => [Schema::TYPE_BINARY],
            'char' => [Schema::TYPE_CHAR],
        ];
    }

    /**
     * @dataProvider pdoValueProvider
     */
    public function testPdoValueCreation($inputValue, int $pdoType): void
    {
        $column = $this->createStringColumn();
        $result = $column->phpTypecast([$inputValue, $pdoType]);
        $this->assertInstanceOf(PdoValue::class, $result);
        $this->assertSame($inputValue, $result->getValue());
        $this->assertSame($pdoType, $result->getType());
    }

    public static function pdoValueProvider(): array
    {
        return [
            'PARAM_INT' => [42, PDO::PARAM_INT],
            'PARAM_STR' => ['hello', PDO::PARAM_STR],
            'PARAM_BOOL' => [true, PDO::PARAM_BOOL],
            'PARAM_LOB' => ['binary', PDO::PARAM_LOB],
            'PARAM_NULL' => [null, PDO::PARAM_NULL],
        ];
    }

    public function testArrayNotMatchingPdoPatternPassesThrough(): void
    {
        $column = $this->createColumn(['phpType' => 'array', 'type' => Schema::TYPE_JSON]);
        $value = ['key' => 'value'];
        $this->assertSame($value, $column->phpTypecast($value));
    }

    public function testArrayWithThreeElementsIsNotPdoValue(): void
    {
        $column = $this->createColumn(['phpType' => 'array', 'type' => Schema::TYPE_JSON]);
        $value = [1, PDO::PARAM_INT, 'extra'];
        $this->assertSame($value, $column->phpTypecast($value));
    }

    public function testArrayWithInvalidPdoTypeIsNotConverted(): void
    {
        $column = $this->createColumn(['phpType' => 'array', 'type' => Schema::TYPE_JSON]);
        $value = ['hello', 999];
        $this->assertSame($value, $column->phpTypecast($value));
    }

    public function testCastToStringPlain(): void
    {
        $column = $this->createStringColumn();
        $this->assertSame('42', $column->phpTypecast(42));
    }

    public function testCastToStringFromObject(): void
    {
        $column = $this->createStringColumn();
        $object = new StringableObject('test');
        $this->assertSame('test', $column->phpTypecast($object));
    }

    public function testCastToStringFromFloat(): void
    {
        $column = $this->createStringColumn();
        $result = $column->phpTypecast(1.5);
        $this->assertIsString($result);
        $this->assertSame('1.5', $result);
    }

    public function testCastToStringFromFloatWithTrailingZero(): void
    {
        $column = $this->createStringColumn();
        $result = $column->phpTypecast(1.0);
        $this->assertIsString($result);
        $this->assertStringContainsString('1', $result);
    }

    public function testCastToStringFromResource(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_BINARY,
            'phpType' => 'resource',
        ]);
        $resource = fopen('php://memory', 'r');
        $result = $column->phpTypecast($resource);
        $this->assertIsResource($result);
        $this->assertSame($resource, $result);
        fclose($resource);
    }

    public function testResourcePassthroughForStringPhpType(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_STRING,
            'phpType' => 'string',
        ]);
        $resource = fopen('php://memory', 'r');
        $result = $column->phpTypecast($resource);
        $this->assertIsResource($result);
        $this->assertSame($resource, $result);
        fclose($resource);
    }

    public function testNumericValueInNumericColumnPreservedAsIs(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_INTEGER,
            'phpType' => 'string',
        ]);
        $this->assertSame(42, $column->phpTypecast(42));
    }

    public function testCastToStringFromNumericStringInNumericColumn(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_DECIMAL,
            'phpType' => 'string',
        ]);
        $result = $column->phpTypecast('123.45');
        $this->assertSame('123.45', $result);
    }

    public function testCastToInteger(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertSame(42, $column->phpTypecast('42'));
    }

    public function testCastToIntegerFromFloat(): void
    {
        $column = $this->createIntegerColumn();
        $this->assertSame(3, $column->phpTypecast(3.7));
    }

    /**
     * @dataProvider booleanTruthyProvider
     */
    public function testCastToBooleanTruthy($value): void
    {
        $column = $this->createBooleanColumn();
        $this->assertSame(true, $column->phpTypecast($value));
    }

    public static function booleanTruthyProvider(): array
    {
        return [
            'integer 1' => [1],
            'string 1' => ['1'],
            'string yes' => ['yes'],
            'string TRUE' => ['TRUE'],
        ];
    }

    /**
     * @dataProvider booleanFalsyProvider
     */
    public function testCastToBooleanFalsy($value): void
    {
        $column = $this->createBooleanColumn();
        $this->assertSame(false, $column->phpTypecast($value));
    }

    public static function booleanFalsyProvider(): array
    {
        return [
            'integer 0' => [0],
            'string 0' => ['0'],
            'null byte' => ["\0"],
            'string false' => ['false'],
            'string FALSE' => ['FALSE'],
            'string False' => ['False'],
        ];
    }

    public function testEmptyStringOnBooleanColumnReturnsNull(): void
    {
        $column = $this->createBooleanColumn();
        $this->assertNull($column->phpTypecast(''));
    }

    public function testCastToDouble(): void
    {
        $column = $this->createDoubleColumn();
        $this->assertSame(3.14, $column->phpTypecast('3.14'));
    }

    public function testCastToDoubleFromInt(): void
    {
        $column = $this->createDoubleColumn();
        $result = $column->phpTypecast(42);
        $this->assertSame(42.0, $result);
    }

    public function testFallbackReturnsValueUnchanged(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_JSON,
            'phpType' => 'array',
        ]);
        $value = 'json_string';
        $this->assertSame($value, $column->phpTypecast($value));
    }

    public function testEnumValuesProperty(): void
    {
        $column = $this->createColumn([
            'type' => Schema::TYPE_STRING,
            'phpType' => 'string',
            'enumValues' => ['active', 'inactive', 'pending'],
        ]);
        $this->assertSame(['active', 'inactive', 'pending'], $column->enumValues);
    }

    public function testAutoIncrementDefaultIsFalse(): void
    {
        $column = new ColumnSchema();
        $this->assertFalse($column->autoIncrement);
    }
}

class StringableObject
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
