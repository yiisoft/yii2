<?php

namespace yiiunit\framework\db\mysql;

/**
 * @group db
 * @group mysql
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    protected $driverName = 'mysql';


    /**
     * Test whether slave connection is recovered when call getSlavePdo() after close()
     *
     * @see https://github.com/yiisoft/yii2/issues/14165
     */
    public function testGetPdoAfterClose()
    {
        $connection = $this->getConnection();
        if (!empty($connection->slaves)){
            $connection->getSlavePdo(false);
        }
        $connection->close();

        $masterPdo = $connection->getMasterPdo();
        $this->assertNotFalse($masterPdo);
        $this->assertNotNull($masterPdo);

        if (!empty($connection->slaves)){
            $slavePdo = $connection->getSlavePdo(false);
            $this->assertNotFalse($slavePdo);
            $this->assertNotNull($slavePdo);
            $this->assertNotSame($masterPdo,$slavePdo);
        }
    }
}
