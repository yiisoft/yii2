<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\type;

use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mssql
 */
class VarbinaryTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testVarbinary()
    {
        $db = $this->getConnection();

        $db->createCommand()->delete('type')->execute();
        $db->createCommand()->insert('type', [
            'int_col' => $key = 1,
            'char_col' => '',
            'char_col2' => '6a3ce1a0bffe8eeb6fa986caf443e24c',
            'float_col' => 0.0,
            'blob_col' => 'a:1:{s:13:"template";s:1:"1";}',
            'bool_col' => true,
        ])->execute();

        $result = (new Query())
            ->select(['blob_col'])
            ->from('type')
            ->where(['int_col' => $key])
            ->createCommand($db)
            ->queryScalar();

        $this->assertSame('a:1:{s:13:"template";s:1:"1";}', $result);
    }
}
