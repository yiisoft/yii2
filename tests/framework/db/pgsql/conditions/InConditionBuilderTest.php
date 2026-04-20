<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\conditions;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidArgumentException;
use yii\db\conditions\InCondition;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\pgsql\conditions\providers\InConditionBuilderProvider;

/**
 * Unit test for {@see \yii\db\conditions\InConditionBuilder} with PostgreSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('condition')]
#[Group('pgsql')]
final class InConditionBuilderTest extends DatabaseTestCase
{
    protected $driverName = 'pgsql';
    protected static string $driverNameStatic = 'pgsql';

    #[DataProviderExternal(InConditionBuilderProvider::class, 'buildCondition')]
    public function testBuildCondition(array|object $condition, string $expected, array $expectedParams): void
    {
        $query = (new Query())->where($condition);

        $db = $this->getConnection(true, false);

        [$sql, $params] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            'SELECT *' . ($expected === '' ? '' : ' WHERE ' . $this->replaceQuotes($expected)),
            $sql,
            'Generated SQL does not match expected SQL.',
        );
        self::assertSame(
            $expectedParams,
            $params,
            'Bound parameters do not match expected parameters.',
        );
    }

    public function testThrowInvalidArgumentExceptionWhenFromArrayDefinitionHasMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'IN' requires two operands.");

        InCondition::fromArrayDefinition('IN', []);
    }
}
