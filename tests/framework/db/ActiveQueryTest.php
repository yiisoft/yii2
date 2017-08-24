<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\QueryBuilder;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Profile;

/**
 * Class ActiveQueryTest the base class for testing ActiveQuery.
 */
abstract class ActiveQueryTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testConstructor()
    {
        $config = [
            'on' => ['a' => 'b'],
            'joinWith' => ['dummy relation'],
        ];
        $query = new ActiveQuery(Customer::className(), $config);
        $this->assertSame($query->modelClass, Customer::className());
        $this->assertSame($query->on, $config['on']);
        $this->assertSame($query->joinWith, $config['joinWith']);
    }

    public function testTriggerInitEvent()
    {
        $where = '1==1';
        $callback = function (\yii\base\Event $event) use ($where) {
            $event->sender->where = $where;
        };
        Event::on(ActiveQuery::className(), ActiveQuery::EVENT_INIT, $callback);
        $result = new ActiveQuery(Customer::className());
        $this->assertSame($where, $result->where);
        Event::off(ActiveQuery::className(), ActiveQuery::EVENT_INIT, $callback);
    }

    /**
     * @todo: tests for internal logic of prepare()
     */
    public function testPrepare()
    {
        $query = new ActiveQuery(Customer::className());
        $builder = new QueryBuilder(new Connection());
        $result = $query->prepare($builder);
        $this->assertInstanceOf('yii\db\Query', $result);
    }

    public function testPopulate_EmptyRows()
    {
        $query = new ActiveQuery(Customer::className());
        $rows = [];
        $result = $query->populate([]);
        $this->assertSame($rows, $result);
    }

    /**
     * @todo: tests for internal logic of populate()
     */
    public function testPopulate_FilledRows()
    {
        $query = new ActiveQuery(Customer::className());
        $rows = $query->all();
        $result = $query->populate($rows);
        $this->assertSame($rows, $result);
    }

    /**
     * @todo: tests for internal logic of one()
     */
    public function testOne()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->one();
        $this->assertInstanceOf('yiiunit\data\ar\Customer', $result);
    }

    /**
     * @todo: test internal logic of createCommand()
     */
    public function testCreateCommand()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->createCommand();
        $this->assertInstanceOf('yii\db\Command', $result);
    }

    /**
     * @todo: tests for internal logic of queryScalar()
     */
    public function testQueryScalar()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $this->invokeMethod($query, 'queryScalar', ['name', null]);
        $this->assertSame('user1', $result);
    }

    /**
     * @todo: tests for internal logic of joinWith()
     */
    public function testJoinWith()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->joinWith('profile');
        $this->assertSame([
            [['profile'], true, 'LEFT JOIN'],
        ], $result->joinWith);
    }

    /**
     * @todo: tests for internal logic of innerJoinWith()
     */
    public function testInnerJoinWith()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->innerJoinWith('profile');
        $this->assertSame([
            [['profile'], true, 'INNER JOIN'],
        ], $result->joinWith);
    }

    /**
     * @todo: tests for the regex inside getQueryTableName
     */
    public function testGetQueryTableName_from_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertSame(['customer', 'customer'], $result);
    }

    public function testGetQueryTableName_from_set()
    {
        $options = ['from' => ['alias' => 'customer']];
        $query = new ActiveQuery(Customer::className(), $options);
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertSame(['customer', 'alias'], $result);
    }

    public function testOnCondition()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->onCondition($on, $params);
        $this->assertSame($on, $result->on);
        $this->assertSame($params, $result->params);
    }

    public function testAndOnCondition_on_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertSame($on, $result->on);
        $this->assertSame($params, $result->params);
    }

    public function testAndOnCondition_on_set()
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::className());
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertSame(['and', $onOld, $on], $result->on);
        $this->assertSame($params, $result->params);
    }

    public function testOrOnCondition_on_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertSame($on, $result->on);
        $this->assertSame($params, $result->params);
    }

    public function testOrOnCondition_on_set()
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::className());
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertSame(['or', $onOld, $on], $result->on);
        $this->assertSame($params, $result->params);
    }

    /**
     * @todo: tests for internal logic of viaTable()
     */
    public function testViaTable()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->viaTable(Profile::className(), ['id' => 'item_id']);
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertInstanceOf('yii\db\ActiveQuery', $result->via);
    }

    public function testAlias_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertSame(['alias' => 'customer'], $result->from);
    }

    public function testAlias_yet_set()
    {
        $aliasOld = ['old'];
        $query = new ActiveQuery(Customer::className());
        $query->from = $aliasOld;
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertSame(['alias' => 'old'], $result->from);
    }

    use GetTablesAliasTestTrait;
    protected function createQuery()
    {
        return new ActiveQuery(null);
    }

    public function testGetTableNames_notFilledFrom()
    {
        $query = new ActiveQuery(Profile::className());

        $tables = $query->getTablesUsedInFrom();

        $this->assertSame([
            '{{' . Profile::tableName() . '}}' => '{{' . Profile::tableName() . '}}',
        ], $tables);
    }
}
