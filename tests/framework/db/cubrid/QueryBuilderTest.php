<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
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
        $version = $this->getQueryBuilder(false)->db->getSlavePdo(true)->getAttribute(\PDO::ATTR_SERVER_VERSION);
        if (version_compare($version, '10.0', '<')) {
            $this->markTestSkipped('Comments on columns are supported starting with CUBRID 10.0.');
            return;
        }

        parent::testCommentColumn();
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => 'MERGE INTO "T_upsert" USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS "EXCLUDED" ("email", "address", "status", "profile_id") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"="EXCLUDED"."address", "status"="EXCLUDED"."status", "profile_id"="EXCLUDED"."profile_id" WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS "EXCLUDED" ("email", "address", "status", "profile_id") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS "EXCLUDED" ("email", "address", "status", "profile_id") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'query' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1) AS "EXCLUDED" ("email", "status") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "status"="EXCLUDED"."status" WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
            ],
            'query with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1) AS "EXCLUDED" ("email", "status") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
            ],
            'query without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1) AS "EXCLUDED" ("email", "status") ON ("T_upsert"."email"="EXCLUDED"."email") WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
            ],
            'values and expressions' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions with update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions without update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'query, values and expressions with update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) AS "EXCLUDED" ("email", [[time]]) ON ({{%T_upsert}}."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) AS "EXCLUDED" ("email", [[time]]) ON ({{%T_upsert}}."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }

        // skip test
        unset($newData['no columns to update']);

        return $newData;
    }
}
