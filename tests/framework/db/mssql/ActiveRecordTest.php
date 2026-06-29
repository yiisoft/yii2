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
use yii\db\StaleObjectException;
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
