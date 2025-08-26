<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\auth;

use Yii;
use yii\base\Action;
use yii\filters\auth\AuthMethod;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

class AuthMethodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication([
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::class,
                ],
            ],
        ]);
    }

    /**
     * Creates mock for [[AuthMethod]] filter.
     * @param callable $authenticateCallback callback, which result should [[authenticate()]] method return.
     * @return AuthMethod filter instance.
     */
    protected function createFilter($authenticateCallback)
    {
        $filter = $this->createPartialMock(AuthMethod::class, ['authenticate']);
        $filter->method('authenticate')->willReturnCallback($authenticateCallback);

        return $filter;
    }

    /**
     * Creates test action.
     * @param array $config action configuration.
     * @return Action action instance.
     */
    protected function createAction(array $config = [])
    {
        $controller = new Controller('test', Yii::$app);
        return new Action('index', $controller, $config);
    }

    // Tests :

    public function testBeforeAction(): void
    {
        $action = $this->createAction();

        $filter = $this->createFilter(fn() => new \stdClass());
        $this->assertTrue($filter->beforeAction($action));

        $filter = $this->createFilter(fn() => null);
        $this->expectException(UnauthorizedHttpException::class);
        $this->assertTrue($filter->beforeAction($action));
    }

    public function testIsOptional(): void
    {
        $reflection = new \ReflectionClass(AuthMethod::class);
        $method = $reflection->getMethod('isOptional');

        // @link https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_reflectionsetaccessible
        // @link https://wiki.php.net/rfc/make-reflection-setaccessible-no-op
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $filter = $this->createFilter(fn() => new \stdClass());

        $filter->optional = ['some'];
        $this->assertFalse($method->invokeArgs($filter, [$this->createAction(['id' => 'index'])]));
        $this->assertTrue($method->invokeArgs($filter, [$this->createAction(['id' => 'some'])]));

        $filter->optional = ['test/*'];
        $this->assertFalse($method->invokeArgs($filter, [$this->createAction(['id' => 'index'])]));
        $this->assertTrue($method->invokeArgs($filter, [$this->createAction(['id' => 'test/index'])]));
    }
}
