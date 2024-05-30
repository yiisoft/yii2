<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\base\BaseObject;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yiiunit\TestCase;

/**
 * Unit test emulating console app (without user component) for [[\yii\behaviors\BlameableBehavior]].
 *
 * @group behaviors
 */
class BlameableBehaviorConsoleTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    protected function setUp(): void
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $columns = [
            'name' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_blame', $columns)->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
    }

    public function testDefaultValue()
    {
        $model = new ActiveRecordBlameableConsole([
            'as blameable' => [
                'class' => BlameableBehavior::className(),
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
        $model = new ActiveRecordBlameableConsoleWithDefaultValueClosure();
        $model->name = __METHOD__;
        $model->beforeSave(true);

        $this->assertEquals(11, $model->created_by);
        $this->assertEquals(11, $model->updated_by);
    }
}

class ActiveRecordBlameableConsoleWithDefaultValueClosure extends ActiveRecordBlameableConsole
{
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'defaultValue' => function () {
                    return 10 + 1;
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
class ActiveRecordBlameableConsole extends ActiveRecord
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
