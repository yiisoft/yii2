<?php

namespace yiiunit\framework\db;

use yii\db\ActiveQuery;
use yii\db\Query;

trait GetTablesAliasTestTrait
{
    /**
     * @return Query|ActiveQuery
     */
    abstract protected function createQuery();

    public function testGetTableNames_isFromArray()
    {
        $query = $this->createQuery();
        $query->from = [
            '{{prf}}' => '{{profile}}',
            '{{usr}}' => '{{user}}',
            '{{a b}}' => '{{c d}}',
        ];

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{prf}}' => '{{profile}}',
            '{{usr}}' => '{{user}}',
            '{{a b}}' => '{{c d}}',
        ], $tables);
    }

    public function testGetTableNames_isFromString()
    {
        $query = $this->createQuery();
        $query->from = 'profile AS \'prf\', user "usr", `order`, "customer", "a b" as "c d"';

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{prf}}' => '{{profile}}',
            '{{usr}}' => '{{user}}',
            '{{order}}' => '{{order}}',
            '{{customer}}' => '{{customer}}',
            '{{c d}}' => '{{a b}}',
        ], $tables);
    }

    public function testGetTableNames_isFromObject_generateException()
    {
        $query = $this->createQuery();
        $query->from = new \stdClass();

        $this->setExpectedException('\yii\base\InvalidConfigException');

        $query->getTablesUsedInFrom();
    }

    public function testGetTablesAlias_isFromString()
    {
        $query = $this->createQuery();
        $query->from = 'profile AS \'prf\', user "usr", service srv, order, [a b] [c d], {{something}} AS myalias';

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{prf}}' => '{{profile}}',
            '{{usr}}' => '{{user}}',
            '{{srv}}' => '{{service}}',
            '{{order}}' => '{{order}}',
            '{{c d}}' => '{{a b}}',
            '{{myalias}}' => '{{something}}',
        ], $tables);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14150
     */
    public function testGetTableAliasFromPrefixedTableName()
    {
        $query = $this->createQuery();
        $query->from = '{{%order_item}}';

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{%order_item}}' => '{{%order_item}}',
        ], $tables);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14211
     */
    public function testGetTableAliasFromTableNameWithDatabase()
    {
        $query = $this->createQuery();
        $query->from = 'tickets.workflows';

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{tickets.workflows}}' => '{{tickets.workflows}}',
        ], $tables);
    }
}
