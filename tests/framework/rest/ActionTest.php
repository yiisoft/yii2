<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\rest\Action;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rest\stubs\RestController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class ActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'user' => [
                    'identityClass' => UserIdentity::class,
                ],
            ],
        ]);
        Yii::$app->getDb()->createCommand()->createTable(ActionTestModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
    }

    public function testInitWithoutModelClassThrowsException(): void
    {
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestModel::class,
        ]);

        $this->expectException(InvalidConfigException::class);
        new Action('test', $controller);
    }

    public function testFindModelWithSinglePrimaryKey(): void
    {
        $model = new ActionTestModel();
        $model->name = 'find-test';
        $model->save(false);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestModel::class,
        ]);

        $action = new Action('test', $controller, [
            'modelClass' => ActionTestModel::class,
        ]);

        $found = $action->findModel($model->id);

        $this->assertSame($model->id, $found->id);
        $this->assertSame('find-test', $found->name);
    }

    public function testFindModelNotFoundThrowsException(): void
    {
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestModel::class,
        ]);

        $action = new Action('test', $controller, [
            'modelClass' => ActionTestModel::class,
        ]);

        $this->expectException('yii\web\NotFoundHttpException');
        $action->findModel(999);
    }

    public function testFindModelWithCustomCallable(): void
    {
        $model = new ActionTestModel();
        $model->name = 'custom-callable';
        $model->save(false);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestModel::class,
        ]);

        $calledWithAction = null;
        $action = new Action('test', $controller, [
            'modelClass' => ActionTestModel::class,
            'findModel' => function ($id, $actionInstance) use (&$calledWithAction) {
                $calledWithAction = $actionInstance;
                return ActionTestModel::findOne($id);
            },
        ]);

        $found = $action->findModel($model->id);

        $this->assertSame('custom-callable', $found->name);
        $this->assertInstanceOf(Action::class, $calledWithAction);
    }

    public function testFindModelWithCompositePrimaryKey(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(ActionTestCompositeModel::tableName(), [
            'key1' => 'integer',
            'key2' => 'integer',
            'name' => 'string',
            'PRIMARY KEY (key1, key2)',
        ])->execute();
        Yii::$app->getDb()->createCommand()->insert(ActionTestCompositeModel::tableName(), [
            'key1' => 1,
            'key2' => 2,
            'name' => 'composite',
        ])->execute();

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestCompositeModel::class,
        ]);

        $action = new Action('test', $controller, [
            'modelClass' => ActionTestCompositeModel::class,
        ]);

        $found = $action->findModel('1,2');

        $this->assertSame('composite', $found->name);
    }

    public function testFindModelWithCompositePrimaryKeyMismatchThrowsException(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(ActionTestCompositeModel::tableName(), [
            'key1' => 'integer',
            'key2' => 'integer',
            'name' => 'string',
            'PRIMARY KEY (key1, key2)',
        ])->execute();

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ActionTestCompositeModel::class,
        ]);

        $action = new Action('test', $controller, [
            'modelClass' => ActionTestCompositeModel::class,
        ]);

        $this->expectException('yii\web\NotFoundHttpException');
        $action->findModel('1,2,3');
    }
}

/**
 * @property int $id
 * @property string $name
 */
class ActionTestModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_action';
    }
}

/**
 * @property int $key1
 * @property int $key2
 * @property string $name
 */
class ActionTestCompositeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_action_composite';
    }

    public static function primaryKey()
    {
        return ['key1', 'key2'];
    }
}
