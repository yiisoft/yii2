<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use Throwable;
use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Schema;
use yiiunit\base\db\BaseCommand;

use function count;

/**
 * Unit test for {@see \yii\db\Command} with Oracle driver.
 */
#[Group('db')]
#[Group('oci')]
#[Group('command')]
class CommandTest extends BaseCommand
{
    protected $driverName = 'oci';

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testBatchInsert(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            ['name'],
            [
                ['t1'],
                ['t2'],
            ]
        );

        self::assertSame(
            2,
            $command->execute(),
            'Two rows must be inserted via the trigger-backed batch path.',
        );

        $ids = $db->createCommand(
            <<<SQL
            SELECT "id" FROM "legacy_identity_via_trigger" WHERE "name" IN ('t1', 't2')
            SQL
        )->queryColumn();

        self::assertCount(
            2,
            $ids,
            'Trigger-backed batch insert must produce two rows.',
        );
        self::assertNotContains(
            null,
            $ids,
            'Trigger must populate the PK for every batched row.',
        );

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            ['name'],
            []
        );

        self::assertSame(
            0,
            $command->execute(),
            'Empty batch must execute as a no-op.',
        );
    }

    public function testBatchInsertExecutesAgainstIdentityTableWithoutCollisionRegressionForORA00001(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            [
                ['identity-batch-1@example.com', 'identity-batch-1', 'address-1'],
                ['identity-batch-2@example.com', 'identity-batch-2', 'address-2'],
                ['identity-batch-3@example.com', 'identity-batch-3', 'address-3'],
            ]
        );

        self::assertSame(
            3,
            $command->execute(),
            'batchInsert into IDENTITY table must execute without `ORA-00001`.',
        );

        $ids = $db->createCommand(
            <<<SQL
            SELECT "id" FROM "customer" WHERE "email" LIKE 'identity-batch-%@example.com' ORDER BY "id"
            SQL
        )->queryColumn();

        self::assertSame(
            ['4', '5', '6'],
            $ids,
            'IDENTITY rows must receive ids 4, 5, 6 (fixture inserts ids 1-3; counter advances from there).',
        );

        $command->delete(
            'customer',
            [
                'email' => [
                    'identity-batch-1@example.com',
                    'identity-batch-2@example.com',
                    'identity-batch-3@example.com',
                ],
            ],
        )->execute();
    }

    public function testBatchInsertExecutesAsNoOpForSingleEmptyRow(): void
    {
        $command = $this->getConnection()->createCommand();

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            ['name'],
            [[]],
        );

        self::assertSame(
            0,
            $command->execute(),
            'A single empty row must execute as a no-op (no SQL emitted).',
        );
    }

    public function testBatchInsertExecutesSkippingEmptyRowAtBatchStart(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            ['name'],
            [
                [],
                ['skip-start-row'],
            ],
        );

        self::assertSame(
            1,
            $command->execute(),
            'Empty row at batch start must be skipped; the trailing non-empty row must be persisted.',
        );

        $rows = $db->createCommand(
            <<<SQL
            SELECT "name" FROM "legacy_identity_via_trigger" WHERE "name" = 'skip-start-row'
            SQL
        )->queryColumn();

        self::assertCount(
            1,
            $rows,
            'Only the non-empty row must persist.',
        );

        $command->delete(
            'legacy_identity_via_trigger',
            ['name' => 'skip-start-row'],
        )->execute();
    }

    public function testBatchInsertExecutesSkippingInterleavedEmptyRowsForIdentityTable(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            [
                ['skip-edge-1@example.com', 'skip-edge-1', 'addr-1'],
                [],
                [],
                ['skip-edge-2@example.com', 'skip-edge-2', 'addr-2'],
            ]
        );

        self::assertSame(
            2,
            $command->execute(),
            'Interleaved empty rows must be skipped on the IDENTITY path.',
        );

        $ids = $db->createCommand(
            <<<SQL
            SELECT "id" FROM "customer"
            WHERE "email" IN ('skip-edge-1@example.com', 'skip-edge-2@example.com')
            ORDER BY "id"
            SQL
        )->queryColumn();

        self::assertSame(
            ['4', '5'],
            $ids,
            'IDENTITY rows must receive ids 4 and 5 (fixture inserts ids 1-3; counter advances from there).',
        );

        $command->delete(
            'customer',
            [
                'email' => [
                    'skip-edge-1@example.com',
                    'skip-edge-2@example.com',
                ],
            ],
        )->execute();
    }

    /**
     * Same as the IDENTITY case but exercising the legacy trigger-backed path.
     */
    public function testBatchInsertExecutesSkippingInterleavedEmptyRowsForLegacyTriggerBackedTable(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            ['name'],
            [
                ['skip-edge-a'],
                [],
                [],
                ['skip-edge-b'],
            ]
        );

        self::assertSame(
            2,
            $command->execute(),
            'Interleaved empty rows must be skipped on the legacy trigger-backed path.',
        );

        $rows = $db->createCommand(
            <<<SQL
            SELECT "name" FROM "legacy_identity_via_trigger" WHERE "name" IN ('skip-edge-a', 'skip-edge-b')
            SQL
        )->queryColumn();

        self::assertCount(
            2,
            $rows,
            'Only the non-empty rows must persist.',
        );

        $command->delete(
            'legacy_identity_via_trigger',
            [
                'name' => [
                    'skip-edge-a',
                    'skip-edge-b',
                ],
            ],
        )->execute();
    }

    public function testBatchInsertExecutesWithEmptyColumnList(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->batchInsert(
            '{{legacy_identity_via_trigger}}',
            [],
            [
                [null, 'empty-cols-row-1'],
                [null, 'empty-cols-row-2'],
            ]
        );

        self::assertSame(
            2,
            $command->execute(),
            'batchInsert with empty columns must execute without `ORA-03050`.',
        );

        $rows = $db->createCommand(
            <<<SQL
            SELECT "name" FROM "legacy_identity_via_trigger" WHERE "name" IN ('empty-cols-row-1', 'empty-cols-row-2')
            SQL
        )->queryColumn();

        self::assertCount(
            2,
            $rows,
            'Both empty-columns rows must be persisted.',
        );

        $command->delete(
            'legacy_identity_via_trigger',
            [
                'name' => [
                    'empty-cols-row-1',
                    'empty-cols-row-2',
                ],
            ],
        )->execute();
    }

    public function testLastInsertId(): void
    {
        $db = $this->getConnection();

        $table = $db->getSchema()->getTableSchema('profile');

        self::assertNotNull(
            $table,
            "IDENTITY-backed 'profile' fixture table must be loadable.",
        );

        $sequenceName = $table->sequenceName;

        self::assertNotNull(
            $sequenceName,
            "IDENTITY-backed 'profile' table must surface its system sequence.",
        );
        self::assertStringStartsWith(
            'ISEQ$$_',
            (string) $sequenceName,
            'IDENTITY-backed sequence must use the Oracle `ISEQ$$_` system prefix.',
        );

        $db->createCommand(
            <<<SQL
            INSERT INTO {{profile}} ([[description]]) VALUES ('lastInsertId-row')
            SQL
        )->execute();

        self::assertNotEmpty(
            $db->getSchema()->getLastInsertID($sequenceName),
            'CURRVAL of the IDENTITY system sequence must reflect the inserted row.',
        );
    }

    public function testGetLastInsertIDReturnsValueViaLegacyTriggerBackedSequence(): void
    {
        $db = $this->getConnection();

        $table = $db->getSchema()->getTableSchema('legacy_identity_via_trigger');

        self::assertNotNull(
            $table,
            "Legacy 'legacy_identity_via_trigger' fixture table must be loadable.",
        );

        $sequenceName = $table->sequenceName;

        self::assertSame(
            'legacy_identity_via_trigger_SEQ',
            $sequenceName,
            'Legacy fallback must surface the trigger-referenced sequence name.',
        );

        $db->createCommand(
            <<<SQL
            INSERT INTO {{legacy_identity_via_trigger}} ([[name]]) VALUES ('legacy-row')
            SQL
        )->execute();

        self::assertNotEmpty(
            $db->getSchema()->getLastInsertID($sequenceName),
            'Legacy CURRVAL lookup must return the trigger-populated PK.',
        );
    }

    public static function batchInsertSqlProvider(): array
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO "type" ("int_col", "float_col", "char_col") ' .
            "SELECT NULL, NULL, 'Kyiv {{city}}, Ukraine' FROM SYS.DUAL";
        $data['wrongBehavior']['expected'] = 'INSERT INTO "type" ("type"."int_col", "float_col", "char_col") ' .
            "SELECT '', '', 'Kyiv {{city}}, Ukraine' FROM SYS.DUAL";
        $data['batchInsert binds params from expression']['expected'] = 'INSERT INTO "type" ("int_col") ' .
            'SELECT :qp1 FROM SYS.DUAL';

        return $data;
    }

    /**
     * Testing the "ORA-01461: can bind a LONG value only for insert into a LONG column"
     *
     * @return void
     */
    public function testCLOBStringInsertion(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('longstring') !== null) {
            $db->createCommand()->dropTable('longstring')->execute();
        }

        $db->createCommand()->createTable('longstring', ['message' => Schema::TYPE_TEXT])->execute();

        $longData = str_pad('-', 4001, '-=', STR_PAD_LEFT);
        $db->createCommand()->insert('longstring', [
            'message' => $longData,
        ])->execute();

        $this->assertEquals(1, $db->createCommand('SELECT count(*) FROM {{longstring}}')->queryScalar());

        $db->createCommand()->dropTable('longstring')->execute();
    }

    public function testQueryCache(): void
    {
        $db = $this->getConnection(true);

        $db->enableQueryCache = true;
        $db->queryCache = new ArrayCache();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $update = $db->createCommand('UPDATE {{customer}} SET [[name]] = :name WHERE [[id]] = :id');
        $update->bindValues([':id' => 1, ':name' => 'user11'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($update): void {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user22'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $db->noCache(function () use ($db): void {
                $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

                $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());
            });

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = false;

        $db->cache(function (Connection $db) use ($update): void {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user2'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = true;

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $update->bindValues([':id' => 1, ':name' => 'user1'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db): void {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

            $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());
        }, 10);
    }

    public static function paramsNonWhereProvider(): array
    {
        return [
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email GROUP BY SUBSTR([[name]], :len)'],
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email ORDER BY SUBSTR([[name]], :len)'],
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email'],
        ];
    }

    public function testInsert(): void
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());
        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryOne();
        $this->assertEquals([
            'email' => 't1@example.com',
            'name' => 'test',
            'address' => 'test address',
        ], $record);
    }

    /**
     * Test INSERT INTO ... SELECT SQL statement with alias syntax.
     */
    public function testInsertSelectAlias(): void
    {
        $db = $this->getConnection();

        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();

        $command->insert(
            '{{customer}}',
            [
                'email'   => 't1@example.com',
                'name'    => 'test',
                'address' => 'test address',
            ]
        )->execute();

        $query = $db->createCommand(
            "SELECT 't2@example.com' as [[email]], [[address]] as [[name]], [[name]] as [[address]] from {{customer}}"
        );

        $command->insert(
            '{{customer}}',
            $query->queryOne()
        )->execute();

        $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());

        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryAll();

        $this->assertEquals([
            [
                'email'   => 't1@example.com',
                'name'    => 'test',
                'address' => 'test address',
            ],
            [
                'email'   => 't2@example.com',
                'name'    => 'test address',
                'address' => 'test',
            ],
        ], $record);
    }

    /**
     * Test batch insert with different data types.
     *
     * Ensure double is inserted with `.` decimal separator.
     *
     * https://github.com/yiisoft/yii2/issues/6526
     */
    public function testBatchInsertDataTypesLocale(): void
    {
        $locale = setlocale(LC_NUMERIC, '0');
        if (false === $locale) {
            $this->markTestSkipped('Your platform does not support locales.');
        }
        $db = $this->getConnection();

        try {
            // This one sets decimal mark to comma sign
            setlocale(LC_NUMERIC, 'ru_RU.UTF-8');

            $cols = ['int_col', 'char_col', 'float_col', 'bool_col'];
            $data = [
                [1, 'A', 9.735, '1'],
                [2, 'B', -2.123, '0'],
                [3, 'C', 2.123, '0'],
            ];

            // clear data in "type" table
            $db->createCommand()->delete('type')->execute();
            // batch insert on "type" table
            $db->createCommand()->batchInsert('type', $cols, $data)->execute();

            // change , for point oracle.
            $db->createCommand("ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'")->execute();

            $data = $db->createCommand(
                'SELECT [[int_col]], [[char_col]], [[float_col]], [[bool_col]] FROM {{type}} WHERE [[int_col]] ' .
                'IN (1,2,3) ORDER BY [[int_col]]'
            )->queryAll();

            $this->assertEquals(3, count($data));
            $this->assertEquals(1, $data[0]['int_col']);
            $this->assertEquals(2, $data[1]['int_col']);
            $this->assertEquals(3, $data[2]['int_col']);
            $this->assertEquals('A', rtrim((string) $data[0]['char_col'])); // rtrim because Postgres padds the column with whitespace
            $this->assertEquals('B', rtrim((string) $data[1]['char_col']));
            $this->assertEquals('C', rtrim((string) $data[2]['char_col']));
            $this->assertEquals('9.735', $data[0]['float_col']);
            $this->assertEquals('-2.123', $data[1]['float_col']);
            $this->assertEquals('2.123', $data[2]['float_col']);
            $this->assertEquals('1', $data[0]['bool_col']);
            $this->assertIsOneOf($data[1]['bool_col'], ['0', false]);
            $this->assertIsOneOf($data[2]['bool_col'], ['0', false]);
        } catch (Exception $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        } catch (Throwable $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        }
        setlocale(LC_NUMERIC, $locale);
    }

    /**
     * verify that {{}} are not going to be replaced in parameters.
     */
    public function testNoTablenameReplacement(): void
    {
        $db = $this->getConnection();

        $inserted = $db->getSchema()->insert(
            '{{customer}}',
            [
                'name' => 'Some {{weird}} name',
                'email' => 'test@example.com',
                'address' => 'Some {{%weird}} address',
            ],
        );

        self::assertIsArray(
            $inserted,
            'Insert must return the row data array.',
        );

        $customerId = $inserted['id'];

        $customer = $db->createCommand(
            <<<SQL
            SELECT * FROM {{customer}} WHERE [[id]] = $customerId
            SQL,
        )->queryOne();

        self::assertEquals(
            'Some {{weird}} name',
            $customer['name'],
            'Double curly braces must not be replaced in parameter values.',
        );
        self::assertEquals(
            'Some {{%weird}} address',
            $customer['address'],
            'Double curly braces must not be replaced in parameter values.',
        );

        $db->createCommand()->update(
            '{{customer}}',
            [
                'name' => 'Some {{updated}} name',
                'address' => 'Some {{%updated}} address',
            ],
            ['id' => $customerId],
        )->execute();

        $customer = $db->createCommand(
            <<<SQL
            SELECT * FROM {{customer}} WHERE [[id]] = $customerId
            SQL,
        )->queryOne();

        self::assertEquals(
            'Some {{updated}} name',
            $customer['name'],
            'Double curly braces must not be replaced in parameter values.',
        );
        self::assertEquals(
            'Some {{%updated}} address',
            $customer['address'],
            'Double curly braces must not be replaced in parameter values.',
        );
    }

    public function testCreateTable(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testCreateTable') !== null) {
            $db->createCommand('DROP SEQUENCE testCreateTable_SEQ')->execute();
            $db->createCommand()->dropTable('testCreateTable')->execute();
        }

        $db->createCommand()->createTable(
            '{{testCreateTable}}',
            ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER]
        )->execute();

        $db->createCommand('CREATE SEQUENCE testCreateTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}}')->queryAll();

        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testsInsertQueryAsColumnValue(): void
    {
        $db = $this->getConnection();

        $time = time();

        $db->createCommand(
            <<<SQL
            DELETE FROM {{order_with_null_fk}}
            SQL,
        )->execute();

        $inserted = $db->getSchema()->insert(
            '{{order}}',
            [
                'customer_id' => 1,
                'created_at' => $time,
                'total' => 42,
            ],
        );

        self::assertIsArray(
            $inserted,
            'Insert must return the row data array.',
        );

        $orderId = $inserted['id'];

        $columnValueQuery = new Query();

        $columnValueQuery->select('created_at')->from('{{order}}')->where(['id' => $orderId]);

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'customer_id' => $orderId,
                'created_at' => $columnValueQuery,
                'total' => 42,
            ],
        )->execute();

        self::assertEquals(
            $time,
            $db->createCommand(
                <<<SQL
                SELECT [[created_at]] FROM {{order_with_null_fk}} WHERE [[customer_id]] = $orderId
                SQL,
            )->queryScalar()
        );

        $db->createCommand(
            <<<SQL
            DELETE FROM {{order_with_null_fk}}
            SQL,
        )->execute();
        $db->createCommand(
            <<<SQL
            DELETE FROM {{order}} WHERE [[id]] = $orderId
            SQL,
        )->execute();
    }

    public function testAlterTable(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testAlterTable') !== null) {
            $db->createCommand('DROP SEQUENCE testAlterTable_SEQ')->execute();
            $db->createCommand()->dropTable('testAlterTable')->execute();
        }

        $db->createCommand()->createTable(
            'testAlterTable',
            ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER]
        )->execute();

        $db->createCommand('CREATE SEQUENCE testAlterTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testAlterTable}} ([[id]], [[bar]]) VALUES(testAlterTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} ADD ([[bar_tmp]] VARCHAR(20))')->execute();

        $db->createCommand('UPDATE {{testAlterTable}} SET [[bar_tmp]] = [[bar]]')->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} DROP COLUMN [[bar]]')->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} RENAME COLUMN [[bar_tmp]] TO [[bar]]')->execute();

        $db->createCommand(
            "INSERT INTO {{testAlterTable}} ([[id]], [[bar]]) VALUES(testAlterTable_SEQ.NEXTVAL, 'hello')"
        )->execute();

        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}}')->queryAll();

        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }

    public function testCreateView(): void
    {
        $db = $this->getConnection();

        $subquery = (new Query())
            ->select('bar')
            ->from('testCreateViewTable')
            ->where(['>', 'bar', '5']);

        if ($db->getSchema()->getTableSchema('testCreateView') !== null) {
            $db->createCommand()->dropView('testCreateView')->execute();
        }

        if ($db->getSchema()->getTableSchema('testCreateViewTable')) {
            $db->createCommand('DROP SEQUENCE testCreateViewTable_SEQ')->execute();
            $db->createCommand()->dropTable('testCreateViewTable')->execute();
        }

        $db->createCommand()->createTable('testCreateViewTable', [
            'id'  => Schema::TYPE_PK,
            'bar' => Schema::TYPE_INTEGER,
        ])->execute();

        $db->createCommand('CREATE SEQUENCE testCreateViewTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateViewTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateViewTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 6)'
        )->execute();

        $db->createCommand()->createView('testCreateView', $subquery)->execute();

        $records = $db->createCommand('SELECT [[bar]] FROM {{testCreateView}}')->queryAll();

        $this->assertEquals([['bar' => 6]], $records);
    }

    public function testColumnCase(): void
    {
        $this->markTestSkipped('Should be fixed.');
    }
}
