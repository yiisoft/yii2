<?php

namespace yiiunit\extensions\mongodb\file;

use yii\mongodb\file\Query;
use yiiunit\extensions\mongodb\MongoDbTestCase;

/**
 * @group mongodb
 */
class QueryTest extends MongoDbTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpTestRows();
    }

    protected function tearDown()
    {
        $this->dropFileCollection();
        parent::tearDown();
    }

    /**
     * Sets up test rows.
     */
    protected function setUpTestRows()
    {
        $collection = $this->getConnection()->getFileCollection();
        for ($i = 1; $i <= 10; $i++) {
            $collection->insertFileContent('content' . $i, [
                'filename' => 'name' . $i,
                'file_index' => $i,
            ]);
        }
    }

    // Tests :

    public function testAll()
    {
        $connection = $this->getConnection();
        $query = new Query;
        $rows = $query->from('fs')->all($connection);
        $this->assertEquals(10, count($rows));
    }

    public function testOne()
    {
        $connection = $this->getConnection();
        $query = new Query;
        $row = $query->from('fs')->one($connection);
        $this->assertTrue(is_array($row));
        $this->assertTrue($row['file'] instanceof \MongoGridFSFile);
    }

    public function testDirectMatch()
    {
        $connection = $this->getConnection();
        $query = new Query;
        $rows = $query->from('fs')
            ->where(['file_index' => 5])
            ->all($connection);
        $this->assertEquals(1, count($rows));
        /** @var $file \MongoGridFSFile */
        $file = $rows[0];
        $this->assertEquals('name5', $file['filename']);
    }
}
