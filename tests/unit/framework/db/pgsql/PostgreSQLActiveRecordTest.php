<?php

namespace yiiunit\framework\db\pgsql;

use yiiunit\data\ar\ActiveRecord;
use yiiunit\framework\db\ActiveRecordTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'pgsql';


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

        $this->assertEquals(1, BoolAR::find()->where('bool_col = TRUE')->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where('bool_col = FALSE')->count('*', $db));
        $this->assertEquals(2, BoolAR::find()->where('bool_col IN (TRUE, FALSE)')->count('*', $db));

        $this->assertEquals(1, BoolAR::find()->where(['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where(['bool_col' => false])->count('*', $db));
        $this->assertEquals(2, BoolAR::find()->where(['bool_col' => [true, false]])->count('*', $db));

        $this->assertEquals(1, BoolAR::find()->where('bool_col = :bool_col', ['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where('bool_col = :bool_col', ['bool_col' => false])->count('*', $db));

        $this->assertSame(true,  BoolAR::find()->where(['bool_col' => true])->one($db)->bool_col);
        $this->assertSame(false, BoolAR::find()->where(['bool_col' => false])->one($db)->bool_col);
    }

    public function testBooleanDefaultValues()
    {
        $model = new BoolAR();
        $this->assertNull($model->bool_col);
        $this->assertNull($model->default_true);
        $this->assertNull($model->default_false);
        $model->loadDefaultValues();
        $this->assertNull($model->bool_col);
        $this->assertSame(true, $model->default_true);
        $this->assertSame(false, $model->default_false);

        $this->assertTrue($model->save(false));
    }
}

class BoolAR extends ActiveRecord
{
    public static function tableName()
    {
        return 'bool_values';
    }
}