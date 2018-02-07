<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yii\base\DynamicModel;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\db\Schema;

/**
 * @group db
 * @group mysql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    protected $driverName = 'mysql';

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        $columns = [
            [
                Schema::TYPE_PK . ' AFTER `col_before`',
                $this->primaryKey()->after('col_before'),
                'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY AFTER `col_before`',
            ],
            [
                Schema::TYPE_PK . ' FIRST',
                $this->primaryKey()->first(),
                'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . ' FIRST',
                $this->primaryKey()->first()->after('col_before'),
                'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . '(8) AFTER `col_before`',
                $this->primaryKey(8)->after('col_before'),
                'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY AFTER `col_before`',
            ],
            [
                Schema::TYPE_PK . '(8) FIRST',
                $this->primaryKey(8)->first(),
                'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . '(8) FIRST',
                $this->primaryKey(8)->first()->after('col_before'),
                'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
            ],
            [
                Schema::TYPE_PK . " COMMENT 'test' AFTER `col_before`",
                $this->primaryKey()->comment('test')->after('col_before'),
                "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'test' AFTER `col_before`",
            ],
        ];

        /*
         * TODO Remove in Yii 2.1
         *
         * Disabled due bug in MySQL extension
         * @link https://bugs.php.net/bug.php?id=70384
         */
        if (version_compare(PHP_VERSION, '5.6', '>=')) {
            $columns[] = [
                Schema::TYPE_JSON,
                $this->json(),
                "json",
            ];
        }

        return array_merge(parent::columnTypes(), $columns);
    }

    public function primaryKeysProvider()
    {
        $result = parent::primaryKeysProvider();
        $result['drop'][0] = 'ALTER TABLE {{T_constraints_1}} DROP PRIMARY KEY';
        $result['add'][0] = 'ALTER TABLE {{T_constraints_1}} ADD CONSTRAINT [[CN_pk]] PRIMARY KEY ([[C_id_1]])';
        $result['add (2 columns)'][0] = 'ALTER TABLE {{T_constraints_1}} ADD CONSTRAINT [[CN_pk]] PRIMARY KEY ([[C_id_1]], [[C_id_2]])';
        return $result;
    }

    public function foreignKeysProvider()
    {
        $result = parent::foreignKeysProvider();
        $result['drop'][0] = 'ALTER TABLE {{T_constraints_3}} DROP FOREIGN KEY [[CN_constraints_3]]';
        return $result;
    }

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['create'][0] = 'ALTER TABLE {{T_constraints_2}} ADD INDEX [[CN_constraints_2_single]] ([[C_index_1]])';
        $result['create (2 columns)'][0] = 'ALTER TABLE {{T_constraints_2}} ADD INDEX [[CN_constraints_2_multi]] ([[C_index_2_1]], [[C_index_2_2]])';
        $result['create unique'][0] = 'ALTER TABLE {{T_constraints_2}} ADD UNIQUE INDEX [[CN_constraints_2_single]] ([[C_index_1]])';
        $result['create unique (2 columns)'][0] = 'ALTER TABLE {{T_constraints_2}} ADD UNIQUE INDEX [[CN_constraints_2_multi]] ([[C_index_2_1]], [[C_index_2_2]])';
        return $result;
    }

    public function uniquesProvider()
    {
        $result = parent::uniquesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_unique]] ON {{T_constraints_1}}';
        return $result;
    }

    public function checksProvider()
    {
        $this->markTestSkipped('Adding/dropping check constraints is not supported in MySQL.');
    }

    public function defaultValuesProvider()
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in MySQL.');
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE `item` AUTO_INCREMENT=6';
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE `item` AUTO_INCREMENT=4';
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            // json conditions
            [['=', 'jsoncol', new JsonExpression(['lang' => 'uk', 'country' => 'UA'])], '[[jsoncol]] = :qp0', [':qp0' => '{"lang":"uk","country":"UA"}']],
            [['=', 'jsoncol', new JsonExpression([false])], '[[jsoncol]] = :qp0', [':qp0' => '[false]']],
            'object with type. Type is ignored for MySQL' => [
                ['=', 'prices', new JsonExpression(['seeds' => 15, 'apples' => 25], 'jsonb')],
                '[[prices]] = :qp0', [':qp0' => '{"seeds":15,"apples":25}']
            ],
            'nested json' => [
                ['=', 'data', new JsonExpression(['user' => ['login' => 'silverfire', 'password' => 'c4ny0ur34d17?'], 'props' => ['mood' => 'good']])],
                '[[data]] = :qp0', [':qp0' => '{"user":{"login":"silverfire","password":"c4ny0ur34d17?"},"props":{"mood":"good"}}']
            ],
            'null value' => [['=', 'jsoncol', new JsonExpression(null)], '[[jsoncol]] = :qp0', [':qp0' => 'null']],
            'null as array value' => [['=', 'jsoncol', new JsonExpression([null])], '[[jsoncol]] = :qp0', [':qp0' => '[null]']],
            'null as object value' => [['=', 'jsoncol', new JsonExpression(['nil' => null])], '[[jsoncol[[ = :qp0', [':qp0' => '{"nil":null}']],

            [['=', 'jsoncol', new JsonExpression(new DynamicModel(['a' => 1, 'b' => 2]))], '[[jsoncol]] = :qp0', [':qp0' => '{"a":1,"b":2}']],
            'query' => [
                ['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]))],
                '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]
            ],
            'query with type, that is ignored in MySQL' => [
                ['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]), 'jsonb')],
                '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]
            ],
        ]);
    }
}
