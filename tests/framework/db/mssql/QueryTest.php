<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\Query;

/**
 * @group db
 * @group mssql
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{
    protected $driverName = 'sqlsrv';

    public function testUnion()
    {
        $connection = $this->getConnection();

        // MSSQL supports limit only in sub queries with UNION
        $query = (new Query())
            ->select(['id', 'name'])
            ->from(
                (new Query())
                    ->select(['id', 'name'])
                    ->from('item')
                    ->limit(2)
            )
            ->union(
                (new Query())
                    ->select(['id', 'name'])
                    ->from(
                        (new Query())
                            ->select(['id', 'name'])
                            ->from(['category'])
                            ->limit(2)
                    )
            );

        $result = $query->all($connection);
        $this->assertNotEmpty($result);
        $this->assertCount(4, $result);
    }
}
