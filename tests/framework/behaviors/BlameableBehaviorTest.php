<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yii\base\Object;
use yii\behaviors\BlameableBehavior;
use yii\db\BaseActiveRecord;
use yiiunit\TestCase;
use yii\db\Connection;
use yii\db\ActiveRecord;

/**
 * Unit test for [[\yii\behaviors\BlameableBehavior]].
 *
 * @group behaviors
 */
class BlameableBehaviorTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'user' => [
                    'class' => 'yiiunit\framework\behaviors\UserMock',
                ]
            ]
        ]);

        $columns = [
            'name' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_blame', $columns)->execute();

        $this->getUser()->login(10);
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
    }

    /**
     * @return UserMock
     */
    private function getUser()
    {
        return Yii::$app->get('user');
    }

    public function testInsertUserIsGuest()
    {
        $this->getUser()->logout();

        $model = new ActiveRecordBlameable();
        $model->name = __METHOD__;
        $model->beforeSave(true);

        $this->assertNull($model->created_by);
        $this->assertNull($model->updated_by);
    }

    public function testInsertUserIsNotGuest()
    {
        $model = new ActiveRecordBlameable();
        $model->name = __METHOD__;
        $model->beforeSave(true);

        $this->assertEquals(10, $model->created_by);
        $this->assertEquals(10, $model->updated_by);
    }

    public function testUpdateUserIsNotGuest()
    {
        $model = new ActiveRecordBlameable();
        $model->name = __METHOD__;
        $model->save();

        $this->getUser()->login(20);
        $model = ActiveRecordBlameable::findOne(['name' => __METHOD__]);
        $model->name = __CLASS__;
        $model->save();

        $this->assertEquals(10, $model->created_by);
        $this->assertEquals(20, $model->updated_by);
    }

    public function testInsertCustomValue()
    {
        $model = new ActiveRecordBlameable();
        $model->name = __METHOD__;
        $model->getBlameable()->value = 42;
        $model->beforeSave(true);

        $this->assertEquals(42, $model->created_by);
        $this->assertEquals(42, $model->updated_by);
    }

    public function testInsertClosure()
    {
        $model = new ActiveRecordBlameable();
        $model->name = __METHOD__;
        $model->getBlameable()->value = function ($event) {
            return strlen($event->sender->name); // $model->name;
        };
        $model->beforeSave(true);

        $this->assertEquals(strlen($model->name), $model->created_by);
        $this->assertEquals(strlen($model->name), $model->updated_by);
    }

    public function testCustomAttributesAndEvents()
    {
        $model = new ActiveRecordBlameable([
            'as blameable' => [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'created_by',
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by']
                ]
            ]
        ]);
        $model->name = __METHOD__;

        $this->assertNull($model->created_by);
        $this->assertNull($model->updated_by);

        $model->beforeValidate();
        $this->assertEquals(10, $model->created_by);
        $this->assertNull($model->updated_by);

        $this->getUser()->login(20);
        $model->beforeSave(true);
        $this->assertEquals(20, $model->created_by);
        $this->assertEquals(20, $model->updated_by);
    }

}

/**
 * Test Active Record class with [[BlameableBehavior]] behavior attached.
 *
 * @property string $name
 * @property int $created_by
 * @property int $updated_by
 *
 * @property BlameableBehavior $blameable
 */
class ActiveRecordBlameable extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::className(),
            ],
        ];
    }

    public static function tableName()
    {
        return 'test_blame';
    }

    /**
     * @return BlameableBehavior
     */
    public function getBlameable()
    {
        return $this->getBehavior('blameable');
    }

    public static function primaryKey()
    {
        return ['name'];
    }
}

class UserMock extends Object
{
    public $id;

    public $isGuest = true;

    public function login($id)
    {
        $this->isGuest = false;
        $this->id = $id;
    }

    public function logout()
    {
        $this->isGuest = true;
        $this->id = null;
    }
}
