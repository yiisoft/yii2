<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionEvent;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yiiunit\TestCase;

/**
 * @group filters
 */
class VerbFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->mockWebApplication();
    }

    /**
     * @return ActionEvent
     */
    private function mockActionEvent(string $actionId = 'index')
    {
        $controller = new Controller('test', Yii::$app);
        $action = new Action($actionId, $controller);
        return new ActionEvent($action);
    }

    public function testAllowedVerbPasses(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $event = $this->mockActionEvent('view');

        $filter = new VerbFilter([
            'actions' => [
                'view' => ['GET'],
            ],
        ]);

        $filter->beforeAction($event);

        $this->assertTrue($event->isValid);
    }

    public function testMultipleAllowedVerbs(): void
    {
        $filter = new VerbFilter([
            'actions' => [
                'create' => ['GET', 'POST'],
            ],
        ]);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $event = $this->mockActionEvent('create');
        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $event = $this->mockActionEvent('create');
        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);
    }

    public function testDisallowedVerbThrows405(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $event = $this->mockActionEvent('view');

        $filter = new VerbFilter([
            'actions' => [
                'view' => ['GET'],
            ],
        ]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $filter->beforeAction($event);
    }

    public function testDisallowedVerbSetsIsValidFalse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $event = $this->mockActionEvent('view');

        $filter = new VerbFilter([
            'actions' => [
                'view' => ['GET', 'POST'],
            ],
        ]);

        try {
            $filter->beforeAction($event);
        } catch (MethodNotAllowedHttpException $e) {
        }

        $this->assertFalse($event->isValid);
    }

    public function testAllowHeaderSetOnDisallowedVerb(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $event = $this->mockActionEvent('update');

        $filter = new VerbFilter([
            'actions' => [
                'update' => ['GET', 'PUT', 'POST'],
            ],
        ]);

        try {
            $filter->beforeAction($event);
        } catch (MethodNotAllowedHttpException $e) {
        }

        $allowHeader = Yii::$app->getResponse()->getHeaders()->get('Allow');
        $this->assertSame('GET, PUT, POST', $allowHeader);
    }

    public function testExceptionMessageContainsAllowedMethods(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $event = $this->mockActionEvent('delete');

        $filter = new VerbFilter([
            'actions' => [
                'delete' => ['POST', 'DELETE'],
            ],
        ]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $this->expectExceptionMessage('POST, DELETE');
        $filter->beforeAction($event);
    }

    public function testVerbsNormalizedToUpperCase(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $event = $this->mockActionEvent('index');

        $filter = new VerbFilter([
            'actions' => [
                'index' => ['get', 'Post'],
            ],
        ]);

        $filter->beforeAction($event);

        $this->assertTrue($event->isValid);
    }

    public function testWildcardAppliesToUnlistedActions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $event = $this->mockActionEvent('anything');

        $filter = new VerbFilter([
            'actions' => [
                '*' => ['GET'],
            ],
        ]);

        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);
    }

    public function testWildcardBlocksDisallowedVerb(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $event = $this->mockActionEvent('anything');

        $filter = new VerbFilter([
            'actions' => [
                '*' => ['GET'],
            ],
        ]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $filter->beforeAction($event);
    }

    public function testExplicitActionOverridesWildcard(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $event = $this->mockActionEvent('create');

        $filter = new VerbFilter([
            'actions' => [
                'create' => ['GET', 'POST'],
                '*' => ['GET'],
            ],
        ]);

        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);
    }

    public function testWildcardNotUsedWhenExplicitActionExists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $event = $this->mockActionEvent('view');

        $filter = new VerbFilter([
            'actions' => [
                'view' => ['GET'],
                '*' => ['GET', 'POST', 'DELETE'],
            ],
        ]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $filter->beforeAction($event);
    }

    public function testUnlistedActionAllowsAllMethods(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $event = $this->mockActionEvent('unlisted');

        $filter = new VerbFilter([
            'actions' => [
                'index' => ['GET'],
            ],
        ]);

        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);
    }

    public function testEmptyActionsAllowsAllMethods(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $event = $this->mockActionEvent('anything');

        $filter = new VerbFilter([
            'actions' => [],
        ]);

        $filter->beforeAction($event);
        $this->assertTrue($event->isValid);
    }
}
