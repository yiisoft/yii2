<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yiiunit\TestCase;

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
                    '__class' => \yii\db\Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
                'user' => [
                    '__class' => \yiiunit\framework\behaviors\UserMock::class,
                ],
            ],
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
        $model->getBlameable()->value = function (Event $event) {
            return strlen($event->getTarget()->name); // $model->name;
        };
        $model->beforeSave(true);

        $this->assertEquals(strlen($model->name), $model->created_by);
        $this->assertEquals(strlen($model->name), $model->updated_by);
    }

    public function testCustomAttributesAndEvents()
    {
        $model = new ActiveRecordBlameable([
            'as blameable' => [
                '__class' => BlameableBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'created_by',
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                ],
            ],
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

    public function testDefaultValue()
    {
        $this->getUser()->logout();

        $model = new ActiveRecordBlameable([
            'as blameable' => [
                '__class' => BlameableBehavior::class,
                'defaultValue' => 2
            ],
        ]);

        $model->name = __METHOD__;
        $model->beforeSave(true);

        $this->assertEquals(2, $model->created_by);
        $this->assertEquals(2, $model->updated_by);
    }

    public function testDefaultValueWithClosure()
    {
        $model = new ActiveRecordBlameableWithDefaultValueClosure();
        $model->name = __METHOD__;
        $model->beforeSave(true);

        $this->getUser()->logout();
        $model->beforeSave(true);

        $this->assertEquals(11, $model->created_by);
        $this->assertEquals(11, $model->updated_by);
    }
}

class ActiveRecordBlameableWithDefaultValueClosure extends ActiveRecordBlameable
{
    public function behaviors()
    {
        return [
            'blameable' => [
                '__class' => BlameableBehavior::class,
                'defaultValue' => function () {
                    return $this->created_by + 1;
                }
            ],
        ];
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
                '__class' => BlameableBehavior::class,
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

class UserMock extends BaseObject
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
