<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
        $object->binary = unpack('H*', $object->binary);
        $object->binary = base_convert($object->binary[1], 16, 2);

        return $object;
    }
}
