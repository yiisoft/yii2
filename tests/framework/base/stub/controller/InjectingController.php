<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use LogicException;
use ReflectionMethod;
use ReflectionNamedType;
use yii\base\Controller;
use yii\data\DataProviderInterface;
use yii\web\Cookie;

/**
 * Controller exposing typed action parameters used to drive {@see Controller::bindInjectedParams()} branches.
 *
 * Encapsulates the reflection plumbing required to extract a {@see ReflectionNamedType} from a target action so the
 * surrounding test files can call {@see bindInjectedFor()} without using reflection directly.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class InjectingController extends Controller
{
    public function actionComponent(Cookie $cookie): void
    {
    }

    public function actionModuleService(DataProviderInterface $dataProvider): void
    {
    }

    public function actionContainerService(ServiceStub $service): void
    {
    }

    public function actionNullableService(?ServiceStub $service): void
    {
    }

    /**
     * Drives {@see Controller::bindInjectedParams()} for the typed parameter named `$paramName` of the given action
     * method, populating `$args` and `$requestedParams` exactly as the framework would during dispatch.
     *
     * @param array<int, mixed> $args
     * @param array<string, string> $requestedParams
     */
    public function bindInjectedFor(
        string $actionMethod,
        string $paramName,
        array &$args,
        array &$requestedParams,
    ): void {
        $type = (new ReflectionMethod($this, $actionMethod))->getParameters()[0]->getType();

        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException(
                "Stub action '{$actionMethod}' must declare a single named type parameter.",
            );
        }

        $this->bindInjectedParams($type, $paramName, $args, $requestedParams);
    }
}
