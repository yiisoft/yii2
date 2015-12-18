<?php
namespace yiiunit\framework\db\sqlite;

use yii\db\Query;
use yiiunit\framework\db\QueryTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteQueryTest extends QueryTest
{
    protected $driverName = 'sqlite';

    public function testUnion()
    {
        $connection = $this->getConnection();
        $query = new Query;
        $query->select(['id', 'name'])
            ->from('item')
            ->union(
                (new Query())
                    ->select(['id', 'name'])
                    ->from(['category'])
            );
        $result = $query->all($connection);
        $this->assertNotEmpty($result);
        $this->assertSame(7, count($result));
    }
}
