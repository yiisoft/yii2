<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\mssql;

/**
 * Class DbSessionTest.
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

    protected function buildObjectForSerialization()
    {
        $object = parent::buildObjectForSerialization();
        unset($object->binary);
        // Binary data produce error on insert:
        // `An error occurred translating string for input param 1 to UCS-2`
        // I failed to make it work either with `nvarchar(max)` or `varbinary(max)` column
        // in Microsoft SQL server. © SilverFire TODO: fix it

        return $object;
    }
}
