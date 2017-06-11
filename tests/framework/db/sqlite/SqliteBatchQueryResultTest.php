<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\BatchQueryResultTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteBatchQueryResultTest extends BatchQueryResultTest
{
    protected $driverName = 'sqlite';
    public static $largeTableInsertBatch = 100;
}
