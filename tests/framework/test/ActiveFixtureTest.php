<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\test;

use Yii;
use yii\db\Connection;
use yii\test\ActiveFixture;
use yii\test\FixtureTrait;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group fixture
 * @group db
 */
class ActiveFixtureTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->getConnection();
        Yii::$app->set('db', $db);
        ActiveRecord::$db = $db;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetData()
    {
        $test = new CustomerDbTestCase();
        $test->setUp();
        $fixture = $test->getFixture('customers');

        $this->assertEquals(CustomerFixture::className(), get_class($fixture));
        $this->assertCount(2, $fixture);
        $this->assertEquals(1, $fixture['customer1']['id']);
        $this->assertEquals('customer1@example.com', $fixture['customer1']['email']);
        $this->assertEquals(1, $fixture['customer1']['profile_id']);

        $this->assertEquals(2, $fixture['customer2']['id']);
        $this->assertEquals('customer2@example.com', $fixture['customer2']['email']);
        $this->assertEquals(2, $fixture['customer2']['profile_id']);

        $test->tearDown();
    }

    public function testGetModel()
    {
        $test = new CustomerDbTestCase();
        $test->setUp();
        $fixture = $test->getFixture('customers');

        $this->assertEquals(Customer::className(), get_class($fixture->getModel('customer1')));
        $this->assertEquals(1, $fixture->getModel('customer1')->id);
        $this->assertEquals('customer1@example.com', $fixture->getModel('customer1')->email);
        $this->assertEquals(1, $fixture['customer1']['profile_id']);

        $this->assertEquals(2, $fixture->getModel('customer2')->id);
        $this->assertEquals('customer2@example.com', $fixture->getModel('customer2')->email);
        $this->assertEquals(2, $fixture['customer2']['profile_id']);

        $test->tearDown();
    }

    public function testDataDirectory()
    {
        $test = new CustomDirectoryDbTestCase();

        $test->setUp();
        $fixture = $test->getFixture('customers');
        $directory = $fixture->getModel('directory');

        $this->assertEquals(1, $directory->id);
        $this->assertEquals('directory@example.com', $directory['email']);
        $test->tearDown();

    }

    public function testDataPath()
    {
        $test = new DataPathDbTestCase();

        $test->setUp();
        $fixture = $test->getFixture('customers');
        $customer = $fixture->getModel('customer1');

        $this->assertEquals(1, $customer->id);
        $this->assertEquals('customer1@example.com', $customer['email']);
        $test->tearDown();
    }

    public function testTruncate()
    {
        $test = new TruncateTestCase();

        $test->setUp();
        $fixture = $test->getFixture('animals');
        $this->assertEmpty($fixture->data);
        $test->tearDown();
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/14343
     */
    public function testDifferentModelDb()
    {
        $fixture = new DifferentDbFixture();

        $this->assertSame('unique-dsn', $fixture->db->dsn);
        $this->assertNotSame('unique-dsn', Yii::$app->getDb()->dsn);
    }
}

class ProfileFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Profile';

    public function beforeLoad()
    {
        if ($this->db->driverName === 'sqlsrv') {
            $this->db->createCommand()->truncateTable('profile')->execute();
        }

        parent::beforeLoad();
    }

    protected function getData()
    {
        $data = parent::getData();

        if ($this->db->driverName === 'sqlsrv') {
            array_walk($data, static function (&$item) {
                unset($item['id']);
            });
        }

        return $data;
    }
}

class CustomerFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Customer';

    public $depends = [
        'yiiunit\framework\test\ProfileFixture',
    ];

    public function beforeLoad()
    {
        if ($this->db->driverName === 'sqlsrv') {
            $this->db->createCommand()->truncateTable('customer')->execute();
        }

        parent::beforeLoad();
    }
}

class CustomDirectoryFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Customer';

    public $dataDirectory = '@app/framework/test/custom';

    public function beforeLoad()
    {
        if ($this->db->driverName === 'sqlsrv') {
            $this->db->createCommand()->truncateTable('customer')->execute();
        }

        parent::beforeLoad();
    }
}

class AnimalFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Animal';
}

class DifferentDbFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\framework\test\CustomDb';
}

class CustomDb extends ActiveRecord
{
    public static function getDb()
    {
        return new Connection(['dsn' => 'unique-dsn']);
    }
}

class BaseDbTestCase
{
    use FixtureTrait;

    public function setUp(): void
    {
        $this->initFixtures();
    }

    public function tearDown(): void
    {
    }
}

class CustomerDbTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'customers' => CustomerFixture::className(),
        ];
    }
}

class CustomDirectoryDbTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'customers' => CustomDirectoryFixture::className(),
        ];
    }
}

class DataPathDbTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'customers' => [
                'class' => CustomDirectoryFixture::className(),
                'dataFile' => '@app/framework/test/data/customer.php'
            ]
        ];
    }
}

class TruncateTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'animals' => [
                'class' => AnimalFixture::className(),
            ]
        ];
    }
}
