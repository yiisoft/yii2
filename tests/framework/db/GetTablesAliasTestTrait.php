<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use stdClass;
use yii\db\Expression;
use yii\db\ActiveQuery;
use yii\db\Query;

trait GetTablesAliasTestTrait
{
    /**
     * @return Query|ActiveQuery
     */
    abstract protected function createQuery();

    public function testGetTableNamesIsFromArrayWithAlias(): void
    {
        $query = $this->createQuery();
        $query->from = [
            'prf' => 'profile',
            '{{usr}}' => '{{user}}',
            '{{a b}}' => '{{c d}}',
            'post AS p',
        ];

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{prf}}' => '{{profile}}',
            '{{usr}}' => '{{user}}',
            '{{a b}}' => '{{c d}}',
            '{{p}}' => '{{post}}',
        ], $tables);
    }

    public function testGetTableNamesIsFromArrayWithoutAlias(): void
    {
        $query = $this->createQuery();
        $query->from = [
            '{{profile}}',
            'user'
        ];

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{profile}}' => '{{profile}}',
            '{{user}}' => '{{user}}',
        ], $tables);
    }

    public function testGetTableNamesIsFromString(): void
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

    public function testGetTableNamesIsFromObjectGenerateException(): void
    {
        $query = $this->createQuery();
        $query->from = new stdClass();

        $this->expectException('\yii\base\InvalidConfigException');

        $query->getTablesUsedInFrom();
    }

    public function testGetTablesAliasIsFromString(): void
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
    public function testGetTableNamesIsFromPrefixedTableName(): void
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
    public function testGetTableNamesIsFromTableNameWithDatabase(): void
    {
        $query = $this->createQuery();
        $query->from = 'tickets.workflows';

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{tickets.workflows}}' => '{{tickets.workflows}}',
        ], $tables);
    }

    public function testGetTableNamesIsFromAliasedExpression(): void
    {
        $query = $this->createQuery();
        $expression = new Expression('(SELECT id FROM user)');
        $query->from = $expression;

        $this->expectException('yii\base\InvalidParamException');
        $this->expectExceptionMessage('To use Expression in from() method, pass it in array format with alias.');
        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals(['{{x}}' => $expression], $tables);
    }

    public function testGetTableNamesIsFromAliasedArrayWithExpression(): void
    {
        $query = $this->createQuery();
        $query->from = ['x' => new Expression('(SELECT id FROM user)')];

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{x}}' => '(SELECT id FROM user)',
        ], $tables);
    }

    public function testGetTableNamesIsFromAliasedSubquery(): void
    {
        $query = $this->createQuery();
        $subQuery = $this->createQuery();
        $subQuery->from('user');
        $query->from(['x' => $subQuery]);
        $expected = ['{{x}}' => $subQuery];

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals($expected, $tables);
    }
}
