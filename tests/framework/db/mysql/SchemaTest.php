<?php

namespace yiiunit\framework\db\mysql;

use yiiunit\framework\db\AnyCaseValue;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'mysql';

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2] = [];

        $result['2: primary key'][2]->name = null;

        // Work aroung bug in MySQL 5.1 - it creates only this table in lowercase. O_o
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');
        return $result;
    }
}
