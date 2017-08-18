<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;

abstract class QueryTest extends DatabaseTestCase
{
    public function testSelect()
    {
        // default
        $query = new Query();
        $query->select('*');
        $this->assertSame(['*'], $query->select);
        $this->assertNull($query->distinct);
        $this->assertSame(null, $query->selectOption);

        $query = new Query();
        $query->select('id, name', 'something')->distinct(true);
        $this->assertSame(['id', 'name'], $query->select);
        $this->assertTrue($query->distinct);
        $this->assertSame('something', $query->selectOption);

        $query = new Query();
        $query->addSelect('email');
        $this->assertSame(['email'], $query->select);

        $query = new Query();
        $query->select('id, name');
        $query->addSelect('email');
        $this->assertSame(['id', 'name', 'email'], $query->select);
    }

    public function testFrom()
    {
        $query = new Query();
        $query->from('user');
        $this->assertSame(['user'], $query->from);
    }

    use GetTablesAliasTestTrait;
    protected function createQuery()
    {
        return new Query();
    }

    public function testWhere()
    {
        $query = new Query();
        $query->where('id = :id', [':id' => 1]);
        $this->assertSame('id = :id', $query->where);
        $this->assertSame([':id' => 1], $query->params);

        $query->andWhere('name = :name', [':name' => 'something']);
        $this->assertSame(['and', 'id = :id', 'name = :name'], $query->where);
        $this->assertSame([':id' => 1, ':name' => 'something'], $query->params);

        $query->orWhere('age = :age', [':age' => '30']);
        $this->assertSame(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->where);
        $this->assertSame([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    public function testFilterWhereWithHashFormat()
    {
        $query = new Query();
        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertSame(['id' => 0], $query->where);

        $query->andFilterWhere(['status' => null]);
        $this->assertSame(['id' => 0], $query->where);

        $query->orFilterWhere(['name' => '']);
        $this->assertSame(['id' => 0], $query->where);
    }

    public function testFilterWhereWithOperatorFormat()
    {
        $query = new Query();
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertSame($condition, $query->where);

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertSame($condition, $query->where);

        $query->andFilterWhere(['or', ['eq', 'id', null], ['eq', 'id', []]]);
        $this->assertSame($condition, $query->where);
    }

    public function testFilterHavingWithHashFormat()
    {
        $query = new Query();
        $query->filterHaving([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertSame(['id' => 0], $query->having);

        $query->andFilterHaving(['status' => null]);
        $this->assertSame(['id' => 0], $query->having);

        $query->orFilterHaving(['name' => '']);
        $this->assertSame(['id' => 0], $query->having);
    }

    public function testFilterHavingWithOperatorFormat()
    {
        $query = new Query();
        $condition = ['like', 'name', 'Alex'];
        $query->filterHaving($condition);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['between', 'id', null, null]);
        $this->assertSame($condition, $query->having);

        $query->orFilterHaving(['not between', 'id', null, null]);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['in', 'id', []]);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['not in', 'id', []]);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['like', 'id', '']);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['or like', 'id', '']);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['not like', 'id', '   ']);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['or not like', 'id', null]);
        $this->assertSame($condition, $query->having);

        $query->andFilterHaving(['or', ['eq', 'id', null], ['eq', 'id', []]]);
        $this->assertSame($condition, $query->having);
    }

    public function testFilterRecursively()
    {
        $query = new Query();
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertSame(['and', ['id' => 1]], $query->where);
    }

    /*public function testJoin()
    {
    }*/

    public function testGroup()
    {
        $query = new Query();
        $query->groupBy('team');
        $this->assertSame(['team'], $query->groupBy);

        $query->addGroupBy('company');
        $this->assertSame(['team', 'company'], $query->groupBy);

        $query->addGroupBy('age');
        $this->assertSame(['team', 'company', 'age'], $query->groupBy);
    }

    public function testHaving()
    {
        $query = new Query();
        $query->having('id = :id', [':id' => 1]);
        $this->assertSame('id = :id', $query->having);
        $this->assertSame([':id' => 1], $query->params);

        $query->andHaving('name = :name', [':name' => 'something']);
        $this->assertSame(['and', 'id = :id', 'name = :name'], $query->having);
        $this->assertSame([':id' => 1, ':name' => 'something'], $query->params);

        $query->orHaving('age = :age', [':age' => '30']);
        $this->assertSame(['or', ['and', 'id = :id', 'name = :name'], 'age = :age'], $query->having);
        $this->assertSame([':id' => 1, ':name' => 'something', ':age' => '30'], $query->params);
    }

    public function testOrder()
    {
        $query = new Query();
        $query->orderBy('team');
        $this->assertSame(['team' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('company');
        $this->assertSame(['team' => SORT_ASC, 'company' => SORT_ASC], $query->orderBy);

        $query->addOrderBy('age');
        $this->assertSame(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->orderBy);

        $query->addOrderBy(['age' => SORT_DESC]);
        $this->assertSame(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);

        $query->addOrderBy('age ASC, company DESC');
        $this->assertSame(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->orderBy);

        $expression = new Expression('SUBSTR(name, 3, 4) DESC, x ASC');
        $query->orderBy($expression);
        $this->assertSame([$expression], $query->orderBy);

        $expression = new Expression('SUBSTR(name, 3, 4) DESC, x ASC');
        $query->addOrderBy($expression);
        $this->assertSame([$expression, $expression], $query->orderBy);
    }

    public function testLimitOffset()
    {
        $query = new Query();
        $query->limit(10)->offset(5);
        $this->assertSame(10, $query->limit);
        $this->assertSame(5, $query->offset);
    }

    public function testLimitOffsetWithExpression()
    {
        $query = (new Query())->from('customer')->select('id')->orderBy('id');
        $query
            ->limit(new Expression('1 + 1'))
            ->offset(new Expression('1 + 0'));

        $result = $query->column($this->getConnection());

        $this->assertCount(2, $result);

        $this->assertNotContains(1, $result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
    }

    public function testUnion()
    {
        $connection = $this->getConnection();
        $query = new Query();
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
        $this->assertCount(4, $result);
    }

    public function testOne()
    {
        $db = $this->getConnection();

        $result = (new Query())->from('customer')->where(['status' => 2])->one($db);
        $this->assertSame('user3', $result['name']);

        $result = (new Query())->from('customer')->where(['status' => 3])->one($db);
        $this->assertFalse($result);
    }

    public function testExists()
    {
        $db = $this->getConnection();

        $result = (new Query())->from('customer')->where(['status' => 2])->exists($db);
        $this->assertTrue($result);

        $result = (new Query())->from('customer')->where(['status' => 3])->exists($db);
        $this->assertFalse($result);
    }

    public function testColumn()
    {
        $db = $this->getConnection();
        $result = (new Query())->select('name')->from('customer')->orderBy(['id' => SORT_DESC])->column($db);
        $this->assertSame(['user3', 'user2', 'user1'], $result);

        // https://github.com/yiisoft/yii2/issues/7515
        $result = (new Query())->from('customer')
            ->select('name')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->column($db);
        $this->assertSame([3 => 'user3', 2 => 'user2', 1 => 'user1'], $result);

        // https://github.com/yiisoft/yii2/issues/12649
        $result = (new Query())->from('customer')
            ->select(['name', 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->indexBy(function ($row) {
                return $row['id'] * 2;
            })
            ->column($db);
        $this->assertSame([6 => 'user3', 4 => 'user2', 2 => 'user1'], $result);

        $result = (new Query())->from('customer')
            ->select(['name'])
            ->indexBy('name')
            ->orderBy(['id' => SORT_DESC])
            ->column($db);
        $this->assertSame(['user3' => 'user3', 'user2' => 'user2', 'user1' => 'user1'], $result);
    }


    /**
     * Ensure no ambiguous column error occurs on indexBy with JOIN
     * https://github.com/yiisoft/yii2/issues/13859
     */
    public function testAmbiguousColumnIndexBy()
    {
        switch ($this->driverName) {
            case 'pgsql':
            case 'sqlite':
                $selectExpression = "(customer.name || ' in ' || p.description) AS name";
                break;
            case 'cubird':
            case 'mysql':
                $selectExpression = "concat(customer.name,' in ', p.description) name";
                break;
            default:
                $this->markTestIncomplete('CONCAT syntax for this DBMS is not added to the test yet.');
        }

        $db = $this->getConnection();
        $result = (new Query())->select([$selectExpression])->from('customer')
            ->innerJoin('profile p', '{{customer}}.[[profile_id]] = {{p}}.[[id]]')
            ->indexBy('id')->column($db);
        $this->assertSame([
            1 => 'user1 in profile customer 1',
            3 => 'user3 in profile customer 3',
        ], $result);
    }

    public function testCount()
    {
        $db = $this->getConnection();

        $count = (new Query())->from('customer')->count('*', $db);
        $this->assertSame(3, $count);

        $count = (new Query())->from('customer')->where(['status' => 2])->count('*', $db);
        $this->assertSame(1, $count);

        $count = (new Query())->select('[[status]], COUNT([[id]])')->from('customer')->groupBy('status')->count('*', $db);
        $this->assertSame(2, $count);

        // testing that orderBy() should be ignored here as it does not affect the count anyway.
        $count = (new Query())->from('customer')->orderBy('status')->count('*', $db);
        $this->assertSame(3, $count);

        $count = (new Query())->from('customer')->orderBy('id')->limit(1)->count('*', $db);
        $this->assertSame(3, $count);
    }

    /**
     * @depends testFilterWhereWithHashFormat
     * @depends testFilterWhereWithOperatorFormat
     */
    public function testAndFilterCompare()
    {
        $query = new Query();

        $result = $query->andFilterCompare('name', null);
        $this->assertInstanceOf('yii\db\Query', $result);
        $this->assertNull($query->where);

        $query->andFilterCompare('name', '');
        $this->assertNull($query->where);

        $query->andFilterCompare('name', 'John Doe');
        $condition = ['=', 'name', 'John Doe'];
        $this->assertSame($condition, $query->where);

        $condition = ['and', $condition, ['like', 'name', 'Doe']];
        $query->andFilterCompare('name', 'Doe', 'like');
        $this->assertSame($condition, $query->where);

        $condition[] = ['>', 'rating', '9'];
        $query->andFilterCompare('rating', '>9');
        $this->assertSame($condition, $query->where);

        $condition[] = ['<=', 'value', '100'];
        $query->andFilterCompare('value', '<=100');
        $this->assertSame($condition, $query->where);
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

        $count = (new Query())->from('customer')->having(['status' => 2])->count('*', $db);
        $this->assertSame(1, $count);
    }

    public function testEmulateExecution()
    {
        $db = $this->getConnection();

        $this->assertGreaterThan(0, (new Query())->from('customer')->count('*', $db));

        $rows = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->all($db);
        $this->assertSame([], $rows);

        $row = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->one($db);
        $this->assertFalse($row);

        $exists = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->exists($db);
        $this->assertFalse($exists);

        $count = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->count('*', $db);
        $this->assertSame(0, $count);

        $sum = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->sum('id', $db);
        $this->assertSame(0, $sum);

        $sum = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->average('id', $db);
        $this->assertSame(0, $sum);

        $max = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->max('id', $db);
        $this->assertNull($max);

        $min = (new Query())
            ->from('customer')
            ->emulateExecution()
            ->min('id', $db);
        $this->assertNull($min);

        $scalar = (new Query())
            ->select(['id'])
            ->from('customer')
            ->emulateExecution()
            ->scalar($db);
        $this->assertNull($scalar);

        $column = (new Query())
            ->select(['id'])
            ->from('customer')
            ->emulateExecution()
            ->column($db);
        $this->assertSame([], $column);
    }

    /**
     * @param Connection $db
     * @param string $tableName
     * @param string $columnName
     * @param array $condition
     * @param string $operator
     * @return int
     */
    protected function countLikeQuery(Connection $db, $tableName, $columnName, array $condition, $operator = 'or')
    {
        $whereCondition = [$operator];
        foreach ($condition as $value) {
            $whereCondition[] = ['like', $columnName, $value];
        }
        $result = (new Query())
            ->from($tableName)
            ->where($whereCondition)
            ->count('*', $db);
        if (is_numeric($result)) {
            $result = (int) $result;
        }
        return $result;
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13745
     */
    public function testMultipleLikeConditions()
    {
        $db = $this->getConnection();
        $tableName = 'like_test';
        $columnName = 'col';

        if ($db->getSchema()->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            $columnName => $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64),
        ])->execute();
        $db->createCommand()->batchInsert($tableName, ['col'], [
            ['test0'],
            ['test\1'],
            ['test\2'],
            ['foo%'],
            ['%bar'],
            ['%baz%'],
        ])->execute();

        // Basic tests
        $this->assertSame(1, $this->countLikeQuery($db, $tableName, $columnName, ['test0']));
        $this->assertSame(2, $this->countLikeQuery($db, $tableName, $columnName, ['test\\']));
        $this->assertSame(0, $this->countLikeQuery($db, $tableName, $columnName, ['test%']));
        $this->assertSame(3, $this->countLikeQuery($db, $tableName, $columnName, ['%']));

        // Multiple condition tests
        $this->assertSame(2, $this->countLikeQuery($db, $tableName, $columnName, [
            'test0',
            'test\1',
        ]));
        $this->assertSame(3, $this->countLikeQuery($db, $tableName, $columnName, [
            'test0',
            'test\1',
            'test\2',
        ]));
        $this->assertSame(3, $this->countLikeQuery($db, $tableName, $columnName, [
            'foo',
            '%ba',
        ]));
    }
}
