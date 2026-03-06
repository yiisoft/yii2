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
use yii\rest\UpdateAction;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rest\stubs\RestController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class UpdateActionTest extends TestCase
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
        Yii::$app->getDb()->createCommand()->createTable(UpdateActionModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
    }

    private function createModel(string $name = 'original'): UpdateActionModel
    {
        $model = new UpdateActionModel();
        $model->name = $name;
        $model->save(false);
        return $model;
    }

    public function testSuccessfulUpdate(): void
    {
        $model = $this->createModel();
        Yii::$app->getRequest()->setBodyParams(['name' => 'updated']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => UpdateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new UpdateAction('update', $controller, [
            'modelClass' => UpdateActionModel::class,
        ]);

        $result = $action->run($model->id);

        $this->assertSame('updated', $result->name);
        $this->assertSame('updated', UpdateActionModel::findOne($model->id)->name);
    }

    public function testCheckAccessIsCalled(): void
    {
        $model = $this->createModel();
        Yii::$app->getRequest()->setBodyParams(['name' => 'access-update']);

        $accessChecked = false;
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => UpdateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new UpdateAction('update', $controller, [
            'modelClass' => UpdateActionModel::class,
            'checkAccess' => function ($actionId, $actionModel) use (&$accessChecked, $model) {
                $accessChecked = true;
                $this->assertSame('update', $actionId);
                $this->assertSame($model->id, $actionModel->id);
            },
        ]);

        $action->run($model->id);

        $this->assertTrue($accessChecked);
    }

    public function testUpdateWithCustomScenario(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(UpdateActionScenarioModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
        Yii::$app->getDb()->createCommand()->insert(UpdateActionScenarioModel::tableName(), ['name' => 'original'])->execute();

        Yii::$app->getRequest()->setBodyParams(['name' => 'scenario-update']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => UpdateActionScenarioModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new UpdateAction('update', $controller, [
            'modelClass' => UpdateActionScenarioModel::class,
            'scenario' => 'custom',
        ]);

        $result = $action->run(1);

        $this->assertSame('scenario-update', $result->name);
        $this->assertSame('custom', $result->scenario);
        $this->assertSame('scenario-update', UpdateActionScenarioModel::findOne(1)->name);
    }

    public function testUpdateNotFound(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'not-found']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => UpdateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new UpdateAction('update', $controller, [
            'modelClass' => UpdateActionModel::class,
        ]);

        $this->expectException('yii\web\NotFoundHttpException');
        $action->run(999);
    }

    public function testSaveFailureWithoutErrorsThrowsException(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(UpdateActionFailingModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
        Yii::$app->getDb()->createCommand()->insert(UpdateActionFailingModel::tableName(), ['name' => 'original'])->execute();

        Yii::$app->getRequest()->setBodyParams(['name' => 'fail-update']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => UpdateActionFailingModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new UpdateAction('update', $controller, [
            'modelClass' => UpdateActionFailingModel::class,
        ]);

        $this->expectException('yii\web\ServerErrorHttpException');
        $action->run(1);
    }
}

/**
 * @property int $id
 * @property string $name
 */
class UpdateActionModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_update_action';
    }

    public function rules()
    {
        return [
            ['name', 'safe'],
        ];
    }
}

/**
 * @property int $id
 * @property string $name
 */
class UpdateActionScenarioModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_update_action_scenario';
    }

    public function rules()
    {
        return [
            ['name', 'safe', 'on' => 'custom'],
        ];
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'custom' => ['name'],
        ]);
    }
}

/**
 * @property int $id
 * @property string $name
 */
class UpdateActionFailingModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_update_action_failing';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        return false;
    }
}
