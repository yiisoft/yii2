<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\cubrid;

/**
 * @group db
 * @group cubrid
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    public $driverName = 'cubrid';

    public function testQuoteValue()
    {
        $connection = $this->getConnection(false);
        $this->assertSame(123, $connection->quoteValue(123));
        $this->assertSame("'string'", $connection->quoteValue('string'));
        $this->assertSame("'It''s interesting'", $connection->quoteValue("It's interesting"));
    }

    public function testQuoteTableName()
    {
        $connection = $this->getConnection(false);
        $this->assertSame('"table"', $connection->quoteTableName('table'));
        $this->assertSame('"table"', $connection->quoteTableName('"table"'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('schema.table'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('schema."table"'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('"schema"."table"'));
        $this->assertSame('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertSame('(table)', $connection->quoteTableName('(table)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false);
        $this->assertSame('"column"', $connection->quoteColumnName('column'));
        $this->assertSame('"column"', $connection->quoteColumnName('"column"'));
        $this->assertSame('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertSame('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertSame('(column)', $connection->quoteColumnName('(column)'));

        $this->assertSame('"column"', $connection->quoteSql('[[column]]'));
        $this->assertSame('"column"', $connection->quoteSql('{{column}}'));
    }

    public function testQuoteFullColumnName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertSame('"table"."column"', $connection->quoteColumnName('table.column'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('table."column"'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('"table".column'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('"table"."column"'));

        $this->assertSame('[[table.column]]', $connection->quoteColumnName('[[table.column]]'));
        $this->assertSame('{{table}}."column"', $connection->quoteColumnName('{{table}}.column'));
        $this->assertSame('{{table}}."column"', $connection->quoteColumnName('{{table}}."column"'));
        $this->assertSame('{{table}}.[[column]]', $connection->quoteColumnName('{{table}}.[[column]]'));
        $this->assertSame('{{%table}}."column"', $connection->quoteColumnName('{{%table}}.column'));
        $this->assertSame('{{%table}}."column"', $connection->quoteColumnName('{{%table}}."column"'));

        $this->assertSame('"table"."column"', $connection->quoteSql('[[table.column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{table}}.[[column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{table}}."column"'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{%table}}.[[column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{%table}}."column"'));
    }
}
