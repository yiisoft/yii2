<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\Exception;
use yii\db\Expression;
use yiiunit\data\ar\TestTrigger;
use yiiunit\data\ar\TestTriggerAlert;

/**
 * @group db
 * @group mssql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public $driverName = 'sqlsrv';

    public function testExplicitPkOnAutoIncrement()
    {
        $this->markTestSkipped('MSSQL does not support explicit value for an IDENTITY column.');
    }

    /**
     * @throws Exception
     */
    public function testSaveWithTrigger()
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
    public function testSaveWithComputedColumn()
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
    public function testSaveWithRowVersionColumn()
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
    public function testSaveWithRowVersionNullColumn()
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
}
