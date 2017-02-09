<?php


namespace yiiunit\framework\log;


/**
 * @group db
 * @group sqlite
 * @group log
 */
class SqliteTargetTest extends DbTargetTest
{
    protected static $driverName = 'sqlite';
}