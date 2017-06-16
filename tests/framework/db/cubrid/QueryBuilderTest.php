<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\cubrid;

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

    public function checksProvider()
    {
        $this->markTestSkipped('Adding/dropping check constraints is not supported in CUBRID.');
    }

    public function defaultValuesProvider()
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in CUBRID.');
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

    public function testCommentColumn()
    {
        $version = $this->getQueryBuilder(false)->db->getSlavePdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        if (version_compare($version, '10.0', '<')) {
            $this->markTestSkipped('Comments on columns are supported starting with CUBRID 10.0.');
            return;
        }

        parent::testCommentColumn();
    }
}
