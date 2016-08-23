<?php

namespace yiiunit\framework\db\cubrid;

use yii\db\Schema;

/**
 * @group db
 * @group cubrid
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'cubrid';

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), []);
    }
}
