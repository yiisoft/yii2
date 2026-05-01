<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yiiunit\framework\base\stub\actions\LegacyDiscoverableAction;
use yiiunit\framework\base\stub\standalone\ActionTestService;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\base\Module} convention-based standalone action discovery via `Module::$actionNamespace`.
 *
 * Verifies the parallel between controllers and standalone actions: `actionNamespace` defaults to
 * `controllerNamespace`, supports hyphen and sub-namespace prefixes in routes, recurses through sub-modules, yields
 * precedence to controllers when both could match a route, and rejects classes with the `Action` suffix that are
 * actually `Controller` subclasses.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('base')]
final class ModuleActionNamespaceTest extends TestCase
{
    private const string FIXTURE_NAMESPACE = 'yiiunit\\framework\\base\\stub\\actions';
    private const string USECASE_NAMESPACE = 'yiiunit\\framework\\base\\stub\\usecase';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testActionNamespaceDefaultsToControllerNamespaceAfterInit(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        self::assertSame(
            self::FIXTURE_NAMESPACE,
            $module->actionNamespace,
            "'actionNamespace' should default to 'controllerNamespace' when not configured.",
        );
    }

    public function testActionNamespaceHonorsExplicitConfiguration(): void
    {
        $module = new Module(
            'mymod',
            null,
            [
                'controllerNamespace' => self::FIXTURE_NAMESPACE,
                'actionNamespace' => self::USECASE_NAMESPACE,
            ],
        );

        self::assertSame(
            self::USECASE_NAMESPACE,
            $module->actionNamespace,
            "Explicitly configured 'actionNamespace' must override the default.",
        );
    }

    public function testStandaloneActionResolvedByConvention(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $result = $module->runAction('post/index');

        self::assertSame(
            'post-index',
            $result,
            "Convention should resolve route to '<ns>\\post\\IndexAction'.",
        );
    }

    public function testStandaloneActionResolvedWithHyphenInSegment(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $result = $module->runAction('post/view-details');

        self::assertSame(
            'view-details',
            $result,
            'Hyphenated last segment must resolve to CamelCased class name.',
        );
    }

    public function testStandaloneActionResolvedWithSubNamespacePrefix(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $result = $module->runAction('admin/posts/view');

        self::assertSame(
            'admin-posts-view',
            $result,
            'Multi-segment route must traverse sub-namespaces.',
        );
    }

    public function testActionNamespaceOverrideRoutesToCustomRoot(): void
    {
        $module = new Module(
            'mymod',
            null,
            [
                'controllerNamespace' => self::FIXTURE_NAMESPACE,
                'actionNamespace' => self::USECASE_NAMESPACE,
            ],
        );

        $result = $module->runAction('orders/create');

        self::assertSame(
            'usecase-orders-create',
            $result,
            "Custom 'actionNamespace' must root standalone-action lookup outside 'controllerNamespace'.",
        );
    }

    public function testStandaloneActionRecursesIntoSubModule(): void
    {
        $parent = new Module(
            'parent',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $parent->setModule(
            'admin',
            [
                'class' => Module::class,
                'controllerNamespace' => self::FIXTURE_NAMESPACE . '\\admin',
            ],
        );

        $result = $parent->runAction('admin/posts/view');

        self::assertSame(
            'admin-posts-view',
            $result,
            'Sub-module must resolve standalone action through recursion.',
        );
    }

    public function testStandaloneActionRejectsControllerSubclass(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $this->expectException(InvalidRouteException::class);

        $module->runAction('sub/foo');
    }

    public function testThrowInvalidConfigExceptionWhenControllerActionAndStandaloneActionShadow(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Route "mymod/post/view" matches both a controller action and a standalone Action class. '
            . 'Remove one to disambiguate.',
        );

        $module->runAction('post/view');
    }

    public function testThrowInvalidRouteExceptionWhenNoControllerNorActionMatches(): void
    {
        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(
            'Unable to resolve the request "mymod/does-not-exist".',
        );

        $module->runAction('does-not-exist');
    }

    public function testLegacyConstructorActionDiscoveredByNamespaceReceivesNullIdFromDispatcher(): void
    {
        $module = new Module(
            'mymod',
            Yii::$app,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $result = $module->runAction('legacy-discoverable');

        $action = Yii::$app->requestedAction;

        self::assertSame(
            'legacy-discoverable',
            $result,
            'Legacy-shaped action discovered by namespace must execute through the same dispatch path.',
        );
        self::assertInstanceOf(
            LegacyDiscoverableAction::class,
            $action,
            'Resolved action must be the legacy stub.',
        );
        self::assertNull(
            $action->idSeenInConstructor,
            "ID must be 'null' during construction.",
        );
    }

    public function testLegacyConstructorActionDiscoveredByNamespaceObservesNullIdInInit(): void
    {
        $module = new Module(
            'mymod',
            Yii::$app,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $module->runAction('legacy-discoverable');
        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyDiscoverableAction::class,
            $action,
            'Resolved action must be the legacy stub.',
        );
        self::assertNull(
            $action->idSeenInInit,
            "Identity must be 'null' during 'init'.",
        );
        self::assertSame(
            'legacy-discoverable',
            $action->id,
            'Dispatcher must assign the route ID post-construction.',
        );
        self::assertSame(
            'mymod/legacy-discoverable',
            $action->getUniqueId(),
            'Unique ID must combine the module ID and the discovered route segment.',
        );
    }

    public function testStandaloneActionWithPromotedConstructorResolvedByConvention(): void
    {
        Yii::$container->setSingleton(ActionTestService::class, ActionTestService::class);

        $module = new Module(
            'mymod',
            null,
            ['controllerNamespace' => self::FIXTURE_NAMESPACE],
        );

        $result = $module->runAction('health');

        Yii::$container->clear(ActionTestService::class);

        self::assertSame(
            'health-svc',
            $result,
            'Convention-based action with promoted constructor must receive its dependency through the DI container.',
        );
    }
}
