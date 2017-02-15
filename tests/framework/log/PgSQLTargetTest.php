<?php


namespace yiiunit\framework\log;


/**
 * @group db
 * @group pgsql
 * @group log
 */
class PgSQLTargetTest extends DbTargetTest
{
    protected static $driverName = 'pgsql';
}