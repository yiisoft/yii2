<?php

namespace yiiunit\framework\db\pgsql;

use yiiunit\framework\db\ConnectionTest;

class PostgreSQLConnectionTest extends ConnectionTest
{
    public function setUp()
    {
        $this->driverName = 'pgsql';
        parent::setUp();
    }
    
    public function testConnection() {
        $connection = $this->getConnection(true);        
    }
}
