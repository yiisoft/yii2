<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest;

use Yii;
use yii\db\ActiveRecord;
use yii\rest\DeleteAction;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rest\stubs\RestController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class DeleteActionTest extends TestCase
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
        Yii::$app->getDb()->createCommand()->createTable(DeleteActionModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
    }

    private function createModel(string $name = 'to-delete'): DeleteActionModel
    {
        $model = new DeleteActionModel();
        $model->name = $name;
        $model->save(false);
        return $model;
    }

    public function testSuccessfulDelete(): void
    {
        $model = $this->createModel();
        $id = $model->id;

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => DeleteActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new DeleteAction('delete', $controller, [
            'modelClass' => DeleteActionModel::class,
        ]);

        $action->run($id);

        $this->assertSame(204, Yii::$app->getResponse()->getStatusCode());
        $this->assertNull(DeleteActionModel::findOne($id));
    }

    public function testCheckAccessIsCalled(): void
    {
        $model = $this->createModel();

        $accessChecked = false;
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => DeleteActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new DeleteAction('delete', $controller, [
            'modelClass' => DeleteActionModel::class,
            'checkAccess' => function ($actionId, $actionModel) use (&$accessChecked, $model) {
                $accessChecked = true;
                $this->assertSame('delete', $actionId);
                $this->assertSame($model->id, $actionModel->id);
            },
        ]);

        $action->run($model->id);

        $this->assertTrue($accessChecked);
    }

    public function testDeleteNotFound(): void
    {
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => DeleteActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new DeleteAction('delete', $controller, [
            'modelClass' => DeleteActionModel::class,
        ]);

        $this->expectException('yii\web\NotFoundHttpException');
        $action->run(999);
    }

    public function testDeleteFailureThrowsException(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(DeleteActionFailingModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
        Yii::$app->getDb()->createCommand()->insert(DeleteActionFailingModel::tableName(), ['name' => 'no-delete'])->execute();

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => DeleteActionFailingModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new DeleteAction('delete', $controller, [
            'modelClass' => DeleteActionFailingModel::class,
        ]);

        $this->expectException('yii\web\ServerErrorHttpException');
        $action->run(1);
    }
}

/**
 * @property int $id
 * @property string $name
 */
class DeleteActionModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_delete_action';
    }
}

/**
 * @property int $id
 * @property string $name
 */
class DeleteActionFailingModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_delete_action_failing';
    }

    public function delete()
    {
        return false;
    }
}
