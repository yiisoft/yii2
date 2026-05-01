<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use PHPUnit\Framework\Attributes\Group;
use yii\web\Application;
use yiiunit\framework\web\FakeController;
use yiiunit\framework\web\FakeInjectionController;
use yiiunit\framework\web\FakeTypedParamsController;
use yiiunit\framework\web\FakeUnionTypesController;
use yiiunit\framework\web\stubs\ModelBindingStub;
use yii\base\Module;
use yii\data\ArrayDataProvider;
use RuntimeException;
use Yii;
use yii\base\InlineAction;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yiiunit\framework\web\stubs\VendorImage;
use yiiunit\TestCase;

use function get_class;

/**
 * Unit test for {@see \yii\web\Controller}.
 */
#[Group('web')]
#[Group('controller')]
class ControllerTest extends TestCase
{
    private \yii\web\Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();

        $this->controller = new FakeController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        Yii::$app->controller = $this->controller;
    }

    public function testBindActionParams(): void
    {
        $aksi1 = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'validator' => 'available'];

        [$fromGet, $other] = $this->controller->bindActionParams($aksi1, $params);

        self::assertSame(
            'from query params',
            $fromGet,
            'Named parameter must be bound from input.',
        );
        self::assertSame(
            'default',
            $other,
            'Missing parameter must fall back to its default value.',
        );

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'other' => 'available'];

        [$fromGet, $other] = $this->controller->bindActionParams($aksi1, $params);

        self::assertSame(
            'from query params',
            $fromGet,
            'Named parameter must be bound from input.',
        );
        self::assertSame(
            'available',
            $other,
            'Provided value must override the default.',
        );
    }

    public function testNullableInjectedActionParams(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $injectionAction = new InlineAction('injection', $this->controller, 'actionNullableInjection');

        $args = $this->controller->bindActionParams($injectionAction, []);

        self::assertSame(
            Yii::$app->request,
            $args[0],
            'Request component must be injected.',
        );
        self::assertNull(
            $args[1],
            "Nullable injected service must default to 'null'.",
        );
    }

    public function testModelBindingHttpException(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'container' => [
                        'definitions' => [
                            ModelBindingStub::class => [
                                ModelBindingStub::class,
                                'build',
                            ],
                        ],
                    ],
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        Yii::$container->set(VendorImage::class, VendorImage::class);

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionModelBindingInjection');

        $this->expectException(get_class(new NotFoundHttpException('Not Found Item.')));
        $this->expectExceptionMessage('Not Found Item.');
        $this->controller->bindActionParams($injectionAction, []);
    }

    public function testInjectionContainerException(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');

        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];

        Yii::$container->set(
            VendorImage::class,
            static function (): never {
                throw new RuntimeException('uh oh');
            },
        );

        $this->expectException((new RuntimeException())::class);
        $this->expectExceptionMessage(
            'uh oh',
        );

        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testUnknownInjection(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');

        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];

        Yii::$container->clear(VendorImage::class);

        $this->expectException((new ServerErrorHttpException())::class);
        $this->expectExceptionMessage(
            'Could not load required service: vendorImage',
        );

        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testInjectedActionParams(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');

        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];

        Yii::$container->set(VendorImage::class, VendorImage::class);

        $args = $this->controller->bindActionParams($injectionAction, $params);

        self::assertSame(
            $params['before'],
            $args[0],
            'Named scalar must be bound by name.',
        );
        self::assertSame(
            Yii::$app->request,
            $args[1],
            'Request component must be resolved by name.',
        );
        self::assertSame(
            'Component: yii\web\Request $request',
            Yii::$app->requestedParams['request'],
            "Resolution path must be tagged 'Component'.",
        );
        self::assertSame(
            $params['between'],
            $args[2],
            'Named scalar between injections must be bound.',
        );
        self::assertInstanceOf(
            VendorImage::class,
            $args[3],
            'Container DI must resolve the typed service.',
        );
        self::assertSame(
            'Container DI: yiiunit\framework\web\stubs\VendorImage $vendorImage',
            Yii::$app->requestedParams['vendorImage'],
            "Resolution path must be tagged 'Container DI'.",
        );
        self::assertNull(
            $args[4],
            "Unregistered nullable service must default to 'null'.",
        );
        self::assertSame(
            'Unavailable service: post',
            Yii::$app->requestedParams['post'],
            'Resolution path must record the unavailable note.',
        );
        self::assertSame(
            $params['after'],
            $args[5],
            'Trailing named scalar must be bound.',
        );
    }

    public function testInjectedActionParamsFromModule(): void
    {
        $module = new Module(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $module->set(
            'yii\data\DataProviderInterface',
            ['class' => ArrayDataProvider::class],
        );

        $this->controller = new FakeInjectionController('fake', $module);

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionModuleServiceInjection');

        $args = $this->controller->bindActionParams($injectionAction, []);

        self::assertInstanceOf(
            ArrayDataProvider::class,
            $args[0],
            'Module DI must resolve the typed service.',
        );
        self::assertSame(
            'Module yii\base\Module DI: yii\data\DataProviderInterface $dataProvider',
            Yii::$app->requestedParams['dataProvider'],
            "Resolution path must be tagged 'Module'.",
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17701
     */
    public function testBindTypedActionParams(): void
    {
        $this->controller = new FakeTypedParamsController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $this->mockWebApplication(['controller' => $this->controller]);

        $aksi1 = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $params = ['foo' => '100', 'bar' => null, 'true' => 'on', 'false' => 'false', 'string' => 'strong'];

        [$foo, $bar, $true, $false, $string] = $this->controller->bindActionParams($aksi1, $params);

        self::assertSame(
            100,
            $foo,
            'Numeric string must be coerced to int.',
        );
        self::assertNull(
            $bar,
            "Explicit 'null' must remain 'null'.",
        );
        self::assertTrue(
            $true,
            "'on' must be coerced to 'true'.",
        );
        self::assertFalse(
            $false,
            "'false' must be coerced to 'false'.",
        );
        self::assertSame(
            'strong',
            $string,
            'String must pass through.',
        );

        // allow nullable argument to be set to empty string (as null)
        // https://github.com/yiisoft/yii2/issues/18450
        $params = ['foo' => 100, 'bar' => '', 'true' => true, 'false' => true, 'string' => 'strong'];

        [, $bar] = $this->controller->bindActionParams($aksi1, $params);

        self::assertNull(
            $bar,
            "Empty string for nullable scalar must coerce to 'null'.",
        );

        // make sure nullable string argument is not set to null when empty string is passed
        $stringy = new InlineAction('stringy', $this->controller, 'actionStringy');

        [$foo] = $this->controller->bindActionParams($stringy, ['foo' => '']);

        self::assertSame(
            '',
            $foo,
            'Empty string for nullable string must remain empty.',
        );

        // make sure mixed type works
        $mixedParameter = new InlineAction('mixed-parameter', $this->controller, 'actionMixedParameter');

        [$foo] = $this->controller->bindActionParams($mixedParameter, ['foo' => 100]);

        self::assertSame(
            100,
            $foo,
            'Mixed parameter must accept int.',
        );

        [$foo] = $this->controller->bindActionParams($mixedParameter, ['foo' => 'foobar']);

        self::assertSame(
            'foobar',
            $foo,
            'Mixed parameter must accept string.',
        );

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(
            'Invalid data received for parameter "foo".',
        );

        $this->controller->bindActionParams($aksi1, ['foo' => 'oops', 'bar' => null]);
    }

    public function testAsJson(): void
    {
        $data = ['test' => 123, 'example' => 'data'];

        $result = $this->controller->asJson($data);

        self::assertInstanceOf(
            Response::class,
            $result,
            'Result must be a Response.',
        );
        self::assertSame(
            Yii::$app->response,
            $result,
            'Returned response must be the application response.',
        );
        self::assertSame(
            Response::FORMAT_JSON,
            $result->format,
            'Format must be JSON.',
        );
        self::assertSame(
            $data,
            $result->data,
            'Data payload must round-trip.',
        );
    }

    public function testAsXml(): void
    {
        $data = ['test' => 123, 'example' => 'data'];

        $result = $this->controller->asXml($data);

        self::assertInstanceOf(
            Response::class,
            $result,
            'Result must be a Response.',
        );
        self::assertSame(
            Yii::$app->response,
            $result,
            'Returned response must be the application response.',
        );
        self::assertSame(
            Response::FORMAT_XML,
            $result->format,
            'Format must be XML.',
        );
        self::assertSame(
            $data,
            $result->data,
            'Data payload must round-trip.',
        );
    }

    public function testRedirect(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://test-domain.com/';

        self::assertSame(
            '/',
            $this->controller->redirect('')->headers->get('location'),
            "Empty target must redirect to '/'.",
        );
        self::assertSame(
            'http://some-external-domain.com',
            $this->controller->redirect('http://some-external-domain.com')->headers->get('location'),
            'Absolute URL must pass through.',
        );
        self::assertSame(
            '/',
            $this->controller->redirect('/')->headers->get('location'),
            "Root path must redirect to '/'.",
        );
        self::assertSame(
            '/something-relative',
            $this->controller->redirect('/something-relative')->headers->get('location'),
            'Relative path must pass through.',
        );
        self::assertSame(
            '/index.php?r=',
            $this->controller->redirect(['/'])->headers->get('location'),
            "Empty route array must produce empty 'r' query.",
        );
        self::assertSame(
            '/index.php?r=fake%2Fview',
            $this->controller->redirect(['view'])->headers->get('location'),
            'Relative action must resolve under the controller.',
        );
        self::assertSame(
            '/index.php?r=controller',
            $this->controller->redirect(['/controller'])->headers->get('location'),
            'Absolute controller route must resolve.',
        );
        self::assertSame(
            '/index.php?r=controller%2Findex',
            $this->controller->redirect(['/controller/index'])->headers->get('location'),
            'Absolute controller/action route must resolve.',
        );
        self::assertSame(
            '/index.php?r=controller%2Findex',
            $this->controller->redirect(['//controller/index'])->headers->get('location'),
            'Double-slash absolute route must resolve.',
        );
        self::assertSame(
            '/index.php?r=controller%2Findex&id=3',
            $this->controller->redirect(['//controller/index', 'id' => 3])->headers->get('location'),
            'Query parameter must be appended.',
        );
        self::assertSame(
            '/index.php?r=controller%2Findex&id_1=3&id_2=4',
            $this->controller->redirect(['//controller/index', 'id_1' => 3, 'id_2' => 4])->headers->get('location'),
            'Multiple query parameters must be appended.',
        );
        self::assertSame(
            '/index.php?r=controller%2Findex&slug=%C3%A4%C3%B6%C3%BC%C3%9F%21%22%C2%A7%24%25%26%2F%28%29',
            $this->controller->redirect(['//controller/index', 'slug' => 'äöüß!"§$%&/()'])->headers->get('location'),
            'Unicode and special characters must be URL-encoded.',
        );
    }

    public function testUnionBindingActionParams(): void
    {
        $this->controller = new FakeUnionTypesController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');

        $args = $this->controller->bindActionParams($injectionAction, ['arg' => 'test', 'second' => 1]);

        self::assertSame(
            'test',
            $args[0],
            "String must bind to 'int|string' union.",
        );
        self::assertSame(
            1,
            $args[1],
            "Int must bind to 'int|string' union.",
        );

        // test that a value PHP parsed to a string but that should be an int becomes one
        $args = $this->controller->bindActionParams($injectionAction, ['arg' => 'test', 'second' => '1']);

        self::assertSame(
            'test',
            $args[0],
            "String must bind to 'int|string' union."
        );
        self::assertSame(
            1,
            $args[1],
            'Numeric string must coerce to int via the union partial.'
        );
    }

    public function testUnionBindingActionParamsWithArray(): void
    {
        $this->controller = new FakeUnionTypesController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                ],
            ),
        );

        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('array-or-int', $this->controller, 'actionArrayOrInt');

        $args = $this->controller->bindActionParams($injectionAction, ['foo' => 1]);

        self::assertSame(
            1,
            $args[0],
            "Int must bind to 'array|int' union.",
        );

        $args = $this->controller->bindActionParams($injectionAction, ['foo' => [1, 2, 3, 4]]);

        self::assertSame(
            [1, 2, 3, 4],
            $args[0],
            "Array must bind to 'array|int' union.",
        );
    }

    public function testBindArrayActionParamCoercesScalarToArray(): void
    {
        $this->controller = new FakeTypedParamsController('fake', Yii::$app);

        $action = new InlineAction('array-param', $this->controller, 'actionArrayParam');

        [$list] = $this->controller->bindActionParams($action, ['list' => 'item']);

        self::assertSame(
            ['item'],
            $list,
            'Scalar must be coerced to a single-element array.',
        );
    }

    public function testBindFloatActionParamCoercesNumericString(): void
    {
        $this->controller = new FakeTypedParamsController('fake', Yii::$app);

        $action = new InlineAction('float-param', $this->controller, 'actionFloatParam');

        [$foo] = $this->controller->bindActionParams($action, ['foo' => '3.14']);

        self::assertSame(
            3.14,
            $foo,
            'Numeric string must be coerced to float.',
        );
    }

    public function testBindNullableUnionActionParamReturnsEmptyStringWhenStringIsAllowed(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('nullable-union-string', $this->controller, 'actionNullableUnionString');

        [$arg] = $this->controller->bindActionParams($action, ['arg' => '']);

        self::assertSame(
            '',
            $arg,
            'Empty string must remain an empty string.',
        );
    }

    public function testBindNullableUnionActionParamReturnsNullWhenStringIsNotAllowed(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction(
            'nullable-union-without-string',
            $this->controller,
            'actionNullableUnionWithoutString',
        );

        [$arg] = $this->controller->bindActionParams($action, ['arg' => '']);

        self::assertNull(
            $arg,
            "Empty string must be coerced to 'null'.",
        );
    }

    public function testBindUnionActionParamSkipsNonBuiltinTypesAndCoercesBuiltin(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('union-with-object', $this->controller, 'actionUnionWithObject');

        [$arg] = $this->controller->bindActionParams($action, ['arg' => '42']);

        self::assertSame(
            42,
            $arg,
            'Numeric string must be coerced via the builtin partial.',
        );
    }

    public function testBindUnionActionParamPassesThroughWhenNoBuiltinIsDeclared(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('union-with-object-only', $this->controller, 'actionUnionWithObjectOnly');

        [$arg] = $this->controller->bindActionParams($action, ['arg' => 'opaque']);

        self::assertSame(
            'opaque',
            $arg,
            'Raw value must pass through unchanged.',
        );
    }

    public function testBindUnionActionParamContinuesIterationForArrayParamWhenArrayTypeNotYetSeen(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('int-or-array', $this->controller, 'actionIntOrArray');

        [$foo] = $this->controller->bindActionParams($action, ['foo' => [10, 20]]);

        self::assertSame(
            [10, 20],
            $foo,
            'Array value must bind through the array partial.',
        );
    }

    public function testBindNullableUnionActionParamSkipsNonBuiltinPartialBeforeReturningEmptyString(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);
        $action = new InlineAction(
            'nullable-object-string-union',
            $this->controller,
            'actionNullableObjectStringUnion',
        );

        [$arg] = $this->controller->bindActionParams($action, ['arg' => '']);

        self::assertSame('', $arg, 'Non-builtin partial must be skipped.');
    }

    public function testRenderAjaxReturnsViewOutput(): void
    {
        $output = $this->controller->renderAjax('@yiiunit/data/views/simple.php');

        self::assertStringContainsString(
            'damn simple view file',
            $output,
            'Output must include the view body.',
        );
    }

    public function testBeforeActionReturnsFalseWhenEventListenerInvalidatesIt(): void
    {
        $action = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $this->controller->on(
            \yii\base\Controller::EVENT_BEFORE_ACTION,
            static function ($event): void {
                $event->isValid = false;
            },
        );

        self::assertFalse(
            $this->controller->beforeAction($action),
            "Invalid event must yield 'false'.",
        );
    }

    public function testGoHomeRedirectsToHomeUrl(): void
    {
        $response = $this->controller->goHome();

        self::assertSame(
            Yii::$app->getHomeUrl(),
            $response->headers->get('location'),
            'Location must point to the home URL.',
        );
    }

    public function testGoBackRedirectsToReturnUrl(): void
    {
        Yii::$app->set(
            'user',
            [
                'class' => \yii\web\User::class,
                'identityClass' => UserIdentity::class,
            ],
        );

        $response = $this->controller->goBack('/elsewhere');

        self::assertSame(
            '/elsewhere',
            $response->headers->get('location'),
            'Location must equal the default return URL.',
        );
    }

    public function testRefreshRedirectsToCurrentUrlWithAnchor(): void
    {
        $_SERVER['REQUEST_URI'] = '/page';

        $response = $this->controller->refresh('#section');

        self::assertSame(
            '/page#section',
            $response->headers->get('location'),
            'Location must equal current URL plus anchor.',
        );
    }

    public function testThrowBadRequestHttpExceptionWhenArrayPassedToScalarActionParam(): void
    {
        $this->controller = new FakeTypedParamsController('fake', Yii::$app);

        $action = new InlineAction('int-param', $this->controller, 'actionIntParam');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(
            'Invalid data received for parameter "foo".',
        );

        $this->controller->bindActionParams($action, ['foo' => [1, 2]]);
    }

    public function testThrowBadRequestHttpExceptionWhenArrayPassedToUnionWithoutArrayPartial(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('int-or-float', $this->controller, 'actionIntOrFloat');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(
            'Invalid data received for parameter "foo".',
        );

        $this->controller->bindActionParams($action, ['foo' => [1, 2]]);
    }

    public function testThrowBadRequestHttpExceptionWhenCsrfTokenIsInvalid(): void
    {
        $this->controller->enableCsrfValidation = true;

        $_POST[Yii::$app->getRequest()->methodParam] = 'POST';

        Yii::$app->getRequest()->setBodyParams([Yii::$app->getRequest()->csrfParam => 'bogus-token']);

        $action = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(
            'Unable to verify your data submission.',
        );

        try {
            $this->controller->beforeAction($action);
        } finally {
            unset($_POST[Yii::$app->getRequest()->methodParam]);
        }
    }

    public function testThrowBadRequestHttpExceptionWhenUnionActionParamHasNoMatchingBuiltin(): void
    {
        $this->controller = new FakeUnionTypesController('fake', Yii::$app);

        $action = new InlineAction('int-or-float', $this->controller, 'actionIntOrFloat');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(
            'Invalid data received for parameter "foo".',
        );

        $this->controller->bindActionParams($action, ['foo' => 'oops']);
    }
}
