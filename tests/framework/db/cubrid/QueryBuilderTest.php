<?php

namespace yiiunit\framework\db\cubrid;

use yii\db\Schema;

/**
 * @group db
 * @group cubrid
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'cubrid';

    protected $likeEscapeCharSql = " ESCAPE '!'";
    protected $likeParameterReplacements = [
        '\%' => '!%',
        '\_' => '!_',
        '\!' => '!!',
        '\\\\' => '\\',
    ];

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), []);
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE "item" AUTO_INCREMENT=6;';
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "item" AUTO_INCREMENT=4;';
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }
}
