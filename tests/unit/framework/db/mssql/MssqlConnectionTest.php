<?php

namespace yiiunit\framework\db\mssql;

class MssqlConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    public function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }

	function testQuoteValue()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
	}

	function testQuoteTableName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('[table]', $connection->quoteTableName('table'));
		$this->assertEquals('[table]', $connection->quoteTableName('[table]'));
		$this->assertEquals('[schema].[table]', $connection->quoteTableName('schema.table'));
		$this->assertEquals('[schema].[table]', $connection->quoteTableName('schema.[table]'));
		$this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
		$this->assertEquals('(table)', $connection->quoteTableName('(table)'));
	}

	function testQuoteColumnName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('[column]', $connection->quoteColumnName('column'));
		$this->assertEquals('[column]', $connection->quoteColumnName('[column]'));
		$this->assertEquals('[table].[column]', $connection->quoteColumnName('table.column'));
		$this->assertEquals('[table].[column]', $connection->quoteColumnName('table.[column]'));
		$this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
		$this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
		$this->assertEquals('(column)', $connection->quoteColumnName('(column)'));
	}
}
