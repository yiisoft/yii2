<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest;

use yii\base\InlineAction;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;
use yiiunit\framework\rest\stubs\RestModule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testVerbsReturnsEmptyArray(): void
    {
        $controller = $this->createController();
        $this->assertSame([], $controller->getVerbs());
    }

    public function testBehaviorsReturnExpectedConfig(): void
    {
        $controller = $this->createController();

        $behaviors = $controller->behaviors();

        $this->assertSame(ContentNegotiator::className(), $behaviors['contentNegotiator']['class']);
        $this->assertSame(
            [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            $behaviors['contentNegotiator']['formats']
        );
        $this->assertSame(VerbFilter::className(), $behaviors['verbFilter']['class']);
        $this->assertSame([], $behaviors['verbFilter']['actions']);
        $this->assertSame(CompositeAuth::className(), $behaviors['authenticator']['class']);
        $this->assertSame(RateLimiter::className(), $behaviors['rateLimiter']['class']);
    }

    public function testSerializeDataUsesConfiguredSerializer(): void
    {
        $controller = new RestBaseController('test', new RestModule('rest'), [
            'serializer' => ControllerSerializerStub::class,
        ]);

        $this->assertSame(
            ['serialized' => ['name' => 'test']],
            $controller->getSerializedData(['name' => 'test'])
        );
    }

    public function testAfterActionSerializesResult(): void
    {
        $controller = new RestBaseController('test', new RestModule('rest'), [
            'serializer' => ControllerSerializerStub::class,
        ]);

        $action = new InlineAction('verbs', $controller, 'getVerbs');

        $this->assertSame(
            ['serialized' => ['id' => 1]],
            $controller->afterAction($action, ['id' => 1])
        );
    }

    private function createController(): RestBaseController
    {
        return new RestBaseController('test', new RestModule('rest'));
    }
}

class RestBaseController extends Controller
{
    public function getVerbs(): array
    {
        return $this->verbs();
    }

    public function getSerializedData(array $data): array
    {
        return $this->serializeData($data);
    }
}

class ControllerSerializerStub
{
    public function serialize($data): array
    {
        return ['serialized' => $data];
    }
}
