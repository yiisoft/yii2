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
use yii\rest\ViewAction;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rest\stubs\RestController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class ViewActionTest extends TestCase
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
        Yii::$app->getDb()->createCommand()->createTable(ViewActionModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
    }

    private function createModel(string $name = 'test-view'): ViewActionModel
    {
        $model = new ViewActionModel();
        $model->name = $name;
        $model->save(false);
        return $model;
    }

    public function testSuccessfulView(): void
    {
        $model = $this->createModel();

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ViewActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new ViewAction('view', $controller, [
            'modelClass' => ViewActionModel::class,
        ]);

        $result = $action->run($model->id);

        $this->assertInstanceOf(ViewActionModel::class, $result);
        $this->assertSame($model->id, $result->id);
        $this->assertSame('test-view', $result->name);
    }

    public function testCheckAccessIsCalled(): void
    {
        $model = $this->createModel();

        $accessChecked = false;
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ViewActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new ViewAction('view', $controller, [
            'modelClass' => ViewActionModel::class,
            'checkAccess' => function ($actionId, $actionModel) use (&$accessChecked, $model) {
                $accessChecked = true;
                $this->assertSame('view', $actionId);
                $this->assertSame($model->id, $actionModel->id);
            },
        ]);

        $action->run($model->id);

        $this->assertTrue($accessChecked);
    }

    public function testViewNotFound(): void
    {
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ViewActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new ViewAction('view', $controller, [
            'modelClass' => ViewActionModel::class,
        ]);

        $this->expectException('yii\web\NotFoundHttpException');
        $action->run(999);
    }

    public function testViewWithCustomFindModel(): void
    {
        $model = $this->createModel('custom-find');
        $findModelCalled = false;
        $calledWithAction = null;

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => ViewActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new ViewAction('view', $controller, [
            'modelClass' => ViewActionModel::class,
            'findModel' => function ($id, $actionInstance) use (&$findModelCalled, &$calledWithAction) {
                $findModelCalled = true;
                $calledWithAction = $actionInstance;
                return ViewActionModel::findOne($id);
            },
        ]);

        $result = $action->run($model->id);

        $this->assertTrue($findModelCalled);
        $this->assertSame($action, $calledWithAction);
        $this->assertSame('custom-find', $result->name);
    }
}

/**
 * @property int $id
 * @property string $name
 */
class ViewActionModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_view_action';
    }
}
