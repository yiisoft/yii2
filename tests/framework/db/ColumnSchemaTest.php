<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\ColumnSchema;
use yii\db\Expression;
use yii\db\PdoValue;
use yii\db\Query;
use yii\db\Schema;
use yiiunit\data\base\StringableObject;
use yiiunit\framework\db\providers\ColumnSchemaProvider;
use yiiunit\TestCase;
use PDO;

use function fclose;
use function fopen;

/**
 * Unit tests for {@see \yii\db\ColumnSchema} type-casting and value handling.
 */
#[Group('db')]
#[Group('column-schema')]
final class ColumnSchemaTest extends TestCase
{
    public function testPhpTypecastDelegatesToTypecast(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            42,
            $column->phpTypecast('42'),
            "Numeric string must cast to 'int'.",
        );
    }

    public function testDbTypecastDelegatesToTypecast(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            42,
            $column->dbTypecast('42'),
            "Numeric string must cast to 'int'.",
        );
    }

    public function testIsTypeReturnsTrueForMatchingType(): void
    {
        $column = $this->createColumn(
            ['type' => Schema::TYPE_BINARY],
        );

        self::assertTrue(
            $column->isType(Schema::TYPE_BINARY),
            "Matching type must yield 'true'.",
        );
    }

    public function testIsTypeReturnsFalseForDifferentType(): void
    {
        $column = $this->createStringColumn();

        self::assertFalse(
            $column->isType(Schema::TYPE_BINARY),
            "Different type must yield 'false'.",
        );
    }

    public function testDefaultPhpTypecastDelegatesToPhpTypecastForInteger(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            42,
            $column->defaultPhpTypecast('42'),
            "Integer default must cast to 'int'.",
        );
    }

    public function testDefaultPhpTypecastDelegatesToPhpTypecastForDouble(): void
    {
        $column = $this->createDoubleColumn();

        self::assertSame(
            3.14,
            $column->defaultPhpTypecast('3.14'),
            "Double default must cast to 'float'.",
        );
    }

    public function testDefaultPhpTypecastDelegatesToPhpTypecastForString(): void
    {
        $column = $this->createStringColumn();

        self::assertSame(
            'hello',
            $column->defaultPhpTypecast('hello'),
            'String default must pass through.',
        );
    }

    public function testDefaultPhpTypecastReturnsNullForNull(): void
    {
        $column = $this->createIntegerColumn();

        self::assertNull(
            $column->defaultPhpTypecast(null),
            "Null default must stay 'null'.",
        );
    }

    public function testNullPassthrough(): void
    {
        $column = $this->createIntegerColumn();

        self::assertNull(
            $column->phpTypecast(null),
            "Null input must stay 'null'.",
        );
    }

    public function testExpressionPassthrough(): void
    {
        $column = $this->createIntegerColumn();

        $expression = new Expression('NOW()');

        self::assertSame(
            $expression,
            $column->phpTypecast($expression),
            'Expression must pass through unchanged.',
        );
    }

    public function testQueryPassthrough(): void
    {
        $column = $this->createIntegerColumn();

        $query = new Query();

        self::assertSame(
            $query,
            $column->phpTypecast($query),
            'Query must pass through unchanged.',
        );
    }

    public function testSameTypePassthrough(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            42,
            $column->phpTypecast(42),
            'Matching type must pass through unchanged.',
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'emptyStringToNull')]
    public function testEmptyStringToNullForNonTextTypes(string $type): void
    {
        $column = $this->createColumn(
            [
                'type' => $type,
                'phpType' => 'integer',
            ],
        );

        self::assertNull(
            $column->phpTypecast(''),
            "Empty string must become 'null'.",
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'emptyStringPreserved')]
    public function testEmptyStringPreservedForTextTypes(string $type): void
    {
        $column = $this->createColumn(
            [
                'type' => $type,
                'phpType' => 'string',
            ],
        );

        self::assertSame(
            '',
            $column->phpTypecast(''),
            'Empty string must be preserved.',
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'pdoValue')]
    public function testPdoValueCreation($inputValue, int $pdoType): void
    {
        $column = $this->createStringColumn();

        $result = $column->phpTypecast([$inputValue, $pdoType]);

        self::assertInstanceOf(
            PdoValue::class,
            $result,
            "Result must be a 'PdoValue'.",
        );
        self::assertSame(
            $inputValue,
            $result->getValue(),
            'Wrapped value must be preserved.',
        );
        self::assertSame(
            $pdoType,
            $result->getType(),
            'PDO type must be preserved.',
        );
    }

    public function testArrayNotMatchingPdoPatternPassesThrough(): void
    {
        $column = $this->createColumn(
            [
                'phpType' => 'array',
                'type' => Schema::TYPE_JSON,
            ],
        );

        $value = ['key' => 'value'];

        self::assertSame(
            $value,
            $column->phpTypecast($value),
            'Non-PDO array must pass through unchanged.',
        );
    }

    public function testArrayWithThreeElementsIsNotPdoValue(): void
    {
        $column = $this->createColumn(
            [
                'phpType' => 'object',
                'type' => Schema::TYPE_JSON,
            ],
        );

        $value = [
            1,
            PDO::PARAM_INT,
            'extra',
        ];

        self::assertSame(
            $value,
            $column->phpTypecast($value),
            'Three-element array must pass through unchanged.',
        );
    }

    public function testArrayWithInvalidPdoTypeIsNotConverted(): void
    {
        $column = $this->createColumn(
            [
                'phpType' => 'object',
                'type' => Schema::TYPE_JSON,
            ],
        );

        $value = [
            'hello',
            999,
        ];

        self::assertSame(
            $value,
            $column->phpTypecast($value),
            'Invalid PDO type must pass through unchanged.',
        );
    }

    public function testCastToStringPlain(): void
    {
        $column = $this->createStringColumn();

        self::assertSame(
            '42',
            $column->phpTypecast(42),
            "Integer must cast to 'string'.",
        );
    }

    public function testCastToStringFromObject(): void
    {
        $column = $this->createStringColumn();

        $object = new StringableObject('test');

        self::assertSame(
            'test',
            $column->phpTypecast($object),
            "Stringable must cast to 'string'.",
        );
    }

    public function testCastToStringFromFloat(): void
    {
        $column = $this->createStringColumn();

        $result = $column->phpTypecast(1.5);

        self::assertIsString(
            $result,
            "Result must be a 'string'.",
        );
        self::assertSame(
            '1.5',
            $result,
            "Float must cast to 'string'.",
        );
    }

    public function testCastToStringFromFloatWithTrailingZero(): void
    {
        $column = $this->createStringColumn();

        $result = $column->phpTypecast(1.0);

        self::assertSame(
            '1',
            $result,
            'Trailing zero must be dropped.',
        );
    }

    public function testCastToStringFromResource(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_BINARY,
                'phpType' => 'resource',
            ],
        );

        $resource = fopen('php://memory', 'r');

        $result = $column->phpTypecast($resource);

        self::assertIsResource(
            $result,
            'Result must be a resource.',
        );
        self::assertSame(
            $resource,
            $result,
            'Resource must pass through unchanged.',
        );

        fclose($resource);
    }

    public function testResourcePassthroughForStringPhpType(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_STRING,
                'phpType' => 'string',
            ],
        );

        $resource = fopen('php://memory', 'r');
        $result = $column->phpTypecast($resource);

        self::assertIsResource(
            $result,
            'Result must be a resource.',
        );
        self::assertSame(
            $resource,
            $result,
            'Resource must pass through unchanged.',
        );

        fclose($resource);
    }

    public function testNumericValueInNumericColumnPreservedAsIs(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_INTEGER,
                'phpType' => 'string',
            ],
        );

        self::assertSame(
            42,
            $column->phpTypecast(42),
            'Integer must be preserved as is.',
        );
    }

    public function testCastToStringFromNumericStringInNumericColumn(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_DECIMAL,
                'phpType' => 'string',
            ],
        );

        $result = $column->phpTypecast('123.45');

        self::assertSame(
            '123.45',
            $result,
            'Numeric string must be preserved.',
        );
    }

    public function testCastToInteger(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            42,
            $column->phpTypecast('42'),
            "Numeric string must cast to 'int'.",
        );
    }

    public function testCastToIntegerFromFloat(): void
    {
        $column = $this->createIntegerColumn();

        self::assertSame(
            3,
            $column->phpTypecast(3.7),
            "Float must truncate to 'int'.",
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'booleanTruthy')]
    public function testCastToBooleanTruthy($value): void
    {
        $column = $this->createBooleanColumn();

        self::assertSame(
            true,
            $column->phpTypecast($value),
            "Truthy value must cast to 'true'.",
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'booleanFalsy')]
    public function testCastToBooleanFalsy($value): void
    {
        $column = $this->createBooleanColumn();

        self::assertSame(
            false,
            $column->phpTypecast($value),
            "Falsy value must cast to 'false'.",
        );
    }

    public function testEmptyStringOnBooleanColumnReturnsNull(): void
    {
        $column = $this->createBooleanColumn();

        self::assertNull(
            $column->phpTypecast(''),
            "Empty string must become 'null'.",
        );
    }

    public function testCastToDouble(): void
    {
        $column = $this->createDoubleColumn();

        self::assertSame(
            3.14,
            $column->phpTypecast('3.14'),
            "Numeric string must cast to 'float'.",
        );
    }

    public function testCastToDoubleFromInt(): void
    {
        $column = $this->createDoubleColumn();

        $result = $column->phpTypecast(42);

        self::assertSame(
            42.0,
            $result,
            "Integer must cast to 'float'.",
        );
    }

    public function testFallbackReturnsValueUnchanged(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_JSON,
                'phpType' => 'array',
            ],
        );

        $value = 'json_string';

        self::assertSame(
            $value,
            $column->phpTypecast($value),
            'Unhandled type must pass through unchanged.',
        );
    }

    public function testEnumValuesProperty(): void
    {
        $column = $this->createColumn(
            [
                'type' => Schema::TYPE_STRING,
                'phpType' => 'string',
                'enumValues' => [
                    'active',
                    'inactive',
                    'pending',
                ],
            ],
        );

        self::assertSame(
            ['active', 'inactive', 'pending'],
            $column->enumValues,
            'Enum values must be stored.',
        );
    }

    public function testAutoIncrementDefaultIsFalse(): void
    {
        $column = new ColumnSchema();

        self::assertFalse(
            $column->autoIncrement,
            "Default must be 'false'.",
        );
    }

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
        return $this->createColumn(
            [
                'type' => $type,
                'phpType' => 'string',
            ],
        );
    }

    private function createIntegerColumn(): ColumnSchema
    {
        return $this->createColumn(
            [
                'type' => Schema::TYPE_INTEGER,
                'phpType' => 'integer',
            ],
        );
    }

    private function createBooleanColumn(): ColumnSchema
    {
        return $this->createColumn(
            [
                'type' => Schema::TYPE_BOOLEAN,
                'phpType' => 'boolean',
            ],
        );
    }

    private function createDoubleColumn(): ColumnSchema
    {
        return $this->createColumn(
            [
                'type' => Schema::TYPE_FLOAT,
                'phpType' => 'double',
            ],
        );
    }
}
