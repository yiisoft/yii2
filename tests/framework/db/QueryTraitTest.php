<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db;

use yii\db\Expression;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\QueryTrait} methods via {@see Query} without database connection.
 *
 * @group db
 */
class QueryTraitTest extends TestCase
{
    public function testWhereSetsCondition(): void
    {
        $query = new Query();

        $result = $query->where(['id' => 1]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testWhereReplacesExistingCondition(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->where(['name' => 'test']);

        $this->assertSame(['name' => 'test'], $query->where);
    }

    public function testWhereWithStringCondition(): void
    {
        $query = new Query();

        $query->where('id = 1');

        $this->assertSame('id = 1', $query->where);
    }

    public function testWhereWithExpression(): void
    {
        $expression = new Expression('id = :id', [':id' => 1]);
        $query = new Query();

        $query->where($expression);

        $this->assertSame($expression, $query->where);
    }

    public function testAndWhereOnEmptyQuery(): void
    {
        $query = new Query();

        $result = $query->andWhere(['id' => 1]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testAndWhereAppendsCondition(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->andWhere(['name' => 'test']);

        $this->assertSame(['and', ['id' => 1], ['name' => 'test']], $query->where);
    }

    public function testAndWhereFlattensConsecutiveCalls(): void
    {
        $query = new Query();
        $query->where(['a' => 1]);
        $query->andWhere(['b' => 2]);
        $query->andWhere(['c' => 3]);

        $this->assertSame(['and', ['a' => 1], ['b' => 2], ['c' => 3]], $query->where);
    }

    public function testOrWhereOnEmptyQuery(): void
    {
        $query = new Query();

        $result = $query->orWhere(['id' => 1]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testOrWhereAppendsCondition(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->orWhere(['name' => 'test']);

        $this->assertSame(['or', ['id' => 1], ['name' => 'test']], $query->where);
    }

    public function testOrWhereNestsConsecutiveCalls(): void
    {
        $query = new Query();
        $query->where(['a' => 1]);
        $query->orWhere(['b' => 2]);
        $query->orWhere(['c' => 3]);

        $this->assertSame(['or', ['or', ['a' => 1], ['b' => 2]], ['c' => 3]], $query->where);
    }

    public function testAndWhereFollowedByOrWhere(): void
    {
        $query = new Query();
        $query->where(['a' => 1]);
        $query->andWhere(['b' => 2]);
        $query->orWhere(['c' => 3]);

        $this->assertSame(['or', ['and', ['a' => 1], ['b' => 2]], ['c' => 3]], $query->where);
    }

    public function testFilterWhereRemovesNullValues(): void
    {
        $query = new Query();

        $result = $query->filterWhere(['id' => 1, 'name' => null]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testFilterWhereRemovesEmptyStrings(): void
    {
        $query = new Query();

        $query->filterWhere(['id' => 1, 'name' => '']);

        $this->assertSame(['id' => 1], $query->where);
    }

    public function testFilterWhereRemovesWhitespaceStrings(): void
    {
        $query = new Query();

        $query->filterWhere(['id' => 1, 'name' => '   ']);

        $this->assertSame(['id' => 1], $query->where);
    }

    public function testFilterWhereRemovesEmptyArrays(): void
    {
        $query = new Query();

        $query->filterWhere(['id' => 1, 'tags' => []]);

        $this->assertSame(['id' => 1], $query->where);
    }

    public function testFilterWhereKeepsZero(): void
    {
        $query = new Query();

        $query->filterWhere(['status' => 0]);

        $this->assertSame(['status' => 0], $query->where);
    }

    public function testFilterWhereKeepsZeroString(): void
    {
        $query = new Query();

        $query->filterWhere(['status' => '0']);

        $this->assertSame(['status' => '0'], $query->where);
    }

    public function testFilterWhereKeepsFalse(): void
    {
        $query = new Query();

        $query->filterWhere(['active' => false]);

        $this->assertSame(['active' => false], $query->where);
    }

    public function testFilterWhereAllEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['name' => null, 'title' => '', 'tags' => []]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereHashFormatRemovesEmptyValues(): void
    {
        $query = new Query();

        $query->filterWhere(['status' => 1, 'name' => null]);

        $this->assertSame(['status' => 1], $query->where);
    }

    public function testFilterWhereBetweenBothEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['between', 'age', null, null]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereBetweenFirstEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['between', 'age', null, 30]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereBetweenSecondEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['between', 'age', 18, '']);

        $this->assertNull($query->where);
    }

    public function testFilterWhereBetweenBothPresent(): void
    {
        $query = new Query();

        $query->filterWhere(['between', 'age', 18, 30]);

        $this->assertSame(['between', 'age', 18, 30], $query->where);
    }

    public function testFilterWhereNotBetweenBothEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['not between', 'age', null, null]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereNotBetweenFirstEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['not between', 'age', null, 30]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereNotBetweenSecondEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['not between', 'age', 18, '']);

        $this->assertNull($query->where);
    }

    public function testFilterWhereNotBetweenBothPresent(): void
    {
        $query = new Query();

        $query->filterWhere(['not between', 'age', 18, 30]);

        $this->assertSame(['not between', 'age', 18, 30], $query->where);
    }

    public function testFilterWhereLikeEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['like', 'name', '']);

        $this->assertNull($query->where);
    }

    public function testFilterWhereLikePresent(): void
    {
        $query = new Query();

        $query->filterWhere(['like', 'name', 'John']);

        $this->assertSame(['like', 'name', 'John'], $query->where);
    }

    public function testFilterWhereInEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['in', 'id', []]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereInPresent(): void
    {
        $query = new Query();

        $query->filterWhere(['in', 'id', [1, 2, 3]]);

        $this->assertSame(['in', 'id', [1, 2, 3]], $query->where);
    }

    public function testFilterWhereDefaultOperatorEmptyValue(): void
    {
        $query = new Query();

        $query->filterWhere(['>=', 'age', null]);

        $this->assertNull($query->where);
    }

    public function testFilterWhereDefaultOperatorWithValue(): void
    {
        $query = new Query();

        $query->filterWhere(['>=', 'age', 18]);

        $this->assertSame(['>=', 'age', 18], $query->where);
    }

    public function testAndFilterWhereOnEmptyQuery(): void
    {
        $query = new Query();

        $result = $query->andFilterWhere(['id' => 1]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testAndFilterWhereAppendsNonEmpty(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->andFilterWhere(['name' => 'test']);

        $this->assertSame(['and', ['id' => 1], ['name' => 'test']], $query->where);
    }

    public function testAndFilterWhereSkipsEmpty(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->andFilterWhere(['name' => null]);

        $this->assertSame(['id' => 1], $query->where);
    }

    public function testOrFilterWhereOnEmptyQuery(): void
    {
        $query = new Query();

        $result = $query->orFilterWhere(['id' => 1]);

        $this->assertSame($query, $result);
        $this->assertSame(['id' => 1], $query->where);
    }

    public function testOrFilterWhereAppendsNonEmpty(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->orFilterWhere(['name' => 'test']);

        $this->assertSame(['or', ['id' => 1], ['name' => 'test']], $query->where);
    }

    public function testOrFilterWhereSkipsEmpty(): void
    {
        $query = new Query();
        $query->where(['id' => 1]);

        $query->orFilterWhere(['name' => '']);

        $this->assertSame(['id' => 1], $query->where);
    }

    public function testFilterConditionRecursiveAndAllEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['and', ['like', 'name', ''], ['in', 'id', []]]);

        $this->assertNull($query->where);
    }

    public function testFilterConditionRecursiveAndPartialEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['and', ['like', 'name', ''], ['id' => 1]]);

        $this->assertSame(['and', ['id' => 1]], $query->where);
    }

    public function testFilterConditionRecursiveOrAllEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['or', ['like', 'name', ''], ['eq', 'id', null]]);

        $this->assertNull($query->where);
    }

    public function testFilterConditionRecursiveOrPartialEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['or', ['like', 'name', ''], ['id' => 1]]);

        $this->assertSame(['or', ['id' => 1]], $query->where);
    }

    public function testFilterConditionRecursiveNotEmpty(): void
    {
        $query = new Query();

        $query->filterWhere(['not', ['like', 'name', '']]);

        $this->assertNull($query->where);
    }

    public function testFilterConditionRecursiveNotWithValue(): void
    {
        $query = new Query();

        $query->filterWhere(['not', ['id' => 1]]);

        $this->assertSame(['not', ['id' => 1]], $query->where);
    }

    public function testFilterConditionDeeplyNested(): void
    {
        $query = new Query();

        $query->filterWhere([
            'and',
            ['or', ['like', 'name', ''], ['like', 'title', '']],
            ['id' => 1],
            ['not', ['like', 'desc', '']],
        ]);

        $this->assertSame(['and', ['id' => 1]], $query->where);
    }

    public function testFilterConditionCaseInsensitiveOperators(): void
    {
        $query = new Query();

        $query->filterWhere(['AND', ['like', 'name', ''], ['id' => 1]]);

        $this->assertSame(['AND', ['id' => 1]], $query->where);
    }

    public function testOrderByString(): void
    {
        $query = new Query();

        $result = $query->orderBy('name');

        $this->assertSame($query, $result);
        $this->assertSame(['name' => SORT_ASC], $query->orderBy);
    }

    public function testOrderByStringDesc(): void
    {
        $query = new Query();

        $query->orderBy('name DESC');

        $this->assertSame(['name' => SORT_DESC], $query->orderBy);
    }

    public function testOrderByStringAsc(): void
    {
        $query = new Query();

        $query->orderBy('name ASC');

        $this->assertSame(['name' => SORT_ASC], $query->orderBy);
    }

    public function testOrderByStringMultipleColumns(): void
    {
        $query = new Query();

        $query->orderBy('name ASC, age DESC');

        $this->assertSame(['name' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);
    }

    public function testOrderByStringCaseInsensitive(): void
    {
        $query = new Query();

        $query->orderBy('name desc');

        $this->assertSame(['name' => SORT_DESC], $query->orderBy);
    }

    public function testOrderByArray(): void
    {
        $query = new Query();

        $query->orderBy(['name' => SORT_ASC, 'age' => SORT_DESC]);

        $this->assertSame(['name' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);
    }

    public function testOrderByExpression(): void
    {
        $expression = new Expression('FIELD(id, 3, 1, 2)');
        $query = new Query();

        $query->orderBy($expression);

        $this->assertSame([$expression], $query->orderBy);
    }

    public function testOrderByReplacesExisting(): void
    {
        $query = new Query();
        $query->orderBy('name');

        $query->orderBy('age');

        $this->assertSame(['age' => SORT_ASC], $query->orderBy);
    }

    public function testOrderByEmpty(): void
    {
        $query = new Query();

        $query->orderBy([]);

        $this->assertSame([], $query->orderBy);
    }

    public function testOrderByNull(): void
    {
        $query = new Query();
        $query->orderBy('name');

        $query->orderBy(null);

        $this->assertSame([], $query->orderBy);
    }

    public function testOrderByEmptyString(): void
    {
        $query = new Query();

        $query->orderBy('');

        $this->assertSame([], $query->orderBy);
    }

    public function testOrderByStringWithExtraSpaces(): void
    {
        $query = new Query();

        $query->orderBy('  name  ASC ,  age  DESC  ');

        $this->assertSame(['name' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);
    }

    public function testOrderByStringWithTrailingText(): void
    {
        $query = new Query();

        $query->orderBy('name desc extra');

        $this->assertSame(['name desc extra' => SORT_ASC], $query->orderBy);
    }

    public function testOrderByStringWithDirectionInColumnName(): void
    {
        $query = new Query();

        $query->orderBy('description');

        $this->assertSame(['description' => SORT_ASC], $query->orderBy);
    }

    public function testOrderByStringColumnWithAscInName(): void
    {
        $query = new Query();

        $query->orderBy('is_basic');

        $this->assertSame(['is_basic' => SORT_ASC], $query->orderBy);
    }

    public function testAddOrderByOnEmptyQuery(): void
    {
        $query = new Query();

        $result = $query->addOrderBy('name');

        $this->assertSame($query, $result);
        $this->assertSame(['name' => SORT_ASC], $query->orderBy);
    }

    public function testAddOrderByAppendsColumns(): void
    {
        $query = new Query();
        $query->orderBy('name');

        $query->addOrderBy('age DESC');

        $this->assertSame(['name' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);
    }

    public function testAddOrderByOverridesDuplicateColumn(): void
    {
        $query = new Query();
        $query->orderBy('name ASC');

        $query->addOrderBy('name DESC');

        $this->assertSame(['name' => SORT_DESC], $query->orderBy);
    }

    public function testAddOrderByWithExpression(): void
    {
        $expression = new Expression('RAND()');
        $query = new Query();
        $query->orderBy('name');

        $query->addOrderBy($expression);

        $this->assertSame(['name' => SORT_ASC, $expression], $query->orderBy);
    }

    public function testAddOrderByMultipleExpressions(): void
    {
        $expr1 = new Expression('RAND()');
        $expr2 = new Expression('FIELD(id, 1, 2)');
        $query = new Query();
        $query->orderBy($expr1);

        $query->addOrderBy($expr2);

        $this->assertSame([$expr1, $expr2], $query->orderBy);
    }

    public function testAddOrderByEmpty(): void
    {
        $query = new Query();
        $query->orderBy('name');

        $query->addOrderBy([]);

        $this->assertSame(['name' => SORT_ASC], $query->orderBy);
    }

    public function testLimit(): void
    {
        $query = new Query();

        $result = $query->limit(10);

        $this->assertSame($query, $result);
        $this->assertSame(10, $query->limit);
    }

    public function testLimitNull(): void
    {
        $query = new Query();
        $query->limit(10);

        $query->limit(null);

        $this->assertNull($query->limit);
    }

    public function testLimitNegative(): void
    {
        $query = new Query();

        $query->limit(-1);

        $this->assertSame(-1, $query->limit);
    }

    public function testLimitZero(): void
    {
        $query = new Query();

        $query->limit(0);

        $this->assertSame(0, $query->limit);
    }

    public function testLimitExpression(): void
    {
        $expression = new Expression('1 + 1');
        $query = new Query();

        $query->limit($expression);

        $this->assertSame($expression, $query->limit);
    }

    public function testOffset(): void
    {
        $query = new Query();

        $result = $query->offset(5);

        $this->assertSame($query, $result);
        $this->assertSame(5, $query->offset);
    }

    public function testOffsetNull(): void
    {
        $query = new Query();
        $query->offset(5);

        $query->offset(null);

        $this->assertNull($query->offset);
    }

    public function testOffsetNegative(): void
    {
        $query = new Query();

        $query->offset(-1);

        $this->assertSame(-1, $query->offset);
    }

    public function testOffsetZero(): void
    {
        $query = new Query();

        $query->offset(0);

        $this->assertSame(0, $query->offset);
    }

    public function testOffsetExpression(): void
    {
        $expression = new Expression('2 + 3');
        $query = new Query();

        $query->offset($expression);

        $this->assertSame($expression, $query->offset);
    }

    public function testIndexByString(): void
    {
        $query = new Query();

        $result = $query->indexBy('id');

        $this->assertSame($query, $result);
        $this->assertSame('id', $query->indexBy);
    }

    public function testIndexByCallable(): void
    {
        $callback = function ($row) {
            return $row['id'];
        };
        $query = new Query();

        $query->indexBy($callback);

        $this->assertSame($callback, $query->indexBy);
    }

    public function testIndexByReplaces(): void
    {
        $query = new Query();
        $query->indexBy('id');

        $query->indexBy('name');

        $this->assertSame('name', $query->indexBy);
    }

    public function testEmulateExecutionDefaultTrue(): void
    {
        $query = new Query();

        $result = $query->emulateExecution();

        $this->assertSame($query, $result);
        $this->assertTrue($query->emulateExecution);
    }

    public function testEmulateExecutionExplicitTrue(): void
    {
        $query = new Query();

        $query->emulateExecution(true);

        $this->assertTrue($query->emulateExecution);
    }

    public function testEmulateExecutionFalse(): void
    {
        $query = new Query();
        $query->emulateExecution();

        $query->emulateExecution(false);

        $this->assertFalse($query->emulateExecution);
    }

    public function testEmulateExecutionDefaultValue(): void
    {
        $query = new Query();

        $this->assertFalse($query->emulateExecution);
    }

    public function testFluentInterface(): void
    {
        $query = new Query();

        $result = $query
            ->where(['id' => 1])
            ->andWhere(['status' => 1])
            ->orWhere(['role' => 'admin'])
            ->orderBy('name')
            ->addOrderBy('id DESC')
            ->limit(10)
            ->offset(5)
            ->indexBy('id')
            ->emulateExecution();

        $this->assertSame($query, $result);
    }

    public function testPopulateWithStringIndexBy(): void
    {
        $query = new Query();
        $query->indexBy('id');

        $rows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $result = $query->populate($rows);

        $expected = [
            1 => ['id' => 1, 'name' => 'Alice'],
            2 => ['id' => 2, 'name' => 'Bob'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testPopulateWithCallableIndexBy(): void
    {
        $query = new Query();
        $query->indexBy(function ($row) {
            return $row['name'] . '_' . $row['id'];
        });

        $rows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $result = $query->populate($rows);

        $expected = [
            'Alice_1' => ['id' => 1, 'name' => 'Alice'],
            'Bob_2' => ['id' => 2, 'name' => 'Bob'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testPopulateWithoutIndexBy(): void
    {
        $query = new Query();

        $rows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $result = $query->populate($rows);

        $this->assertSame($rows, $result);
    }

    public function testPopulateEmptyRows(): void
    {
        $query = new Query();
        $query->indexBy('id');

        $this->assertSame([], $query->populate([]));
    }

    public function testWhereWithParams(): void
    {
        $query = new Query();

        $query->where('id = :id', [':id' => 1]);

        $this->assertSame('id = :id', $query->where);
        $this->assertSame([':id' => 1], $query->params);
    }

    public function testAndWhereWithParams(): void
    {
        $query = new Query();
        $query->where('id = :id', [':id' => 1]);

        $query->andWhere('name = :name', [':name' => 'test']);

        $this->assertSame(['and', 'id = :id', 'name = :name'], $query->where);
        $this->assertSame([':id' => 1, ':name' => 'test'], $query->params);
    }

    public function testOrWhereWithParams(): void
    {
        $query = new Query();
        $query->where('id = :id', [':id' => 1]);

        $query->orWhere('name = :name', [':name' => 'test']);

        $this->assertSame(['or', 'id = :id', 'name = :name'], $query->where);
        $this->assertSame([':id' => 1, ':name' => 'test'], $query->params);
    }

    public function testWhereParamsAccumulate(): void
    {
        $query = new Query();
        $query->where('a = :a', [':a' => 1]);
        $query->andWhere('b = :b', [':b' => 2]);
        $query->orWhere('c = :c', [':c' => 3]);

        $this->assertSame([':a' => 1, ':b' => 2, ':c' => 3], $query->params);
    }

    public function testWhereParamsOverride(): void
    {
        $query = new Query();
        $query->where('a = :v', [':v' => 1]);

        $query->andWhere('b = :v', [':v' => 2]);

        $this->assertSame([':v' => 2], $query->params);
    }
}
