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
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\User;
use yiiunit\TestCase;
use yiiunit\framework\filters\stubs\UserIdentity;

/**
 * @group filters
 */
class AccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    /**
     * @return Action
     */
    private function mockAction()
    {
        $this->mockWebApplication();
        $controller = new Controller('site', Yii::$app);
        return new Action('test', $controller);
    }

    /**
     * @return Action
     */
    private function mockActionWithUser(bool $isGuest = false)
    {
        $this->mockWebApplication([
            'components' => [
                'user' => [
                    'class' => User::class,
                    'identityClass' => UserIdentity::class,
                ],
            ],
        ]);

        if (!$isGuest) {
            Yii::$app->user->setIdentity(UserIdentity::findIdentity('user1'));
        }

        $controller = new Controller('site', Yii::$app);
        return new Action('test', $controller);
    }

    public function testAllowRuleReturnsTrue(): void
    {
        $action = $this->mockAction();

        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                [
                    'allow' => true,
                ],
            ],
        ]);

        $this->assertTrue($filter->beforeAction($action));
    }

    public function testFirstMatchingAllowRuleWins(): void
    {
        $action = $this->mockAction();

        $denyCallbackCalled = false;
        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['test'],
                ],
                [
                    'allow' => false,
                    'actions' => ['test'],
                    'denyCallback' => function ($rule, $action) use (&$denyCallbackCalled) {
                        $denyCallbackCalled = true;
                    },
                ],
            ],
        ]);

        $this->assertTrue($filter->beforeAction($action));
        $this->assertFalse($denyCallbackCalled);
    }

    public function testDenyRuleWithRuleDenyCallback(): void
    {
        $action = $this->mockAction();

        $callbackRule = null;
        $callbackAction = null;
        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                [
                    'allow' => false,
                    'denyCallback' => function ($rule, $action) use (&$callbackRule, &$callbackAction) {
                        $callbackRule = $rule;
                        $callbackAction = $action;
                    },
                ],
            ],
        ]);

        $this->assertFalse($filter->beforeAction($action));
        $this->assertInstanceOf(AccessRule::class, $callbackRule);
        $this->assertSame($action, $callbackAction);
    }

    public function testRuleDenyCallbackTakesPrecedenceOverFilterCallback(): void
    {
        $action = $this->mockAction();

        $ruleCallbackCalled = false;
        $filterCallbackCalled = false;

        $filter = new AccessControl([
            'user' => false,
            'denyCallback' => function ($rule, $action) use (&$filterCallbackCalled) {
                $filterCallbackCalled = true;
            },
            'rules' => [
                [
                    'allow' => false,
                    'denyCallback' => function ($rule, $action) use (&$ruleCallbackCalled) {
                        $ruleCallbackCalled = true;
                    },
                ],
            ],
        ]);

        $filter->beforeAction($action);

        $this->assertTrue($ruleCallbackCalled);
        $this->assertFalse($filterCallbackCalled);
    }

    public function testDenyRuleWithFilterDenyCallback(): void
    {
        $action = $this->mockAction();

        $callbackRule = null;
        $callbackAction = null;
        $filter = new AccessControl([
            'user' => false,
            'denyCallback' => function ($rule, $action) use (&$callbackRule, &$callbackAction) {
                $callbackRule = $rule;
                $callbackAction = $action;
            },
            'rules' => [
                [
                    'allow' => false,
                ],
            ],
        ]);

        $this->assertFalse($filter->beforeAction($action));
        $this->assertInstanceOf(AccessRule::class, $callbackRule);
        $this->assertSame($action, $callbackAction);
    }

    public function testNoMatchingRuleWithFilterDenyCallback(): void
    {
        $action = $this->mockAction();

        $callbackRule = 'not-called';
        $callbackAction = null;
        $filter = new AccessControl([
            'user' => false,
            'denyCallback' => function ($rule, $action) use (&$callbackRule, &$callbackAction) {
                $callbackRule = $rule;
                $callbackAction = $action;
            },
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['other'],
                ],
            ],
        ]);

        $this->assertFalse($filter->beforeAction($action));
        $this->assertNull($callbackRule);
        $this->assertSame($action, $callbackAction);
    }

    public function testNoMatchingRuleWithNoCallbackThrowsForbidden(): void
    {
        $action = $this->mockActionWithUser();

        $filter = new AccessControl([
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['other'],
                ],
            ],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $filter->beforeAction($action);
    }

    public function testEmptyRulesThrowsForbidden(): void
    {
        $action = $this->mockActionWithUser();

        $filter = new AccessControl([
            'rules' => [],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $filter->beforeAction($action);
    }

    public function testNonMatchingRulesSkipped(): void
    {
        $action = $this->mockAction();

        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                [
                    'allow' => false,
                    'actions' => ['other'],
                ],
                [
                    'allow' => true,
                    'actions' => ['test'],
                ],
            ],
        ]);

        $this->assertTrue($filter->beforeAction($action));
    }

    public function testDenyAccessGuestTriggersLoginRequired(): void
    {
        $this->mockWebApplication([
            'components' => [
                'user' => [
                    'class' => User::class,
                    'identityClass' => UserIdentity::class,
                    'enableSession' => false,
                    'loginUrl' => null,
                ],
            ],
        ]);
        $controller = new Controller('site', Yii::$app);
        $action = new Action('test', $controller);

        $filter = new AccessControl([
            'rules' => [
                [
                    'allow' => false,
                ],
            ],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $this->expectExceptionMessage('Login Required');
        $filter->beforeAction($action);
    }

    public function testDenyAccessAuthenticatedThrowsForbidden(): void
    {
        $action = $this->mockActionWithUser(false);

        $filter = new AccessControl([
            'rules' => [
                [
                    'allow' => false,
                ],
            ],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $this->expectExceptionMessage('You are not allowed to perform this action.');
        $filter->beforeAction($action);
    }

    public function testDenyAccessDetachedUserThrowsForbidden(): void
    {
        $action = $this->mockAction();

        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                [
                    'allow' => false,
                ],
            ],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $filter->beforeAction($action);
    }

    public function testRulesInstantiatedFromArrayConfig(): void
    {
        $this->mockWebApplication();

        $filter = new AccessControl([
            'user' => false,
            'rules' => [
                ['allow' => true, 'actions' => ['index']],
                ['allow' => false],
            ],
        ]);

        $this->assertCount(2, $filter->rules);
        $this->assertInstanceOf(AccessRule::class, $filter->rules[0]);
        $this->assertInstanceOf(AccessRule::class, $filter->rules[1]);
        $this->assertTrue($filter->rules[0]->allow);
        $this->assertFalse($filter->rules[1]->allow);
    }

    public function testRulesAcceptAccessRuleObjects(): void
    {
        $this->mockWebApplication();

        $rule = new AccessRule(['allow' => true]);
        $filter = new AccessControl([
            'user' => false,
            'rules' => [$rule],
        ]);

        $this->assertSame($rule, $filter->rules[0]);
    }
}
