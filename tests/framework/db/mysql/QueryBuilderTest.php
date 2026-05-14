<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use Closure;
use PHPUnit\Framework\Attributes\Group;
use yii\base\DynamicModel;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\db\Schema;
use yii\db\mysql\QueryBuilder;
use yiiunit\base\db\BaseQueryBuilder;

/**
 * Unit test for {@see \yii\db\QueryBuilder} with MySQL driver.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('queryBuilder')]
final class QueryBuilderTest extends BaseQueryBuilder
{
    protected $driverName = 'mysql';
    protected static string $driverNameStatic = 'mysql';

    public function testBuildOrderByAndLimitWithOffsetAndLimit(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(10)
            ->offset(5);

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL
            : <<<SQL
            SELECT `id` FROM `example` LIMIT 10 OFFSET 5
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'OFFSET and LIMIT should generate the server-specific pagination syntax.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'OFFSET/LIMIT query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithLimitOnly(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(10);

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` FETCH NEXT 10 ROWS ONLY
            SQL
            : <<<SQL
            SELECT `id` FROM `example` LIMIT 10
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'LIMIT without OFFSET should generate the server-specific limit syntax.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'LIMIT-only query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithOffsetOnly(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->offset(10);

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` OFFSET 10 ROWS
            SQL
            : <<<SQL
            SELECT `id` FROM `example` LIMIT 10, 18446744073709551615
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'OFFSET without LIMIT should generate the server-specific offset syntax.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'OFFSET-only query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithoutOffsetAndLimit(): void
    {
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example');

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example`
            SQL,
            $actualQuerySql,
            'Query without OFFSET/LIMIT should not contain pagination clauses.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'Query without OFFSET/LIMIT should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithOrderByWithoutPagination(): void
    {
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->orderBy('id');

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` ORDER BY `id`
            SQL,
            $actualQuerySql,
            'ORDER BY without OFFSET/LIMIT should not contain pagination clauses.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'ORDER BY without pagination should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithZeroLimit(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(0);

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` FETCH NEXT 0 ROWS ONLY
            SQL
            : <<<SQL
            SELECT `id` FROM `example` LIMIT 0
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            "Limit '0' should generate the server-specific zero-limit syntax.",
        );
        self::assertSame(
            [],
            $actualQueryParams,
            "Limit '0' query should have no bound parameters.",
        );
    }

    public function testBuildOrderByAndLimitWithExplicitOrderBy(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->orderBy('id')
            ->limit(10)
            ->offset(5);

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` ORDER BY `id` OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL
            : <<<SQL
            SELECT `id` FROM `example` ORDER BY `id` LIMIT 10 OFFSET 5
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'Explicit ORDER BY should be preserved alongside server-specific pagination clauses.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'Query with explicit ORDER BY should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithExpressionLimitAndOffset(): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(new Expression('10'))
            ->offset(new Expression('5'));

        $expectedQuerySql = $qb->isMariaDb()
            ? <<<SQL
            SELECT `id` FROM `example` OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL
            : <<<SQL
            SELECT `id` FROM `example` LIMIT 10 OFFSET 5
            SQL;

        [$actualQuerySql, $actualQueryParams] = $qb->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'Integer Expression values should generate server-specific pagination clauses.',
        );
        self::assertSame(
            [],
            $actualQueryParams,
            'Pagination query with integer expressions should have no bound parameters.',
        );
    }

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        $columns = [
            [
                Schema::TYPE_PK . ' AFTER `col_before`',
                $this->primaryKey()->after('col_before'),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY AFTER `col_before`',
            ],
            [
                Schema::TYPE_PK . ' FIRST',
                $this->primaryKey()->first(),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . ' FIRST',
                $this->primaryKey()->first()->after('col_before'),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . '(8) AFTER `col_before`',
                $this->primaryKey(8)->after('col_before'),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY AFTER `col_before`',
            ],
            [
                Schema::TYPE_PK . '(8) FIRST',
                $this->primaryKey(8)->first(),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . '(8) FIRST',
                $this->primaryKey(8)->first()->after('col_before'),
                'int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . " COMMENT 'test' AFTER `col_before`",
                $this->primaryKey()->comment('test')->after('col_before'),
                "int NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'test' AFTER `col_before`",
            ],
            [
                Schema::TYPE_PK . " COMMENT 'testing \'quote\'' AFTER `col_before`",
                $this->primaryKey()->comment('testing \'quote\'')->after('col_before'),
                "int NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'testing \'quote\'' AFTER `col_before`",
            ],
        ];

        /*
         * TODO Remove in Yii 2.1
         *
         * Disabled due bug in MySQL extension
         * @link https://bugs.php.net/bug.php?id=70384
         */
        $columns[] = [
            Schema::TYPE_JSON,
            $this->json(),
            'json',
        ];

        return array_merge(parent::columnTypes(), $this->columnTimeTypes(), $columns);
    }

    public function columnTimeTypes()
    {
        $columns = [
            [
                Schema::TYPE_DATETIME . ' NOT NULL',
                $this->dateTime()->notNull(),
                'datetime(0) NOT NULL',
            ],
            [
                Schema::TYPE_DATETIME,
                $this->dateTime(),
                'datetime(0)',
            ],
            [
                Schema::TYPE_TIME . ' NOT NULL',
                $this->time()->notNull(),
                'time(0) NOT NULL',
            ],
            [
                Schema::TYPE_TIME,
                $this->time(),
                'time(0)',
            ],
            [
                Schema::TYPE_TIMESTAMP . ' NOT NULL',
                $this->timestamp()->notNull(),
                'timestamp(0) NOT NULL',
            ],
            [
                Schema::TYPE_TIMESTAMP . ' NULL DEFAULT NULL',
                $this->timestamp()->defaultValue(null),
                'timestamp(0) NULL DEFAULT NULL',
            ],
        ];

        /**
         * @link https://github.com/yiisoft/yii2/issues/14834
         */
        $sqlModes = $this->getConnection(false)->createCommand('SELECT @@sql_mode')->queryScalar();
        $sqlModes = explode(',', $sqlModes);
        if (in_array('NO_ZERO_DATE', $sqlModes, true)) {
            $this->markTestIncomplete(
                "MySQL doesn't allow the 'TIMESTAMP' column definition when the NO_ZERO_DATE mode enabled. " .
                'This definition test was skipped.'
            );
        } else {
            $columns[] = [
                Schema::TYPE_TIMESTAMP,
                $this->timestamp(),
                'timestamp(0)',
            ];
        }

        return $columns;
    }

    public static function primaryKeysProvider(): array
    {
        $result = parent::primaryKeysProvider();
        $result['drop'][0] = 'ALTER TABLE {{T_constraints_1}} DROP PRIMARY KEY';
        $result['add'][0] = 'ALTER TABLE {{T_constraints_1}} ADD CONSTRAINT [[CN_pk]] PRIMARY KEY ([[C_id_1]])';
        $result['add (2 columns)'][0] = 'ALTER TABLE {{T_constraints_1}} ADD CONSTRAINT [[CN_pk]] PRIMARY KEY ([[C_id_1]], [[C_id_2]])';
        return $result;
    }

    public static function foreignKeysProvider(): array
    {
        $result = parent::foreignKeysProvider();
        $result['drop'][0] = 'ALTER TABLE {{T_constraints_3}} DROP FOREIGN KEY [[CN_constraints_3]]';
        return $result;
    }

    public static function indexesProvider(): array
    {
        $result = parent::indexesProvider();
        $result['create'][0] = 'ALTER TABLE {{T_constraints_2}} ADD INDEX [[CN_constraints_2_single]] ([[C_index_1]])';
        $result['create (2 columns)'][0] = 'ALTER TABLE {{T_constraints_2}} ADD INDEX [[CN_constraints_2_multi]] ([[C_index_2_1]], [[C_index_2_2]])';
        $result['create unique'][0] = 'ALTER TABLE {{T_constraints_2}} ADD UNIQUE INDEX [[CN_constraints_2_single]] ([[C_index_1]])';
        $result['create unique (2 columns)'][0] = 'ALTER TABLE {{T_constraints_2}} ADD UNIQUE INDEX [[CN_constraints_2_multi]] ([[C_index_2_1]], [[C_index_2_2]])';
        return $result;
    }

    public static function uniquesProvider(): array
    {
        $result = parent::uniquesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_unique]] ON {{T_constraints_1}}';
        return $result;
    }

    public function testResetSequence(): void
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE `item` AUTO_INCREMENT=6';
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE `item` AUTO_INCREMENT=4';
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public static function upsertProvider(): array
    {
        $concreteData = [
            'regular values' => [
                3 => 'INSERT INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3) ON DUPLICATE KEY UPDATE `address`=VALUES(`address`), `status`=VALUES(`status`), `profile_id`=VALUES(`profile_id`)',
            ],
            'regular values with update part' => [
                3 => 'INSERT INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3) ON DUPLICATE KEY UPDATE `address`=:qp4, `status`=:qp5, `orders`=T_upsert.orders + 1',
            ],
            'regular values without update part' => [
                3 => 'INSERT INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3) ON DUPLICATE KEY UPDATE `email`=`T_upsert`.`email`',
            ],
            'query' => [
                3 => [
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1 ON DUPLICATE KEY UPDATE `status`=VALUES(`status`)',
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 FETCH NEXT 1 ROWS ONLY ON DUPLICATE KEY UPDATE `status`=VALUES(`status`)',
                ],
            ],
            'query with update part' => [
                3 => [
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1 ON DUPLICATE KEY UPDATE `address`=:qp1, `status`=:qp2, `orders`=T_upsert.orders + 1',
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 FETCH NEXT 1 ROWS ONLY ON DUPLICATE KEY UPDATE `address`=:qp1, `status`=:qp2, `orders`=T_upsert.orders + 1',
                ],
            ],
            'query without update part' => [
                3 => [
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1 ON DUPLICATE KEY UPDATE `email`=`T_upsert`.`email`',
                    'INSERT INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 FETCH NEXT 1 ROWS ONLY ON DUPLICATE KEY UPDATE `email`=`T_upsert`.`email`',
                ],
            ],
            'values and expressions' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions with update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions without update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'query, values and expressions with update part' => [
                3 => 'INSERT INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]] ON DUPLICATE KEY UPDATE `ts`=:qp1, [[orders]]=T_upsert.orders + 1',
            ],
            'query, values and expressions without update part' => [
                3 => 'INSERT INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]] ON DUPLICATE KEY UPDATE `ts`=:qp1, [[orders]]=T_upsert.orders + 1',
            ],
            'no columns to update' => [
                3 => 'INSERT INTO `T_upsert_1` (`a`) VALUES (:qp0) ON DUPLICATE KEY UPDATE `a`=`T_upsert_1`.`a`',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }

    public static function conditionProvider(): array
    {
        return array_merge(
            parent::conditionProvider(),
            [
                // json conditions
                [
                    ['=', 'jsoncol', new JsonExpression(['lang' => 'uk', 'country' => 'UA'])],
                    '[[jsoncol]] = :qp0', [':qp0' => '{"lang":"uk","country":"UA"}'],
                ],
                [
                    ['=', 'jsoncol', new JsonExpression([false])],
                    '[[jsoncol]] = :qp0', [':qp0' => '[false]']
                ],
                'object with type. Type is ignored for MySQL' => [
                    ['=', 'prices', new JsonExpression(['seeds' => 15, 'apples' => 25], 'jsonb')],
                    '[[prices]] = :qp0', [':qp0' => '{"seeds":15,"apples":25}'],
                ],
                'nested json' => [
                    ['=', 'data', new JsonExpression(['user' => ['login' => 'silverfire', 'password' => 'c4ny0ur34d17?'], 'props' => ['mood' => 'good']])],
                    '[[data]] = :qp0', [':qp0' => '{"user":{"login":"silverfire","password":"c4ny0ur34d17?"},"props":{"mood":"good"}}']
                ],
                'null value' => [
                    ['=', 'jsoncol', new JsonExpression(null)],
                    '[[jsoncol]] = :qp0', [':qp0' => 'null']
                ],
                'null as array value' => [
                    ['=', 'jsoncol', new JsonExpression([null])],
                    '[[jsoncol]] = :qp0', [':qp0' => '[null]']
                ],
                'null as object value' => [
                    ['=', 'jsoncol', new JsonExpression(['nil' => null])],
                    '[[jsoncol]] = :qp0', [':qp0' => '{"nil":null}']
                ],
                'with object as value' => [
                    ['=', 'jsoncol', new JsonExpression(new DynamicModel(['a' => 1, 'b' => 2]))],
                    '[[jsoncol]] = :qp0', [':qp0' => '{"a":1,"b":2}']
                ],
                'query' => [
                    ['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]))],
                    '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]
                ],
                'query with type, that is ignored in MySQL' => [
                    ['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]), 'jsonb')],
                    '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]
                ],
                'nested and combined json expression' => [
                    ['=', 'jsoncol', new JsonExpression(new JsonExpression(['a' => 1, 'b' => 2, 'd' => new JsonExpression(['e' => 3])]))],
                    '[[jsoncol]] = :qp0', [':qp0' => '{"a":1,"b":2,"d":{"e":3}}']
                ],
                'search by property in JSON column (issue #15838)' => [
                    ['=', new Expression("(jsoncol->>'$.someKey')"), '42'],
                    "(jsoncol->>'$.someKey') = :qp0", [':qp0' => 42]
                ]
            ],
        );
    }

    public static function updateProvider(): array
    {
        $items = parent::updateProvider();

        $items[] = [
            'profile',
            [
                'description' => new JsonExpression(['abc' => 'def', 123, null]),
            ],
            [
                'id' => 1,
            ],
            'UPDATE [[profile]] SET [[description]]=:qp0 WHERE [[id]]=:qp1',
            [
                ':qp0' => '{"abc":"def","0":123,"1":null}',
                ':qp1' => 1,
            ],
        ];

        return $items;
    }

    public function testIssue17449(): void
    {
        $db = $this->getConnection();
        $pdo = $db->pdo;
        $pdo->exec('DROP TABLE IF EXISTS `issue_17449`');

        $tableQuery = <<<MySqlStatement
CREATE TABLE `issue_17449` (
  `test_column` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'some comment' CHECK (json_valid(`test_column`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1
MySqlStatement;
        $db->createCommand($tableQuery)->execute();

        $actual = $db->createCommand()->addCommentOnColumn('issue_17449', 'test_column', 'Some comment')->rawSql;

        $checkPos = stripos($actual, 'check');
        if ($checkPos === false) {
            $this->markTestSkipped("The used MySql-Server removed or moved the CHECK from the column line, so the original bug doesn't affect it");
        }
        $commentPos = stripos($actual, 'comment');
        $this->assertNotFalse($commentPos);
        $this->assertLessThan($checkPos, $commentPos);
    }

    /**
     * Test for issue https://github.com/yiisoft/yii2/issues/14663
     */
    public function testInsertInteger(): void
    {
        $db = $this->getConnection();
        $command = $db->createCommand();

        // int value should not be converted to string, when column is `int`
        $sql = $command->insert('{{type}}', ['int_col' => 22])->getRawSql();
        $this->assertEquals('INSERT INTO `type` (`int_col`) VALUES (22)', $sql);

        // int value should not be converted to string, when column is `int unsigned`
        $sql = $command->insert('{{type}}', ['int_col3' => 22])->getRawSql();
        $this->assertEquals('INSERT INTO `type` (`int_col3`) VALUES (22)', $sql);

        // int value should not be converted to string, when column is `bigint unsigned`
        $sql = $command->insert('{{type}}', ['bigint_col' => 22])->getRawSql();
        $this->assertEquals('INSERT INTO `type` (`bigint_col`) VALUES (22)', $sql);

        // string value should not be converted
        $sql = $command->insert('{{type}}', ['bigint_col' => '1000000000000'])->getRawSql();
        $this->assertEquals("INSERT INTO `type` (`bigint_col`) VALUES ('1000000000000')", $sql);
    }

    /**
     * Test for issue https://github.com/yiisoft/yii2/issues/15500
     */
    public function testDefaultValues(): void
    {
        $db = $this->getConnection();
        $command = $db->createCommand();

        // primary key columns should have NULL as value
        $sql = $command->insert('null_values', [])->getRawSql();
        $this->assertEquals('INSERT INTO `null_values` (`id`) VALUES (NULL)', $sql);

        // non-primary key columns should have DEFAULT as value
        $sql = $command->insert('negative_default_values', [])->getRawSql();
        $this->assertEquals('INSERT INTO `negative_default_values` (`tinyint_col`) VALUES (DEFAULT)', $sql);
    }

    /**
     * @dataProvider defaultValuesProvider
     * @param string $sql
     */
    public function testAddDropDefaultValue($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^mysql does not support (adding|dropping) default value constraints\.$/',
        );

        parent::testAddDropDefaultValue($sql, $builder);
    }
}
