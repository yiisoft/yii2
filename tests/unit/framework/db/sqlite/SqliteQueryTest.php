<?php
/**
 * Created by JetBrains PhpStorm.
 * User: RusMaxim
 * Date: 09.05.13
 * Time: 21:41
 * To change this template use File | Settings | File Templates.
 */

namespace yiiunit\framework\db\sqlite;


class SqliteQueryTest extends \yiiunit\framework\db\QueryTest
{
    public function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }
}