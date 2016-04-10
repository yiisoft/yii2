<?php

namespace yiiunit\framework\db\oci;

use yii\db\oci\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group oci
 */
class OracleQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'oci';

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL',
                $this->boolean()->notNull()->defaultValue(1),
                'NUMBER(1) DEFAULT 1 NOT NULL'
            ],
        ]);
    }
}
