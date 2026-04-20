<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\conditions;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\conditions\InCondition;
use yii\db\Query;
use yii\db\sqlite\conditions\InConditionBuilder;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\sqlite\conditions\providers\InConditionBuilderProvider;

/**
 * Unit test for {@see \yii\db\sqlite\conditions\InConditionBuilder} with SQLite driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('condition')]
#[Group('sqlite')]
final class InConditionBuilderTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';
    protected static string $driverNameStatic = 'sqlite';

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

    public function testThrowNotSupportedExceptionWhenBuildSubqueryInConditionIsCompositeColumns(): void
    {
        $query = new Query();

        $db = $this->getConnection(true, false);
        $inConditionBuilder = new InConditionBuilder($db->getQueryBuilder());

        $inCondition = new InCondition(
            ['id', 'name'],
            'in',
            $query
                ->select(['id', 'name'])
                ->from('users')
                ->where(['active' => 1]),
        );

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\\db\\sqlite\\conditions\\InConditionBuilder::buildSubqueryInCondition is not supported by SQLite.',
        );

        $inConditionBuilder->build($inCondition);
    }

    public function testThrowInvalidArgumentExceptionWhenFromArrayDefinitionHasMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'IN' requires two operands.");

        InCondition::fromArrayDefinition('IN', []);
    }
}
