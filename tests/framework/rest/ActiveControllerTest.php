<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class ActiveControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testInitWithoutModelClassThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        new ActiveControllerTestController('test', new RestModule('rest'));
    }

    /**
     * @dataProvider actionsDataProvider
     */
    public function testActionsReturnDefaultConfig(
        string $actionId,
        string $expectedClass,
        bool $hasModelClass,
        bool $hasCheckAccess,
        ?string $expectedScenario
    ): void {
        $controller = $this->createController();
        $actions = $controller->actions();

        $this->assertCount(6, $actions);
        $this->assertArrayHasKey($actionId, $actions);
        $this->assertSame($expectedClass, $actions[$actionId]['class']);

        if ($hasModelClass) {
            $this->assertSame(ActiveControllerModel::class, $actions[$actionId]['modelClass']);
        } else {
            $this->assertArrayNotHasKey('modelClass', $actions[$actionId]);
        }

        if ($hasCheckAccess) {
            $this->assertSame([$controller, 'checkAccess'], $actions[$actionId]['checkAccess']);
        } else {
            $this->assertArrayNotHasKey('checkAccess', $actions[$actionId]);
        }

        if ($expectedScenario !== null) {
            $this->assertSame($expectedScenario, $actions[$actionId]['scenario']);
        } else {
            $this->assertArrayNotHasKey('scenario', $actions[$actionId]);
        }
    }

    /**
     * @dataProvider verbsDataProvider
     */
    public function testVerbsReturnsExpectedMethods(string $actionId, array $expectedVerbs): void
    {
        $controller = $this->createController();

        $verbs = new \ReflectionMethod($controller, 'verbs');
        $verbs->setAccessible(true);
        $result = $verbs->invoke($controller);

        $this->assertSame($expectedVerbs, $result[$actionId]);
    }

    public function testCheckAccessDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $controller = $this->createController();
        $controller->checkAccess('view');
        $controller->checkAccess('update', null, ['id' => 1]);
    }

    public function testCustomScenarios(): void
    {
        $controller = new ActiveControllerTestController('test', new RestModule('rest'), [
            'modelClass' => ActiveControllerModel::class,
            'createScenario' => 'custom-create',
            'updateScenario' => 'custom-update',
        ]);

        $actions = $controller->actions();

        $this->assertSame('custom-create', $actions['create']['scenario']);
        $this->assertSame('custom-update', $actions['update']['scenario']);
    }

    private function createController(): ActiveControllerTestController
    {
        return new ActiveControllerTestController('test', new RestModule('rest'), [
            'modelClass' => ActiveControllerModel::class,
        ]);
    }

    public static function actionsDataProvider(): array
    {
        return [
            'index' => ['index', 'yii\rest\IndexAction', true, true, null],
            'view' => ['view', 'yii\rest\ViewAction', true, true, null],
            'create' => ['create', 'yii\rest\CreateAction', true, true, Model::SCENARIO_DEFAULT],
            'update' => ['update', 'yii\rest\UpdateAction', true, true, Model::SCENARIO_DEFAULT],
            'delete' => ['delete', 'yii\rest\DeleteAction', true, true, null],
            'options' => ['options', 'yii\rest\OptionsAction', false, false, null],
        ];
    }

    public static function verbsDataProvider(): array
    {
        return [
            'index' => ['index', ['GET', 'HEAD']],
            'view' => ['view', ['GET', 'HEAD']],
            'create' => ['create', ['POST']],
            'update' => ['update', ['PUT', 'PATCH']],
            'delete' => ['delete', ['DELETE']],
        ];
    }
}

class ActiveControllerTestController extends ActiveController
{
}

/**
 * @property int $id
 * @property string $name
 */
class ActiveControllerModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'active_controller_model';
    }
}
