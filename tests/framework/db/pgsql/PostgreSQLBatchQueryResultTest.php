<?php
namespace yii\tests\unit\framework\db\pgsql;

use yiiunit\framework\db\BatchQueryResultTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLBatchQueryResultTest extends BatchQueryResultTest
{
    public $driverName = 'pgsql';
}
