<?php

namespace yiiunit\extensions\sphinx;

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
        $query->from('tbl_user');
        $this->assertEquals(['tbl_user'], $query->from);
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
}
