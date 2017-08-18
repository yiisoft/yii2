<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\db\Query;

/**
 * @group db
 * @group pgsql
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{
    public $driverName = 'pgsql';

    public function testBooleanValues()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert('bool_values',
            ['bool_col'], [
                [true],
                [false],
            ]
        )->execute();

        $this->assertSame(1, (new Query())->from('bool_values')->where('bool_col = TRUE')->count('*', $db));
        $this->assertSame(1, (new Query())->from('bool_values')->where('bool_col = FALSE')->count('*', $db));
        $this->assertSame(2, (new Query())->from('bool_values')->where('bool_col IN (TRUE, FALSE)')->count('*', $db));

        $this->assertSame(1, (new Query())->from('bool_values')->where(['bool_col' => true])->count('*', $db));
        $this->assertSame(1, (new Query())->from('bool_values')->where(['bool_col' => false])->count('*', $db));
        $this->assertSame(2, (new Query())->from('bool_values')->where(['bool_col' => [true, false]])->count('*', $db));

        $this->assertSame(1, (new Query())->from('bool_values')->where('bool_col = :bool_col', ['bool_col' => true])->count('*', $db));
        $this->assertSame(1, (new Query())->from('bool_values')->where('bool_col = :bool_col', ['bool_col' => false])->count('*', $db));
    }
}
