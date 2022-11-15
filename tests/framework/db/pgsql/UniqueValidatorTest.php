<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\validators\UniqueValidator;
use yiiunit\data\ar\Type;

/**
 * @group db
 * @group pgsql
 * @group validators
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{
    public $driverName = 'pgsql';

    public function testPrepareParams()
    {
        parent::testPrepareParams();

        // Add table prefix for column name
        $model = new Type;
        $model->name = 'Angela';

        $attribute = 'name';
        $targetAttribute = [$attribute => "[[jsonb_col]]->>'name'"];
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['{{' . Type::tableName() . '}}.' . $targetAttribute[$attribute]  => $model->name];
        $this->assertEquals($expected, $result);
    }
}
