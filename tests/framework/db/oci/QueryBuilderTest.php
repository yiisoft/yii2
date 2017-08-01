<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\oci\QueryBuilder;
use yii\db\oci\Schema;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group oci
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'oci';

    protected $likeEscapeCharSql = " ESCAPE '!'";
    protected $likeParameterReplacements = [
        '\%' => '!%',
        '\_' => '!_',
        '!' => '!!',
    ];

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL',
                $this->boolean()->notNull()->defaultValue(1),
                'NUMBER(1) DEFAULT 1 NOT NULL',
            ],
        ]);
    }

    public function foreignKeysProvider()
    {
        $tableName = 'T_constraints_3';
        $name = 'CN_constraints_3';
        $pkTableName = 'T_constraints_2';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropForeignKey($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]]) ON DELETE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1', $pkTableName, 'C_id_1', 'CASCADE');
                },
            ],
            'add (2 columns)' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]], [[C_fk_id_2]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]], [[C_id_2]]) ON DELETE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1, C_fk_id_2', $pkTableName, 'C_id_1, C_id_2', 'CASCADE');
                },
            ],
        ];
    }

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function testAddDropDefaultValue($sql, \Closure $builder)
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in Oracle.');
    }

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS ''";
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON TABLE [[comment]] IS 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON TABLE [[comment]] IS ''";
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'DROP SEQUENCE "item_SEQ";'
            . 'CREATE SEQUENCE "item_SEQ" START WITH 6 INCREMENT BY 1 NOMAXVALUE NOCACHE';
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP SEQUENCE "item_SEQ";'
            . 'CREATE SEQUENCE "item_SEQ" START WITH 4 INCREMENT BY 1 NOMAXVALUE NOCACHE';
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function likeConditionProvider()
    {
        /*
         * Different pdo_oci8 versions may or may not implement PDO::quote(), so
         * yii\db\Schema::quoteValue() may or may not quote \.
         */
        $encodedBackslash = substr($this->getDb()->quoteValue('\\'), 1, -1);
        $this->likeParameterReplacements[$encodedBackslash] = '\\';
        return parent::likeConditionProvider();
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            [
                ['in', 'id', range(0, 2500)],

                ' ('
                . '([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', 'id', range(0, 2500)],

                '('
                . '([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', 'id', new TraversableObject(range(0, 2500))],

                '('
                . '([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
        ]);
    }

    protected function generateSprintfSeries($pattern, $from, $to)
    {
        $items = [];
        for ($i = $from; $i <= $to; $i++) {
            $items[] = sprintf($pattern, $i);
        }

        return $items;
    }
}
