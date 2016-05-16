<?php

namespace yiiunit\framework\db;

use yii\db\Expression;
use yii\db\Query;

/**
 * @group db
 * @group mysql
 */
class QueryTest extends DatabaseTestCase
{
    public function testSelect()
    {
        // default
        $query = new Query;
        $query->select('*');
        $this->assertEquals(['*'], $query->select);
        $this->assertNull($query->distinct);
        $this->assertEquals(null, $query->selectOption);

        $query = new Query;
        $query->select('id, name', 'something')->distinct(true);
        $this->assertEquals(['id', 'name'], $query->select);
        $this->assertTrue($query->distinct);
        $this->assertEquals('something', $query->selectOption);

        $query = new Query();
        $query->addSelect('email');
        $this->assertEquals(['email'], $query->select);

        $query = new Query();
        $query->select('id, name');
        $query->addSelect('email');
        $this->assertEquals(['id', 'name', 'email'], $query->select);
    }

    public function testFrom()
    {
        $query = new Query;
        $query->from('user');
        $this->assertEquals(['user'], $query->from);
    }

    public function testWhere()
    {
        $query = new Query;
        $query->where('id = :id', [':id' => 1]);
        $this->assertEquals('id = :id', $query->where);
        $this->assertEquals([':id' => 1], $query->params);

        $query->andWhere('name = :name', [':name' => 'something']);
        $this->assertEquals(['and', 'id = :id', 'name = :name'], $query->where);
        $this->assertEquals([':id' => 1, ':name' => 'something'], $query->params);

        $query->orWhere('age = :age', [':age' => '30']);
        $this->assertEquals(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->where);
        $this->assertEquals([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    public function testFilterWhere()
    {
        // should work with hash format
        $query = new Query;
        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->andFilterWhere(['status' => null]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->orFilterWhere(['name' => '']);
        $this->assertEquals(['id' => 0], $query->where);

        // should work with operator format
        $query = new Query;
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertEquals($condition, $query->where);
    }

    public function testFilterRecursively()
    {
        $query = new Query();
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->where);
    }

    public function testJoin()
    {
    }

    public function testGroup()
    {
        $query = new Query;
        $query->groupBy('team');
        $this->assertEquals(['team'], $query->groupBy);

        $query->addGroupBy('company');
        $this->assertEquals(['team', 'company'], $query->groupBy);

        $query->addGroupBy('age');
        $this->assertEquals(['team', 'company', 'age'], $query->groupBy);
    }

    public function testHaving()
    {
        $query = new Query;
        $query->having('id = :id', [':id' => 1]);
        $this->assertEquals('id = :id', $query->having);
        $this->assertEquals([':id' => 1], $query->params);

        $query->andHaving('name = :name', [':name' => 'something']);
        $this->assertEquals(['and', 'id = :id', 'name = :name'], $query->having);
        $this->assertEquals([':id' => 1, ':name' => 'something'], $query->params);

        $query->orHaving('age = :age', [':age' => '30']);
        $this->assertEquals(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->having);
        $this->assertEquals([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    public function testOrder()
    {
        $query = new Query;
        $query->orderBy('team');
        $this->assertEquals(['team' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('company');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('age');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->orderBy);

        $query->addOrderBy(['age' => SORT_DESC]);
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);

        $query->addOrderBy('age ASC, company DESC');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->orderBy);

        $expression = new Expression('SUBSTR(name, 3, 4) DESC, x ASC');
        $query->orderBy($expression);
        $this->assertEquals([$expression], $query->orderBy);

        $expression = new Expression('SUBSTR(name, 3, 4) DESC, x ASC');
        $query->addOrderBy($expression);
        $this->assertEquals([$expression, $expression], $query->orderBy);
    }

    public function testLimitOffset()
    {
        $query = new Query;
        $query->limit(10)->offset(5);
        $this->assertEquals(10, $query->limit);
        $this->assertEquals(5, $query->offset);
    }

    public function testUnion()
    {
        $connection = $this->getConnection();
        $query = new Query;
        $query->select(['id', 'name'])
            ->from('item')
            ->limit(2)
            ->union(
                (new Query())
                    ->select(['id', 'name'])
                    ->from(['category'])
                    ->limit(2)
            );
        $result = $query->all($connection);
        $this->assertNotEmpty($result);
        $this->assertSame(4, count($result));
    }

    public function testOne()
    {
        $db = $this->getConnection();

        $result = (new Query)->from('customer')->where(['status' => 2])->one($db);
        $this->assertEquals('user3', $result['name']);

        $result = (new Query)->from('customer')->where(['status' => 3])->one($db);
        $this->assertFalse($result);
    }

    public function testExists()
    {
        $db = $this->getConnection();

        $result = (new Query)->from('customer')->where(['status' => 2])->exists($db);
        $this->assertTrue($result);

        $result = (new Query)->from('customer')->where(['status' => 3])->exists($db);
        $this->assertFalse($result);
    }

    public function testColumn()
    {
        $db = $this->getConnection();
        $result = (new Query)->select('name')->from('customer')->orderBy(['id' => SORT_DESC])->column($db);
        $this->assertEquals(['user3', 'user2', 'user1'], $result);

        // https://github.com/yiisoft/yii2/issues/7515
        $result = (new Query)->from('customer')
            ->select('name')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->column($db);
        $this->assertEquals([3 => 'user3', 2 => 'user2', 1 => 'user1'], $result);
    }

    public function testCount()
    {
        $db = $this->getConnection();

        $count = (new Query)->from('customer')->count('*', $db);
        $this->assertEquals(3, $count);

        $count = (new Query)->from('customer')->where(['status' => 2])->count('*', $db);
        $this->assertEquals(1, $count);

        $count = (new Query)->select('[[status]], COUNT([[id]])')->from('customer')->groupBy('status')->count('*', $db);
        $this->assertEquals(2, $count);
    }

    /**
     * @depends testFilterWhere
     */
    public function testAndFilterCompare()
    {
        $query = new Query;

        $result = $query->andFilterCompare('name', null);
        $this->assertInstanceOf('yii\db\Query', $result);
        $this->assertNull($query->where);

        $query->andFilterCompare('name', '');
        $this->assertNull($query->where);

        $query->andFilterCompare('name', 'John Doe');
        $condition = ['=', 'name', 'John Doe'];
        $this->assertEquals($condition, $query->where);

        $condition = ['and', $condition, ['like', 'name', 'Doe']];
        $query->andFilterCompare('name', 'Doe', 'like');
        $this->assertEquals($condition, $query->where);

        $condition = ['and', $condition, ['>', 'rating', '9']];
        $query->andFilterCompare('rating', '>9');
        $this->assertEquals($condition, $query->where);

        $condition = ['and', $condition, ['<=', 'value', '100']];
        $query->andFilterCompare('value', '<=100');
        $this->assertEquals($condition, $query->where);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8068
     *
     * @depends testCount
     */
    public function testCountHavingWithoutGroupBy()
    {
        if (!in_array($this->driverName, ['mysql'])) {
            $this->markTestSkipped("{$this->driverName} does not support having without group by.");
        }

        $db = $this->getConnection();

        $count = (new Query)->from('customer')->having(['status' => 2])->count('*', $db);
        $this->assertEquals(1, $count);
    }
}
