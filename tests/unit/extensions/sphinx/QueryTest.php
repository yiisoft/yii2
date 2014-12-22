<?php

namespace yiiunit\extensions\sphinx;

use yii\db\Expression;
use yii\sphinx\Query;

/**
 * @group sphinx
 */
class QueryTest extends SphinxTestCase
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
    }

    public function testFrom()
    {
        $query = new Query;
        $query->from('user');
        $this->assertEquals(['user'], $query->from);
    }

    public function testMatch()
    {
        $query = new Query;
        $match = 'test match';
        $query->match($match);
        $this->assertEquals($match, $query->match);

        $command = $query->createCommand($this->getConnection(false));
        $this->assertContains('MATCH(', $command->getSql(), 'No MATCH operator present!');
        $this->assertContains($match, $command->params, 'No match query among params!');
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

    public function testFilterWhereRecursively()
    {
        $query = new Query();
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->where);
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
    }

    public function testLimitOffset()
    {
        $query = new Query;
        $query->limit(10)->offset(5);
        $this->assertEquals(10, $query->limit);
        $this->assertEquals(5, $query->offset);
    }

    public function testWithin()
    {
        $query = new Query;
        $query->within('team');
        $this->assertEquals(['team' => SORT_ASC], $query->within);

        $query->addWithin('company');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC], $query->within);

        $query->addWithin('age');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->within);

        $query->addWithin(['age' => SORT_DESC]);
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->within);

        $query->addWithin('age ASC, company DESC');
        $this->assertEquals(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->within);
    }

    public function testOptions()
    {
        $query = new Query;
        $options = [
            'cutoff' => 50,
            'max_matches' => 50,
        ];
        $query->options($options);
        $this->assertEquals($options, $query->options);

        $newMaxMatches = $options['max_matches'] + 10;
        $query->addOptions(['max_matches' => $newMaxMatches]);
        $this->assertEquals($newMaxMatches, $query->options['max_matches']);
    }

    public function testRun()
    {
        $connection = $this->getConnection();

        $query = new Query;
        $rows = $query->from('yii2_test_article_index')
            ->match('about')
            ->options([
                'cutoff' => 50,
                'field_weights' => [
                    'title' => 10,
                    'content' => 3,
                ],
            ])
            ->all($connection);
        $this->assertNotEmpty($rows);
    }

    /**
     * @depends testRun
     */
    public function testSnippet()
    {
        $connection = $this->getConnection();

        $match = 'about';
        $snippetPrefix = 'snippet#';
        $snippetCallback = function () use ($match, $snippetPrefix) {
            return [
                $snippetPrefix . '1: ' . $match,
                $snippetPrefix . '2: ' . $match,
            ];
        };
        $snippetOptions = [
            'before_match' => '[',
            'after_match' => ']',
        ];

        $query = new Query;
        $rows = $query->from('yii2_test_article_index')
            ->match($match)
            ->snippetCallback($snippetCallback)
            ->snippetOptions($snippetOptions)
            ->all($connection);
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertContains($snippetPrefix, $row['snippet'], 'Snippet source not present!');
            $this->assertContains($snippetOptions['before_match'] . $match, $row['snippet'] . $snippetOptions['after_match'], 'Options not applied!');
        }
    }

    public function testCount()
    {
        $connection = $this->getConnection();

        $query = new Query;
        $count = $query->from('yii2_test_article_index')
            ->match('about')
            ->count('*', $connection);
        $this->assertEquals(2, $count);
    }

    /**
     * @depends testRun
     */
    public function testWhereSpecialCharValue()
    {
        $connection = $this->getConnection();

        $query = new Query;
        $rows = $query->from('yii2_test_article_index')
            ->andWhere(['author_id' => 'some"'])
            ->all($connection);
        $this->assertEmpty($rows);
    }

    /**
     * Data provider for [[testMatchSpecialCharValue()]]
     * @return array test data
     */
    public function dataProviderMatchSpecialCharValue()
    {
        return [
            ["'"],
            ['"'],
            ['@'],
            ['\\'],
            ['()'],
            ['<<<'],
            ['>>>'],
            ["\x00"],
            ["\n"],
            ["\r"],
            ["\x1a"],
            ['\\' . "'"],
            ['\\' . '"'],
        ];
    }

    /**
     * @dataProvider dataProviderMatchSpecialCharValue
     * @depends testRun
     *
     * @param string $char char to be tested
     *
     * @see https://github.com/yiisoft/yii2/issues/3668
     */
    public function testMatchSpecialCharValue($char)
    {
        $connection = $this->getConnection();

        $query = new Query;
        $rows = $query->from('yii2_test_article_index')
            ->match('about' . $char)
            ->all($connection);
        $this->assertTrue(is_array($rows)); // no query error
    }

    /**
     * @depends testMatchSpecialCharValue
     */
    public function testMatchComplex()
    {
        $connection = $this->getConnection();

        $query = new Query;
        $rows = $query->from('yii2_test_article_index')
            ->match(new Expression(':match', ['match' => '@(content) ' . $connection->escapeMatchValue('about\\"')]))
            ->all($connection);
        $this->assertNotEmpty($rows);
    }

    /**
     * @depends testRun
     *
     * @see https://github.com/yiisoft/yii2/issues/4375
     */
    public function testRunOnDistributedIndex()
    {
        $connection = $this->getConnection();

        $query = new Query;
        $rows = $query->from('yii2_test_distributed')
            ->match('about')
            ->options([
                'cutoff' => 50,
                'field_weights' => [
                    'title' => 10,
                    'content' => 3,
                ],
            ])
            ->all($connection);
        $this->assertNotEmpty($rows);
    }
}
