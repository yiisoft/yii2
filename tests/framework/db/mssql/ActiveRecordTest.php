<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

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
     * @throws \yii\db\Exception
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
}
