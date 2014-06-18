<?php

namespace yiiunit\framework\db;

use yii\base\Behavior;
use yii\db\ActiveQueryBehaviorInterface;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;

/**
 * @group db
 */
class ActiveRecordQueryBehaviorTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testScope()
    {
        $models = CustomerEx::find()->whereStatus(2)->all();
        $this->assertCount(1, $models);
    }
}

class CustomerEx extends Customer
{
    public function behaviors()
    {
        return [
            'query' => [
                'class' => ArBehavior::className()
            ]
        ];
    }
}

class ArBehavior extends Behavior implements ActiveQueryBehaviorInterface
{
    public function queryBehavior()
    {
        return [
            'class' => QueryBehavior::className()
        ];
    }
}

class QueryBehavior extends Behavior
{
    public function whereStatus($status)
    {
        /* @var \yii\db\ActiveQuery $query */
        $query = $this->owner;
        $query->andWhere(['status' => $status]);
        return $query;
    }
}