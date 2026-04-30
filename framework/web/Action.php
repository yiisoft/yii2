<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use ReflectionMethod;
use Yii;
use yii\base\Action as BaseAction;

/**
 * Base class for HTTP standalone actions dispatched without a hosting controller.
 *
 * Use this class instead of [[\yii\base\Action]] when an action handles an HTTP request and should benefit from the
 * same parameter-binding semantics as web controller actions: scalar coercion, [[BadRequestHttpException]] on type
 * mismatches and missing required parameters, and DI resolution of typed services through module components,
 * module DI definitions, and the global container.
 *
 * Standalone actions extending [[\yii\base\Action]] still receive DI-based parameter resolution, but skip the
 * HTTP-specific scalar filtering and exception mapping. Reserve that base class for non-HTTP contexts (console actions,
 * queue jobs, scheduled tasks).
 *
 * Usage example:
 *
 * ```php
 * namespace app\controllers;
 *
 * use yii\web\Action;
 * use yii\web\Response;
 *
 * final class HealthAction extends Action
 * {
 *     public function run(int $id, Response $response): Response
 *     {
 *         // 'abc' for $id throws BadRequestHttpException, '7' is coerced to int 7.
 *         return $response;
 *     }
 * }
 * ```
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class Action extends BaseAction
{
    /**
     * Resolves the parameters of {@see run()} using the same HTTP-aware binder as [[Controller::bindActionParams()]].
     *
     * Delegates to [[Controller::bindActionParamsToCallable()]] so scalar parameters are coerced and validated,
     * required parameters raise [[BadRequestHttpException]], and typed services are autowired from the module and
     * the global container.
     *
     * Populates [[\yii\base\Application::$requestedParams]] with the merged action and injected parameter descriptions,
     * mirroring the controller-bound dispatch.
     *
     * @param array $params Route parameters to be bound to {@see run()}.
     * @return array The positional argument list for the action's {@see run()} method.
     */
    protected function resolveStandaloneParams($params): array
    {
        $method = new ReflectionMethod($this, 'run');

        [$args, $actionParams, $requestedParams] = Controller::bindActionParamsToCallable(
            $method,
            $params,
            $this->getModule(),
        );

        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = [
                ...$actionParams,
                ...$requestedParams,
            ];
        }

        return $args;
    }
}
