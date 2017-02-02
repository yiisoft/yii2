<?php

namespace yiiunit\framework\db\mysql;
use yii\db\Expression;
use yii\db\Query;

/**
 * @group db
 * @group mysql
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{
    protected $driverName = 'mysql';

    /**
     * Tests MySQL specific syntax for index hints
     */
    public function testQueryIndexHint()
    {
        $db = $this->getConnection();

        $query = (new Query)->from([new Expression('{{%customer}} USE INDEX (primary)')]);
        $row = $query->one($db);
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('email', $row);
    }
}
