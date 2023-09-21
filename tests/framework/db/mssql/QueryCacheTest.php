<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\caching\FileCache;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mssql
 */
class QueryCacheTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testQueryCacheFileCache()
    {
        $db = $this->getConnection();
        $db->enableQueryCache = true;
        $db->queryCache = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);

        $db->createCommand()->delete('type')->execute();
        $db->createCommand()->insert('type', [
            'int_col' => $key = 1,
            'char_col' => '',
            'char_col2' => '6a3ce1a0bffe8eeb6fa986caf443e24c',
            'float_col' => 0.0,
            'blob_col' => 'a:1:{s:13:"template";s:1:"1";}',
            'bool_col' => true,
        ])->execute();

        $function = function($db) use ($key){
            return (new Query())
                ->select(['blob_col'])
                ->from('type')
                ->where(['int_col' => $key])
                ->createCommand($db)
                ->queryScalar();
        };

        // First run return
        $result = $db->cache($function);
        $this->assertSame('a:1:{s:13:"template";s:1:"1";}', $result);

        // After the request has been cached return
        $result = $db->cache($function);
        $this->assertSame('a:1:{s:13:"template";s:1:"1";}', $result);
    }
}
