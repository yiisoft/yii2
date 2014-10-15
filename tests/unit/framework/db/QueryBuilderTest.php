<?php

namespace yiiunit\framework\db;

use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Schema;
use yii\db\mysql\QueryBuilder as MysqlQueryBuilder;
use yii\db\sqlite\QueryBuilder as SqliteQueryBuilder;
use yii\db\mssql\QueryBuilder as MssqlQueryBuilder;
use yii\db\pgsql\QueryBuilder as PgsqlQueryBuilder;
use yii\db\cubrid\QueryBuilder as CubridQueryBuilder;

/**
 * @group db
 * @group mysql
 */
class QueryBuilderTest extends DatabaseTestCase
{
    /**
     * @throws \Exception
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        switch ($this->driverName) {
            case 'mysql':
                return new MysqlQueryBuilder($this->getConnection(true, false));
            case 'sqlite':
                return new SqliteQueryBuilder($this->getConnection(true, false));
            case 'mssql':
                return new MssqlQueryBuilder($this->getConnection(true, false));
            case 'pgsql':
                return new PgsqlQueryBuilder($this->getConnection(true, false));
            case 'cubrid':
                return new CubridQueryBuilder($this->getConnection(true, false));
        }
        throw new \Exception('Test is not implemented for ' . $this->driverName);
    }

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', 'varchar(255) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', 'varchar(32) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, 'text'],
            [Schema::TYPE_TEXT . '(255)', 'text'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', 'text CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', 'text CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . ' NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_SMALLINT, 'smallint(6)'],
            [Schema::TYPE_SMALLINT . '(8)', 'smallint(8)'],
            [Schema::TYPE_INTEGER, 'int(11)'],
            [Schema::TYPE_INTEGER . '(8)', 'int(8)'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'int(11) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'int(8) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', 'int(11) NOT NULL'],
            [Schema::TYPE_BIGINT, 'bigint(20)'],
            [Schema::TYPE_BIGINT . '(8)', 'bigint(8)'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint(20) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint(8) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', 'bigint(20) NOT NULL'],
            [Schema::TYPE_FLOAT, 'float'],
            [Schema::TYPE_FLOAT . '(16,5)', 'float'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', 'float NOT NULL'],
            [Schema::TYPE_DECIMAL, 'decimal(10,0)'],
            [Schema::TYPE_DECIMAL . '(12,4)', 'decimal(12,4)'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'decimal(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'decimal(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', 'decimal(10,0) NOT NULL'],
            [Schema::TYPE_DATETIME, 'datetime'],
            [Schema::TYPE_DATETIME . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "datetime CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', 'datetime NOT NULL'],
            [Schema::TYPE_TIMESTAMP, 'timestamp'],
            [Schema::TYPE_TIMESTAMP . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', 'timestamp NOT NULL'],
            [Schema::TYPE_TIME, 'time'],
            [Schema::TYPE_TIME . " CHECK(value BETWEEN '12:00:00' AND '13:01:01')", "time CHECK(value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', 'time NOT NULL'],
            [Schema::TYPE_DATE, 'date'],
            [Schema::TYPE_DATE . " CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK(value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'],
            [Schema::TYPE_BINARY, 'blob'],
            [Schema::TYPE_BOOLEAN, 'tinyint(1)'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', 'tinyint(1) NOT NULL DEFAULT 1'],
            [Schema::TYPE_MONEY, 'decimal(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', 'decimal(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'decimal(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'decimal(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', 'decimal(19,4) NOT NULL'],
        ];
    }

    public function testGetColumnType()
    {
        $qb = $this->getQueryBuilder();
        foreach ($this->columnTypes() as $item) {
            list ($column, $expected) = $item;
            $this->assertEquals($expected, $qb->getColumnType($column));
        }
    }

    public function testCreateTableColumnTypes()
    {
        $qb = $this->getQueryBuilder();
        if ($qb->db->getTableSchema('column_type_table', true) !== null) {
            $this->getConnection(false)->createCommand($qb->dropTable('column_type_table'))->execute();
        }
        $columns = [];
        $i = 0;
        foreach ($this->columnTypes() as $item) {
            list ($column, $expected) = $item;
            if (strncmp($column, 'pk', 2) !== 0) {
                $columns['col' . ++$i] = str_replace('CHECK (value', 'CHECK (col' . $i, $column);
            }
        }
        $this->getConnection(false)->createCommand($qb->createTable('column_type_table', $columns))->execute();
    }

    public function conditionProvider()
    {
        $conditions = [
            // empty values
            [ ['like', 'name', []], '0=1', [] ],
            [ ['not like', 'name', []], '', [] ],
            [ ['or like', 'name', []], '0=1', [] ],
            [ ['or not like', 'name', []], '', [] ],

            // simple like
            [ ['like', 'name', 'heyho'], '"name" LIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['not like', 'name', 'heyho'], '"name" NOT LIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['or like', 'name', 'heyho'], '"name" LIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['or not like', 'name', 'heyho'], '"name" NOT LIKE :qp0', [':qp0' => '%heyho%'] ],

            // like for many values
            [ ['like', 'name', ['heyho', 'abc']], '"name" LIKE :qp0 AND "name" LIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['not like', 'name', ['heyho', 'abc']], '"name" NOT LIKE :qp0 AND "name" NOT LIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['or like', 'name', ['heyho', 'abc']], '"name" LIKE :qp0 OR "name" LIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['or not like', 'name', ['heyho', 'abc']], '"name" NOT LIKE :qp0 OR "name" NOT LIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],

            // not
            [ ['not', 'name'], 'NOT (name)', [] ],

            // and
            [ ['and', 'id=1', 'id=2'], '(id=1) AND (id=2)', [] ],
            [ ['and', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) AND ((id=1) OR (id=2))', [] ],

            // or
            [ ['or', 'id=1', 'id=2'], '(id=1) OR (id=2)', [] ],
            [ ['or', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) OR ((id=1) OR (id=2))', [] ],


            // between
            [ ['between', 'id', 1, 10], '"id" BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10] ],
            [ ['not between', 'id', 1, 10], '"id" NOT BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10] ],

            // in
            [ ['in', 'id', [1, 2, 3]], '"id" IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3] ],
            [ ['not in', 'id', [1, 2, 3]], '"id" NOT IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3] ],
            [ ['in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])], '("id") IN (SELECT "id" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],
            [ ['not in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])], '("id") NOT IN (SELECT "id" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],

            // composite in
            [ ['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '("id", "name") IN ((:qp0, :qp1), (:qp2, :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'] ],
            [ ['not in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '("id", "name") NOT IN ((:qp0, :qp1), (:qp2, :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'] ],
            [ ['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], '("id", "name") IN (SELECT "id", "name" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],
            [ ['not in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], '("id", "name") NOT IN (SELECT "id", "name" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],


            // exists
            [ ['exists', (new Query())->select('id')->from('users')->where(['active' => 1])], 'EXISTS (SELECT "id" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],
            [ ['not exists', (new Query())->select('id')->from('users')->where(['active' => 1])], 'NOT EXISTS (SELECT "id" FROM "users" WHERE "active"=:qp0)', [':qp0' => 1] ],

            // simple conditions
            [ ['=', 'a', 'b'], '"a" = :qp0', [':qp0' => 'b'] ],
            [ ['>', 'a', 1], '"a" > :qp0', [':qp0' => 1] ],
            [ ['>=', 'a', 'b'], '"a" >= :qp0', [':qp0' => 'b'] ],
            [ ['<', 'a', 2], '"a" < :qp0', [':qp0' => 2] ],
            [ ['<=', 'a', 'b'], '"a" <= :qp0', [':qp0' => 'b'] ],
            [ ['<>', 'a', 3], '"a" <> :qp0', [':qp0' => 3] ],
            [ ['!=', 'a', 'b'], '"a" != :qp0', [':qp0' => 'b'] ],
        ];

        // adjust dbms specific escaping
        foreach($conditions as $i => $condition) {
            switch ($this->driverName) {
                case 'mssql':
                case 'mysql':
                case 'sqlite':
                    $conditions[$i][1] = str_replace('"', '`', $condition[1]);
                    break;
            }

        }
        return $conditions;
    }

    public function filterConditionProvider()
    {
        $conditions = [
            // like
            [ ['like', 'name', []], '', [] ],
            [ ['not like', 'name', []], '', [] ],
            [ ['or like', 'name', []], '', [] ],
            [ ['or not like', 'name', []], '', [] ],

            // not
            [ ['not', ''], '', [] ],

            // and
            [ ['and', '', ''], '', [] ],
            [ ['and', '', 'id=2'], '(id=2)', [] ],
            [ ['and', 'id=1', ''], '(id=1)', [] ],
            [ ['and', 'type=1', ['or', '', 'id=2']], '(type=1) AND ((id=2))', [] ],

            // or
            [ ['or', 'id=1', ''], '(id=1)', [] ],
            [ ['or', 'type=1', ['or', '', 'id=2']], '(type=1) OR ((id=2))', [] ],


            // between
            [ ['between', 'id', 1, null], '', [] ],
            [ ['not between', 'id', null, 10], '', [] ],

            // in
            [ ['in', 'id', []], '', [] ],
            [ ['not in', 'id', []], '', [] ],

            // simple conditions
            [ ['=', 'a', ''], '', [] ],
            [ ['>', 'a', ''], '', [] ],
            [ ['>=', 'a', ''], '', [] ],
            [ ['<', 'a', ''], '', [] ],
            [ ['<=', 'a', ''], '', [] ],
            [ ['<>', 'a', ''], '', [] ],
            [ ['!=', 'a', ''], '', [] ],
        ];

        // adjust dbms specific escaping
        foreach($conditions as $i => $condition) {
            switch ($this->driverName) {
                case 'mssql':
                case 'mysql':
                case 'sqlite':
                    $conditions[$i][1] = str_replace('"', '`', $condition[1]);
                    break;
            }

        }
        return $conditions;
    }

    /**
     * @dataProvider conditionProvider
     */
    public function testBuildCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->where($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $expected), $sql);
        $this->assertEquals($expectedParams, $params);
    }

    /**
     * @dataProvider filterConditionProvider
     */
    public function testBuildFilterCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->filterWhere($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $expected), $sql);
        $this->assertEquals($expectedParams, $params);
    }

    public function testAddDropPrimaryKey()
    {
        $tableName = 'constraints';
        $pkeyName = $tableName . "_pkey";

        // ADD
        $qb = $this->getQueryBuilder();
        $qb->db->createCommand()->addPrimaryKey($pkeyName, $tableName, ['id'])->execute();
        $tableSchema = $qb->db->getSchema()->getTableSchema($tableName);
        $this->assertEquals(1, count($tableSchema->primaryKey));

        //DROP
        $qb->db->createCommand()->dropPrimaryKey($pkeyName, $tableName)->execute();
        $qb = $this->getQueryBuilder(); // resets the schema
        $tableSchema = $qb->db->getSchema()->getTableSchema($tableName);
        $this->assertEquals(0, count($tableSchema->primaryKey));
    }

    public function existsParamsProvider()
    {
        $conditions = [
            ['exists', "SELECT `id` FROM `TotalExample` `t` WHERE EXISTS (SELECT `1` FROM `Website` `w`)"],
            ['not exists', "SELECT `id` FROM `TotalExample` `t` WHERE NOT EXISTS (SELECT `1` FROM `Website` `w`)"]
        ];

        // adjust dbms specific escaping
        foreach($conditions as $i => $condition) {
            if (!in_array($this->driverName, ['mssql', 'mysql', 'sqlite'])) {
                $conditions[$i][1] = str_replace('`', '"', $condition[1]);
            }
        }
        return $conditions;
    }

    /**
     * @dataProvider existsParamsProvider
     */
    public function testBuildWhereExists($cond, $expectedQuerySql)
    {
        $expectedQueryParams = [];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w');

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where([$cond, $subQuery]);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }


    public function testBuildWhereExistsWithParameters()
    {
        $expectedQuerySql = "SELECT `id` FROM `TotalExample` `t` WHERE (EXISTS (SELECT `1` FROM `Website` `w` WHERE (w.id = t.website_id) AND (w.merchant_id = :merchant_id))) AND (t.some_column = :some_value)";
        if (!in_array($this->driverName, ['mssql', 'mysql', 'sqlite'])) {
            $expectedQuerySql = str_replace('`', '"', $expectedQuerySql);
        }
        $expectedQueryParams = [':some_value' => "asd", ':merchant_id' => 6];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w')
            ->where('w.id = t.website_id')
            ->andWhere('w.merchant_id = :merchant_id', [':merchant_id' => 6]);

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where(['exists', $subQuery])
            ->andWhere('t.some_column = :some_value', [':some_value' => "asd"]);

        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $queryParams);
    }

    public function testBuildWhereExistsWithArrayParameters()
    {
        $expectedQuerySql = "SELECT `id` FROM `TotalExample` `t` WHERE (EXISTS (SELECT `1` FROM `Website` `w` WHERE (w.id = t.website_id) AND ((`w`.`merchant_id`=:qp0) AND (`w`.`user_id`=:qp1)))) AND (`t`.`some_column`=:qp2)";
        if (!in_array($this->driverName, ['mssql', 'mysql', 'sqlite'])) {
            $expectedQuerySql = str_replace('`', '"', $expectedQuerySql);
        }
        $expectedQueryParams = [':qp0' => 6, ':qp1' => 210, ':qp2' => 'asd'];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w')
            ->where('w.id = t.website_id')
            ->andWhere(['w.merchant_id' => 6, 'w.user_id' => '210']);

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where(['exists', $subQuery])
            ->andWhere(['t.some_column' => "asd"]);

        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $queryParams);
    }

    /**
     * This test contains three select queries connected with UNION and UNION ALL constructions.
     * It could be useful to use "phpunit --group=db --filter testBuildUnion" command for run it.
     */
    public function testBuildUnion()
    {
        $expectedQuerySql = "(SELECT `id` FROM `TotalExample` `t1` WHERE (w > 0) AND (x < 2)) UNION ( SELECT `id` FROM `TotalTotalExample` `t2` WHERE w > 5 ) UNION ALL ( SELECT `id` FROM `TotalTotalExample` `t3` WHERE w = 3 )";
        if (!in_array($this->driverName, ['mssql', 'mysql', 'sqlite'])) {
            $expectedQuerySql = str_replace('`', '"', $expectedQuerySql);
        }
        $query = new Query();
        $secondQuery = new Query();
        $secondQuery->select('id')
              ->from('TotalTotalExample t2')
              ->where('w > 5');
        $thirdQuery = new Query();
        $thirdQuery->select('id')
              ->from('TotalTotalExample t3')
              ->where('w = 3');
        $query->select('id')
              ->from('TotalExample t1')
              ->where(['and', 'w > 0', 'x < 2'])
              ->union($secondQuery)
              ->union($thirdQuery, TRUE);
        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
    }
}
