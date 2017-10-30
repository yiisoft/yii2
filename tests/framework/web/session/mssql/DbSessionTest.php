<?php

namespace yiiunit\framework\web\session\mssql;

/**
 * Class DbSessionTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group mssql
 */
class DbSessionTest extends \yiiunit\framework\web\session\AbstractDbSessionTest
{
    protected function getDriverNames()
    {
        return ['mssql', 'sqlsrv', 'dblib'];
    }
}
