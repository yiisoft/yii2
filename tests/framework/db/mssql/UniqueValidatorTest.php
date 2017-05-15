<?php

namespace yiiunit\framework\db\mssql;

/**
 * @group db
 * @group mssql
 * @group validators
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{
    public $driverName = 'sqlsrv';
}
