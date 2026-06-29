<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\mssql\RowVersionBehavior;
use yii\db\StaleObjectException;
use yiiunit\data\ar\Document;
use yiiunit\data\ar\OptimisticRowVersion;
use yiiunit\data\ar\TestTrigger;
use yiiunit\data\ar\TestTriggerAlert;
use yiiunit\data\ar\Type;
use yiiunit\base\db\BaseActiveRecord;
use yiiunit\support\DbHelper;

/**
 * @group db
 * @group mssql
 */
class ActiveRecordTest extends BaseActiveRecord
{
    public $driverName = 'sqlsrv';
    protected static string $driverNameStatic = 'sqlsrv';

    /**
     * MSSQL rejects an explicit value for an `IDENTITY` column while `IDENTITY_INSERT` is `OFF`.
     */
    public function testExplicitPkOnAutoIncrement(): void
    {
        $customerClass = $this->getCustomerClass();

        $customer = new $customerClass();

        $customer->id = 1337;
        $customer->email = 'user1337@example.com';
        $customer->name = 'user1337';
        $customer->address = 'address1337';

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessage('Cannot insert explicit value for identity column');

        $customer->save();
    }

    public function testCastValues(): void
    {
        $model = new Type();
        $model->int_col = 123;
        $model->int_col2 = 456;
        $model->smallint_col = 42;
        $model->char_col = '1337';
        $model->char_col2 = 'test';
        $model->char_col3 = 'test123';
        $model->float_col = 3.742;
        $model->float_col2 = 42.1337;
        $model->bool_col = true;
        $model->bool_col2 = false;
        $model->save(false);

        /** @var Type $model */
        $model = Type::find()->one();
        $this->assertSame(123, $model->int_col);
        $this->assertSame(456, $model->int_col2);
        $this->assertSame(42, $model->smallint_col);
        $this->assertSame('1337', trim((string) $model->char_col));
        $this->assertSame('test', $model->char_col2);
        $this->assertSame('test123', $model->char_col3);
        $this->assertSame('3.742', $model->float_col);
        $this->assertSame(42.1337, $model->float_col2);
        $this->assertSame(1, $model->bool_col);
        $this->assertSame(0, $model->bool_col2);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithTrigger(): void
    {
        $db = $this->getConnection();

        // drop trigger if exist
        $sql = 'IF (OBJECT_ID(N\'[dbo].[test_alert]\') IS NOT NULL)
BEGIN
      DROP TRIGGER [dbo].[test_alert];
END';
        $db->createCommand($sql)->execute();

        // create trigger
        $sql = 'CREATE TRIGGER [dbo].[test_alert] ON [dbo].[test_trigger]
AFTER INSERT
AS
BEGIN
    INSERT INTO [dbo].[test_trigger_alert] ( [stringcol] )
    SELECT [stringcol]
    FROM [inserted]
END';
        $db->createCommand($sql)->execute();

        $record = new TestTrigger();
        $record->stringcol = 'test';
        $this->assertTrue($record->save(false));
        $this->assertEquals(1, $record->id);

        $testRecord = TestTriggerAlert::findOne(1);
        $this->assertEquals('test', $testRecord->stringcol);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithComputedColumn(): void
    {
        $db = $this->getConnection();

        $sql = 'IF OBJECT_ID(\'TESTFUNC\') IS NOT NULL EXEC(\'DROP FUNCTION TESTFUNC\')';
        $db->createCommand($sql)->execute();

        $sql = 'CREATE FUNCTION TESTFUNC(@Number INT)
RETURNS VARCHAR(15)
AS
BEGIN
      RETURN (SELECT CONVERT(VARCHAR(15),@Number))
END';
        $db->createCommand($sql)->execute();

        $sql = 'ALTER TABLE [dbo].[test_trigger] ADD [computed_column] AS dbo.TESTFUNC([ID])';
        $db->createCommand($sql)->execute();

        $record = new TestTrigger();
        $record->stringcol = 'test';
        $this->assertTrue($record->save(false));
        $this->assertEquals(1, $record->id);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithRowVersionColumn(): void
    {
        $db = $this->getConnection();

        $sql = 'ALTER TABLE [dbo].[test_trigger] ADD [RV] rowversion';
        $db->createCommand($sql)->execute();

        $record = new TestTrigger();
        $record->stringcol = 'test';
        $this->assertTrue($record->save(false));
        $this->assertEquals(1, $record->id);
        $this->assertEquals('test', $record->stringcol);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithRowVersionNullColumn(): void
    {
        $db = $this->getConnection();

        $sql = 'ALTER TABLE [dbo].[test_trigger] ADD [RV] rowversion NULL';
        $db->createCommand($sql)->execute();

        $record = new TestTrigger();
        $record->stringcol = 'test';
        $record->RV = new Expression('DEFAULT');
        $this->assertTrue($record->save(false));
        $this->assertEquals(1, $record->id);
        $this->assertEquals('test', $record->stringcol);
    }

    public function testOptimisticRowVersionUpdateSucceeds(): void
    {
        $this->createOptimisticRowVersionTable();

        $record = new OptimisticRowVersion();

        $record->name = 'initial';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );

        $id = $record->id;

        // Rowversion is server-managed and not returned by the INSERT path; fetch to read its initial value.
        /** @var OptimisticRowVersion $fetched */
        $fetched = OptimisticRowVersion::findOne($id);

        $rvAfterInsert = $fetched->rv;

        self::assertIsInt(
            $rvAfterInsert,
            'Rowversion must load as an integer token.',
        );

        $fetched->name = 'updated';

        self::assertTrue(
            $fetched->save(false),
            'UPDATE must not throw.',
        );

        // The server bumps the rowversion; a fresh read confirms the change was persisted and advanced.
        /** @var OptimisticRowVersion $reloaded */

        $reloaded = OptimisticRowVersion::findOne($id);

        self::assertSame(
            'updated',
            $reloaded->name,
            'New value must be persisted.',
        );
        self::assertGreaterThan(
            $rvAfterInsert,
            $reloaded->rv,
            'Persisted rowversion must advance.',
        );

        DbHelper::dropTablesIfExist($this->getConnection(), ['test_optimistic_rowversion']);
    }

    public function testOptimisticRowVersionDeleteSucceeds(): void
    {
        $this->createOptimisticRowVersionTable();

        $record = new OptimisticRowVersion();

        $record->name = 'to-delete';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );

        $id = $record->id;

        /** @var OptimisticRowVersion $fetched */
        $fetched = OptimisticRowVersion::findOne($id);

        self::assertSame(
            1,
            $fetched->delete(),
            'Exactly one row must be deleted.',
        );
        self::assertNull(
            OptimisticRowVersion::findOne($id),
            'Record must be absent after DELETE.',
        );

        DbHelper::dropTablesIfExist($this->getConnection(), ['test_optimistic_rowversion']);
    }

    public function testThrowStaleObjectExceptionWhenRowVersionConflictsOnUpdate(): void
    {
        $this->createOptimisticRowVersionTable();

        $record = new OptimisticRowVersion();

        $record->name = 'shared';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );

        $id = $record->id;

        /** @var OptimisticRowVersion $record1 */
        $record1 = OptimisticRowVersion::findOne($id);
        /** @var OptimisticRowVersion $record2 */
        $record2 = OptimisticRowVersion::findOne($id);

        $record1->name = 'copy1';

        self::assertTrue(
            $record1->save(false),
            'First UPDATE must succeed.',
        );

        $this->expectException(StaleObjectException::class);
        $this->expectExceptionMessage(
            'The object being updated is outdated.',
        );

        $record2->name = 'copy2';

        $record2->save(false);

        DbHelper::dropTablesIfExist($this->getConnection(), ['test_optimistic_rowversion']);
    }

    public function testThrowStaleObjectExceptionWhenRowVersionConflictsOnDelete(): void
    {
        $this->createOptimisticRowVersionTable();

        $record = new OptimisticRowVersion();

        $record->name = 'shared';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );

        $id = $record->id;

        /** @var OptimisticRowVersion $record1 */
        $record1 = OptimisticRowVersion::findOne($id);
        /** @var OptimisticRowVersion $record2 */
        $record2 = OptimisticRowVersion::findOne($id);

        $record1->name = 'updated-before-delete';

        self::assertTrue(
            $record1->save(false),
            'UPDATE must succeed before stale delete attempt.',
        );

        $this->expectException(StaleObjectException::class);
        $this->expectExceptionMessage(
            'The object being deleted is outdated.',
        );

        $record2->delete();

        DbHelper::dropTablesIfExist($this->getConnection(), ['test_optimistic_rowversion']);
    }

    public function testOptimisticRowVersionInConditionCastsEachToken(): void
    {
        $this->createOptimisticRowVersionTable();

        $ids = [];

        foreach (['a', 'b', 'c'] as $name) {
            $record = new OptimisticRowVersion();

            $record->name = $name;

            $record->save(false);

            $ids[$name] = $record->id;
        }

        $record1 = OptimisticRowVersion::findOne($ids['a'])->rv;
        $record2 = OptimisticRowVersion::findOne($ids['b'])->rv;

        // An array hash value builds an `IN` condition; each `rowversion` token must be cast individually.
        $deleted = OptimisticRowVersion::deleteAll(['rv' => [$record1, $record2]]);

        self::assertSame(
            2,
            $deleted,
            'Both tokens in the `IN` list must match.',
        );
        self::assertNull(
            OptimisticRowVersion::findOne($ids['a']),
            'First listed row must be deleted.',
        );
        self::assertNull(
            OptimisticRowVersion::findOne($ids['b']),
            'Second listed row must be deleted.',
        );
        self::assertNotNull(
            OptimisticRowVersion::findOne($ids['c']),
            'Unlisted row must survive.',
        );

        DbHelper::dropTablesIfExist($this->getConnection(), ['test_optimistic_rowversion']);
    }

    /**
     * Documents the `rowversion` refresh contract: SQL Server regenerates the token server-side, so the in-memory value
     * is a guess after a save; reloading the record yields the authoritative token and lets the next save succeed.
     */
    public function testOptimisticRowVersionRepeatedSaveSucceedsAfterReload(): void
    {
        $this->createOptimisticRowVersionTable();

        $db = $this->getConnection();

        $record = new OptimisticRowVersion();

        $record->name = 'initial';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );

        $id = $record->id;

        // Unrelated write advances the database-wide rowversion counter, so the next token is not `old + 1`.
        $db->createCommand(
            <<<SQL
            INSERT INTO [dbo].[test_optimistic_rowversion] ([name]) VALUES ('other')
            SQL
        )->execute();

        /** @var OptimisticRowVersion $first */
        $first = OptimisticRowVersion::findOne($id);

        $first->name = 'first-update';

        self::assertTrue(
            $first->save(false),
            'First UPDATE must succeed.',
        );

        // The server regenerated the rowversion; reload to pick up the authoritative token before saving again.
        /** @var OptimisticRowVersion $reloaded */
        $reloaded = OptimisticRowVersion::findOne($id);

        $reloaded->name = 'second-update';

        self::assertTrue(
            $reloaded->save(false),
            'UPDATE on the reloaded instance must not raise a stale conflict.',
        );
        self::assertSame(
            'second-update',
            OptimisticRowVersion::findOne($id)->name,
            'Last write must be persisted.',
        );

        DbHelper::dropTablesIfExist($db, ['test_optimistic_rowversion']);
    }

    public function testRowVersionBehaviorRefreshesTokenForRepeatedSaveOnSameInstance(): void
    {
        $this->createOptimisticRowVersionTable();

        $db = $this->getConnection();

        $record = new OptimisticRowVersion();

        $record->attachBehavior('rowVersion', new RowVersionBehavior());

        $record->name = 'a';

        self::assertTrue(
            $record->save(false),
            'INSERT must succeed.',
        );
        self::assertIsInt(
            $record->rv,
            'Token must be loaded after INSERT.',
        );

        // Unrelated write advances the database-wide counter, so `old + 1` would diverge from the real token.
        $db->createCommand(
            <<<SQL
            INSERT INTO [dbo].[test_optimistic_rowversion] ([name]) VALUES ('other')
            SQL
        )->execute();

        $record->name = 'b';

        self::assertTrue(
            $record->save(false),
            'UPDATE on the same instance must succeed without a reload.',
        );

        $record->name = 'c';

        self::assertTrue(
            $record->save(false),
            'Repeated UPDATE on the same instance must keep succeeding.',
        );
        self::assertSame(
            'c',
            OptimisticRowVersion::findOne($record->id)->name,
            'Last write must be persisted.',
        );

        DbHelper::dropTablesIfExist($db, ['test_optimistic_rowversion']);
    }

    public function testRowVersionBehaviorIsInertWhenOptimisticLockIsDisabled(): void
    {
        $type = new Type();

        $behavior = new RowVersionBehavior();

        $behavior->attach($type);
        $behavior->refreshRowVersion();

        self::assertNull(
            $type->optimisticLock(),
            'Behavior must be inert when optimistic locking is disabled.',
        );
    }

    public function testRowVersionBehaviorIsInertForNonRowVersionLockColumn(): void
    {
        $document = new Document();

        $document->version = 5;

        $behavior = new RowVersionBehavior();

        $behavior->attach($document);
        $behavior->refreshRowVersion();

        self::assertSame(
            5,
            $document->version,
            'Non-rowversion lock column must be left untouched.',
        );
    }

    public function testRowVersionBehaviorSkipsRefreshWhenRowIsAbsent(): void
    {
        $this->createOptimisticRowVersionTable();

        $db = $this->getConnection();

        $record = new OptimisticRowVersion();

        $record->name = 'gone';

        $record->save(false);

        $id = $record->id;

        /** @var OptimisticRowVersion $loaded */
        $loaded = OptimisticRowVersion::findOne($id);

        $rvBefore = $loaded->rv;

        // Remove the row so the refresh query returns no value.
        $db->createCommand()->delete('test_optimistic_rowversion', ['id' => $id])->execute();

        $behavior = new RowVersionBehavior();

        $behavior->attach($loaded);
        $behavior->refreshRowVersion();

        self::assertSame(
            $rvBefore,
            $loaded->rv,
            'In-memory token must be left untouched when the row is absent.',
        );

        DbHelper::dropTablesIfExist($db, ['test_optimistic_rowversion']);
    }

    private function createOptimisticRowVersionTable(): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['test_optimistic_rowversion']);

        $db->createCommand(
            <<<SQL
            CREATE TABLE [dbo].[test_optimistic_rowversion] (
                [id]   INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
                [name] NVARCHAR(128)     NOT NULL,
                [rv]   ROWVERSION        NOT NULL
            )
            SQL,
        )->execute();

        // Flush the cached `null` entry so `getTableSchema()` picks up the newly created table.
        $db->getSchema()->refreshTableSchema('test_optimistic_rowversion');
    }
}
