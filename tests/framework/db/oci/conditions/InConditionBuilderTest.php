<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\conditions;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidArgumentException;
use yii\db\conditions\InCondition;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\oci\conditions\providers\InConditionBuilderProvider;

/**
 * Unit test for {@see \yii\db\conditions\InConditionBuilder} with Oracle driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('condition')]
#[Group('oci')]
final class InConditionBuilderTest extends DatabaseTestCase
{
    protected $driverName = 'oci';
    protected static string $driverNameStatic = 'oci';

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

    public function testBuildConditionSplitsInWhenValueCountExceedsOracleLimit(): void
    {
        $query = (new Query())->where(new InCondition('id', 'in', range(1, 1001)));

        $db = $this->getConnection(true, false);

        [$sql, $params] = $db->getQueryBuilder()->build($query);

        $expectedSql = 'SELECT * WHERE ("id" IN (' . implode(', ', self::buildPlaceholders(0, 999)) . ')) OR ("id"=:qp1000)';

        self::assertSame(
            $expectedSql,
            $sql,
            'Split IN SQL should match expected chunked SQL.',
        );
        self::assertSame(
            self::buildExpectedParams(1001),
            $params,
            'Split IN SQL should match expected bound parameters.',
        );
    }

    public function testBuildConditionSplitsNotInWhenValueCountExceedsOracleLimit(): void
    {
        $query = (new Query())->where(new InCondition('id', 'not in', range(1, 1001)));

        $db = $this->getConnection(true, false);

        [$sql, $params] = $db->getQueryBuilder()->build($query);

        $expectedSql = 'SELECT * WHERE ("id" NOT IN (' . implode(', ', self::buildPlaceholders(0, 999)) . ')) AND ("id"<>:qp1000)';

        self::assertSame(
            $expectedSql,
            $sql,
            'Split NOT IN SQL should match expected chunked SQL.',
        );
        self::assertSame(
            self::buildExpectedParams(1001),
            $params,
            'Split NOT IN SQL should match expected bound parameters.',
        );
    }

    public function testThrowInvalidArgumentExceptionWhenFromArrayDefinitionHasMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'IN' requires two operands.");

        InCondition::fromArrayDefinition('IN', []);
    }

    private static function buildPlaceholders(int $from, int $to): array
    {
        $placeholders = [];

        for ($i = $from; $i <= $to; ++$i) {
            $placeholders[] = ":qp$i";
        }

        return $placeholders;
    }

    private static function buildExpectedParams(int $count): array
    {
        $params = [];

        for ($i = 0; $i < $count; ++$i) {
            $params[":qp$i"] = $i + 1;
        }

        return $params;
    }
}
