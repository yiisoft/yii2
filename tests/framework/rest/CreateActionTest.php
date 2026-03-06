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
use yii\helpers\Url;
use yii\rest\CreateAction;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rest\stubs\RestController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class CreateActionTest extends TestCase
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
        Yii::$app->getDb()->createCommand()->createTable(CreateActionModel::tableName(), [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
    }

    public function testSuccessfulCreate(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'test-item']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionModel::class,
        ]);

        $model = $action->run();

        $this->assertInstanceOf(CreateActionModel::class, $model);
        $this->assertSame('test-item', $model->name);
        $this->assertFalse($model->isNewRecord);
        $this->assertSame(201, Yii::$app->getResponse()->getStatusCode());
        $this->assertSame('test-item', CreateActionModel::findOne($model->id)->name);
        $this->assertSame(
            Url::toRoute(['view', 'id' => $model->id], true),
            Yii::$app->getResponse()->getHeaders()->get('Location')
        );
    }

    public function testCreateWithValidationErrors(): void
    {
        Yii::$app->getRequest()->setBodyParams([]);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionValidatingModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionValidatingModel::class,
        ]);

        $model = $action->run();

        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->isNewRecord);
    }

    public function testCreateWithCustomScenario(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'scenario-test']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionScenarioModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionScenarioModel::class,
            'scenario' => 'custom',
        ]);

        $model = $action->run();

        $this->assertFalse($model->isNewRecord);
        $this->assertSame('custom', $model->scenario);
    }

    public function testCheckAccessIsCalled(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'access-test']);

        $accessChecked = false;
        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionModel::class,
            'checkAccess' => function ($actionId) use (&$accessChecked) {
                $accessChecked = true;
                $this->assertSame('create', $actionId);
            },
        ]);

        $action->run();

        $this->assertTrue($accessChecked);
    }

    public function testCustomViewAction(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'view-action-test']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionModel::class,
            'viewAction' => 'detail',
        ]);

        $action->run();

        $location = Yii::$app->getResponse()->getHeaders()->get('Location');
        $this->assertSame(
            Url::toRoute(['detail', 'id' => 1], true),
            $location
        );
    }

    public function testSaveFailureWithoutErrorsThrowsException(): void
    {
        Yii::$app->getRequest()->setBodyParams(['name' => 'fail-test']);

        $controller = new RestController('rest', new RestModule('rest'), [
            'modelClass' => CreateActionFailingModel::class,
        ]);
        Yii::$app->controller = $controller;

        $action = new CreateAction('create', $controller, [
            'modelClass' => CreateActionFailingModel::class,
        ]);

        $this->expectException('yii\web\ServerErrorHttpException');
        $action->run();
    }
}

/**
 * @property int $id
 * @property string $name
 */
class CreateActionModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_create_action';
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
class CreateActionValidatingModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_create_action';
    }

    public function rules()
    {
        return [
            ['name', 'required'],
        ];
    }
}

/**
 * @property int $id
 * @property string $name
 */
class CreateActionScenarioModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_create_action';
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
class CreateActionFailingModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_create_action';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        return false;
    }
}
