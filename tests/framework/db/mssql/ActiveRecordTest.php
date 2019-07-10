<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yiiunit\data\ar\Type;
use yiiunit\framework\db\cubrid\ActiveRecordTest as CubridActiveRecordTest;

/**
 * @group db
 * @group mssql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public $driverName = 'sqlsrv';

    public function testExplicitPkOnAutoIncrement()
    {
        $this->markTestSkipped('MSSQL does not support explicit value for an IDENTITY column.');
    }

    public function testDefaultValues()
    {
        $model = new Type();
        $model->loadDefaultValues();
        $this->assertEquals(0, $model->int_col2);
        $this->assertEquals("('something')", $model->char_col2);
        $this->assertEquals(0, $model->float_col2);
        $this->assertEquals("('33.22')", $model->numeric_col);
        $this->assertEquals(0, $model->bool_col2);

        if ($this instanceof CubridActiveRecordTest) {
            // cubrid has non-standard timestamp representation
            $this->assertEquals('12:00:00 AM 01/01/2002', $model->time);
        } else {
            $this->assertEquals("('2002-01-01 00:00:00')", $model->time);
        }

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues();
        $this->assertEquals('not something', $model->char_col2);

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues(false);
        $this->assertEquals("('something')", $model->char_col2);
    }
}
