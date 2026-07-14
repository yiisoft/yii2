<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use Closure;
use PDO;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\ArrayExpression;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\JsonExpression;
use yii\db\pgsql\Schema;
use yii\db\Query;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\pgsql\providers\CommandProvider;
use yiiunit\support\DbHelper;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function count;

/**
 * Unit tests for {@see \yii\db\Command} functionality for the PostgreSQL driver.
 *
 * {@see CommandProvider} for test case data providers.
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('command')]
class CommandTest extends BaseCommand
{
    public $driverName = 'pgsql';

    #[DataProviderExternal(CommandProvider::class, 'upsert')]
    public function testUpsert(
        string $table,
        array|Query $firstInsert,
        array|Query $secondInsert,
        array|bool $updateColumns,
        array $expected,
    ): void {
        $db = $this->getConnection();

        $command = $db->createCommand();

        self::assertSame(
            0,
            (int) (new Query())
                ->from($table)
                ->count('*', $db),
            'Target table must start empty.',
        );

        $command->upsert(
            $table,
            $firstInsert,
            $updateColumns,
        )->execute();

        self::assertSame(
            1,
            (int) (new Query())
                ->from($table)
                ->count('*', $db),
            'Insert path must create exactly one row.',
        );

        $command->upsert(
            $table,
            $secondInsert,
            $updateColumns,
        )->execute();

        $select = [];

        foreach (array_keys($expected) as $column) {
            $select[$column] = $column === 'address'
                ? new Expression('CAST([[address]] AS VARCHAR(255))')
                : $column;
        }

        self::assertEquals(
            $expected,
            (new Query())
                ->select($select)
                ->from($table)
                ->one($db),
            'Conflict must apply the update behavior.',
        );
    }

    public function testThrowIntegrityExceptionWhenConflictMatchesNonArbiterConstraint(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->upsert(
            'T_upsert_2',
            ['a' => 1, 'b' => 1, 'c' => 'first'],
            true,
        )->execute();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessage(
            'duplicate key value violates unique constraint "T_upsert_2_b_key"',
        );

        $command->upsert(
            'T_upsert_2',
            ['a' => 2, 'b' => 1, 'c' => 'second'],
            true,
        )->execute();
    }

    public function testThrowIntegrityExceptionWhenUniqueConflictMissesPrimaryKeyArbiter(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->upsert(
            'T_upsert',
            ['id' => 1, 'email' => 'unique@example.com', 'address' => 'first'],
            true,
        )->execute();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessage(
            'duplicate key value violates unique constraint "T_upsert_email_key"',
        );

        $command->upsert(
            'T_upsert',
            ['id' => 2, 'email' => 'unique@example.com', 'address' => 'second'],
            true,
        )->execute();
    }

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testThrowIntegrityExceptionWhenCheckIntegrityEnablesTriggersWithoutChangingNativePrepares(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();
        $pdo = $db->getMasterPdo();

        $originalEmulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $command->checkIntegrity(false, '', 'item')->execute();

            self::assertFalse(
                $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
                'Disabling integrity checks must not enable emulated prepares.',
            );
            self::assertSame(
                1,
                $command->insert(
                    'item',
                    ['name' => 'invalid category while disabled', 'category_id' => -1],
                )->execute(),
                'Disabling integrity checks must disable the foreign key trigger.',
            );

            $command->checkIntegrity(true, '', 'item')->execute();

            self::assertFalse(
                $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
                'Enabling integrity checks must preserve native prepares.',
            );

            $this->expectException(IntegrityException::class);
            $this->expectExceptionMessage(
                'insert or update on table "item" violates foreign key constraint "item_category_id_fkey"',
            );

            $command->insert(
                'item',
                ['name' => 'invalid category while enabled', 'category_id' => -2],
            )->execute();
        } finally {
            $command->checkIntegrity(true, '', 'item')->execute();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulatePrepares);
        }
    }

    public function testCheckIntegrityPreservesEmulatedPrepares(): void
    {
        $db = $this->getConnection();

        $pdo = $db->getMasterPdo();

        $originalEmulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $command = $db->createCommand()->checkIntegrity(true, 'schema1', 'profile');

        self::assertTrue(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Building the command must preserve emulated prepares.',
        );

        $command->execute();

        self::assertTrue(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Executing the command must preserve emulated prepares.',
        );

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulatePrepares);
    }

    public function testThrowExceptionWhenCheckIntegrityTargetsMissingTableWithoutChangingNativePrepares(): void
    {
        $db = $this->getConnection();

        $pdo = $db->getMasterPdo();

        $originalEmulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $command = $db->createCommand()->checkIntegrity(false, '', 'missing_check_integrity_table');

            $this->expectException(Exception::class);
            $this->expectExceptionMessage(
                'relation "public.missing_check_integrity_table" does not exist',
            );

            $command->execute();
        } finally {
            $emulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulatePrepares);

            self::assertFalse(
                $emulatePrepares,
                'A failed command must preserve native prepares.',
            );
        }
    }

    public function testCheckIntegritySupportsMultipleTablesWithNativePrepares(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'The connection must return a PostgreSQL schema instance.',
        );

        $viewNames = $schema->getViewNames('public');

        $tableNames = array_diff($schema->getTableNames('public'), $viewNames);

        $pdo = $db->getMasterPdo();
        $originalEmulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

        self::assertGreaterThan(
            1,
            count($tableNames),
            'The fixture must contain multiple tables.',
        );
        self::assertContains(
            'animal_view',
            $viewNames,
            'The fixture must contain a view to exclude.',
        );

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $command = $db->createCommand()->checkIntegrity(true, 'public');

        self::assertFalse(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Building a schema-wide command must preserve native prepares.',
        );

        $command->prepare();

        self::assertFalse(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Preparing a schema-wide command must preserve native prepares.',
        );

        $command->execute();

        self::assertFalse(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Executing a schema-wide command must preserve native prepares.',
        );

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulatePrepares);
    }

    public function testBuildingCheckIntegrityDoesNotChangeNativePrepares(): void
    {
        $db = $this->getConnection();

        $pdo = $db->getMasterPdo();
        $originalEmulatePrepares = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->createCommand()->checkIntegrity(false, '', 'item');

        self::assertFalse(
            $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Building the command must not change the PDO attribute.',
        );

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulatePrepares);
    }

    public function testCheckIntegrityReturnsEmptyCommandWhenTableIsView(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand()->checkIntegrity(true, 'public', 'animal_view');

        self::assertSame(
            '',
            $command->getSql(),
            'A view must not produce an integrity-check statement.',
        );
        self::assertSame(
            0,
            $command->execute(),
            'Executing an empty integrity-check command must be a no-op.',
        );
    }

    public function testCheckIntegrityExecutesWhenTableNameContainsDollarQuoteDelimiter(): void
    {
        $db = $this->getConnection();

        $tableName = 'check_integrity_$yii$_table';

        $command = $db->createCommand()->createTable($tableName, ['id' => 'pk']);

        $command->execute();

        self::assertSame(
            0,
            $command->checkIntegrity(true, 'public', $tableName)->execute(),
            'Integrity checks must execute when a table name contains the initial dollar-quote delimiter.',
        );

        DbHelper::dropTablesIfExist($db, [$tableName]);
    }

    public function testBooleanValuesInsert(): void
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => true]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => false]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }

    public function testBooleanValuesBatchInsert(): void
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert(
            'bool_values',
            ['bool_col'],
            [
                [true],
                [false],
            ]
        );
        $this->assertEquals(2, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }

    public function testLastInsertId(): void
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertSame('3', $db->getSchema()->getLastInsertID('public.profile_id_seq'));

        $sql = 'INSERT INTO {{schema1.profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertSame('3', $db->getSchema()->getLastInsertID('schema1.profile_id_seq'));
    }

    public static function dataProviderGetRawSql(): array
    {
        return array_merge(parent::dataProviderGetRawSql(), [
            [
                'SELECT * FROM customer WHERE id::integer IN (:in, :out)',
                [':in' => 1, ':out' => 2],
                'SELECT * FROM customer WHERE id::integer IN (1, 2)',
            ],
        ]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11498
     */
    public function testSaveSerializedObject(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand()->insert('type', [
            'int_col' => 1,
            'char_col' => 'serialize',
            'float_col' => 5.6,
            'bool_col' => true,
            'blob_col' => serialize($db),
        ]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand()->update('type', [
            'blob_col' => serialize($db),
        ], ['char_col' => 'serialize']);
        $this->assertEquals(1, $command->execute());
    }

    public static function batchInsertSqlProvider(): array
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO "type" ("int_col", "float_col", "char_col") VALUES (NULL, NULL, \'Kyiv {{city}}, Ukraine\')';
        $data['wrongBehavior']['expected'] = 'INSERT INTO "type" ("type"."int_col", "float_col", "char_col") VALUES (\'\', \'\', \'Kyiv {{city}}, Ukraine\')';
        $data['batchInsert binds params from expression']['expected'] = 'INSERT INTO "type" ("int_col") VALUES (:qp1)';
        $data['batchInsert binds params from jsonExpression'] = [
            '{{%type}}',
            ['json_col'],
            [[new JsonExpression(['username' => 'silverfire', 'is_active' => true, 'langs' => ['Ukrainian', 'Russian', 'English']])]],
            'expected' => 'INSERT INTO "type" ("json_col") VALUES (:qp0)',
            'expectedParams' => [':qp0' => '{"username":"silverfire","is_active":true,"langs":["Ukrainian","Russian","English"]}']
        ];
        $data['batchInsert binds params from arrayExpression'] = [
            '{{%type}}',
            ['intarray_col'],
            [[new ArrayExpression([1,null,3], 'int')]],
            'expected' => 'INSERT INTO "type" ("intarray_col") VALUES (ARRAY[:qp0, :qp1, :qp2]::int[])',
            'expectedParams' => [':qp0' => 1, ':qp1' => null, ':qp2' => 3]
        ];
        $data['batchInsert casts string to int according to the table schema'] = [
            '{{%type}}',
            ['int_col'],
            [['3']],
            'expected' => 'INSERT INTO "type" ("int_col") VALUES (3)',
        ];
        $data['batchInsert casts JSON to JSONB when column is JSONB'] = [
            '{{%type}}',
            ['jsonb_col'],
            [[['a' => true]]],
            'expected' => 'INSERT INTO "type" ("jsonb_col") VALUES (:qp0::jsonb)',
            'expectedParams' => [':qp0' => '{"a":true}']
        ];

        return $data;
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15827
     */
    public function testIssue15827(): void
    {
        $db = $this->getConnection();

        $inserted = $db->createCommand()->insert('array_and_json_types', [
            'jsonb_col' => new JsonExpression(['Solution date' => '13.01.2011'])
        ])->execute();
        $this->assertSame(1, $inserted);


        $found = $db->createCommand(
            <<<PGSQL
            SELECT *
            FROM array_and_json_types
            WHERE jsonb_col @> '{"Some not existing key": "random value"}'
PGSQL
        )->execute();
        $this->assertSame(0, $found);

        $found = $db->createCommand(
            <<<PGSQL
            SELECT *
            FROM array_and_json_types
            WHERE jsonb_col @> '{"Solution date": "13.01.2011"}'
PGSQL
        )->execute();
        $this->assertSame(1, $found);


        $this->assertSame(1, $db->createCommand()->delete('array_and_json_types')->execute());
    }

    #[DataProviderExternal(CommandProvider::class, 'alterColumn')]
    public function testAlterColumn(string $startColumn, array $setupSql, string|Closure $type, array $expected): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['alter_column']);

        foreach ($setupSql as $sql) {
            $db->createCommand($sql)->execute();
        }

        $command = $db->createCommand();

        $command->createTable(
            'alter_column',
            ['id' => 'pk', 'bar' => $startColumn],
        )->execute();

        if ($type instanceof Closure) {
            $type = $type($db);
        }

        $result = $command->alterColumn(
            'alter_column',
            'bar',
            $type,
        )->execute();

        self::assertSame(
            0,
            $result,
            'DDL must report zero affected rows.',
        );

        if (!empty($expected['repeatable'])) {
            $command->alterColumn(
                'alter_column',
                'bar',
                $type,
            )->execute();
        }

        if (isset($expected['type'])) {
            DbHelper::assertColumnType(
                $db,
                'alter_column',
                'bar',
                $expected['type'],
            );
        }

        if (isset($expected['dbType'])) {
            DbHelper::assertColumnDbType(
                $db,
                'alter_column',
                'bar',
                $expected['dbType'],
            );
        }

        if (isset($expected['allowNull'])) {
            DbHelper::assertColumnAllowNull(
                $db,
                'alter_column',
                'bar',
                $expected['allowNull'],
            );
        }

        if (array_key_exists('defaultValue', $expected)) {
            DbHelper::assertColumnDefaultValue(
                $db,
                'alter_column',
                'bar',
                $expected['defaultValue'],
            );
        }

        if (isset($expected['defaultValueContains'])) {
            DbHelper::assertColumnDefaultValueContains(
                $db,
                'alter_column',
                'bar',
                $expected['defaultValueContains'],
            );
        }

        if (isset($expected['checkContains'])) {
            DbHelper::assertCheckConstraintContains(
                $db,
                'alter_column',
                $expected['checkContains'],
            );
        }

        if (isset($expected['uniqueColumns'])) {
            DbHelper::assertSingleUniqueConstraintCovers(
                $db,
                'alter_column',
                $expected['uniqueColumns'],
            );
        }

        DbHelper::dropTablesIfExist($db, ['alter_column']);
    }

    #[DataProviderExternal(CommandProvider::class, 'alterColumnFailing')]
    public function testThrowExceptionForAlterColumnTypeStringThatIsNotAnAction(
        string $type,
        string $exceptionMessage,
    ): void {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['alter_column']);

        $command = $db->createCommand();

        $command->createTable(
            'alter_column',
            ['id' => 'pk', 'bar' => 'varchar(100)'],
        )->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            $exceptionMessage,
        );

        $command->alterColumn(
            'alter_column',
            'bar',
            $type,
        )->execute();
    }
}
