<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders;

use Exception;
use Yii;
use yii\base\BaseObject;
use yii\base\InlineAction;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class ParameterBinder extends BaseObject {

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[\yii\base\Action]] when it begins to run with the given parameters.
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * @param \yii\base\Action $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($action->controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        $requestedParams = [];

        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $isValid = true;

                if (PHP_VERSION_ID >= 80000) {
                    $isArray = ($type = $param->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
                } else {
                    $isArray = $param->isArray();
                }

                if ($isArray) {
                    $params[$name] = (array)$params[$name];
                } elseif (is_array($params[$name])) {
                    $isValid = false;
                } elseif (
                    PHP_VERSION_ID >= 70000 &&
                    ($type = $param->getType()) !== null &&
                    $type->isBuiltin() &&
                    ($params[$name] !== null || !$type->allowsNull())
                ) {
                    $typeName = PHP_VERSION_ID >= 70100 ? $type->getName() : (string)$type;
                    switch ($typeName) {
                        case 'int':
                            $params[$name] = filter_var($params[$name], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                            break;
                        case 'float':
                            $params[$name] = filter_var($params[$name], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                            break;
                        case 'bool':
                            $params[$name] = filter_var($params[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            break;
                    }
                    if ($params[$name] === null) {
                        $isValid = false;
                    }
                }
                if (!$isValid) {
                    throw new BadRequestHttpException(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                $args[] = $actionParams[$name] = $params[$name];
                unset($params[$name]);
            } elseif (PHP_VERSION_ID >= 70100 && ($type = $param->getType()) !== null && !$type->isBuiltin()) {
                try {
                    $this->bindInjectedParams($action, $type, $name, $args, $requestedParams);
                } catch (Exception $e) {
                    throw new ServerErrorHttpException($e->getMessage(), 0, $e);
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $action->controller->actionParams = $actionParams;

        // We use a different array here, specifically one that doesn't contain service instances but descriptions instead.
        if (\Yii::$app->requestedParams === null) {
            \Yii::$app->requestedParams = array_merge($actionParams, $requestedParams);
        }

        foreach($args as $arg) {
            if ($arg instanceof RequestBinderInterface) {
                $arg->bind(\Yii::$app->request);
            }
        }

        return $args;
    }

    /**
     * Fills parameters based on types and names in action method signature.
     * @param \ReflectionType $type The reflected type of the action parameter.
     * @param string $name The name of the parameter.
     * @param array &$args The array of arguments for the action, this function may append items to it.
     * @param array &$requestedParams The array with requested params, this function may write specific keys to it.
     * @throws ErrorException when we cannot load a required service.
     * @throws InvalidConfigException Thrown when there is an error in the DI configuration.
     * @throws NotInstantiableException Thrown when a definition cannot be resolved to a concrete class
     * (for example an interface type hint) without a proper definition in the container.
     * @since 2.0.36
     */
    final protected function bindInjectedParams($action, \ReflectionType $type, $name, &$args, &$requestedParams)
    {
        $module = $action->controller->module;
        // Since it is not a builtin type it must be DI injection.
        $typeName = $type->getName();

        if (($component = $module->get($name, false)) instanceof $typeName) {
            $args[] = $component;
            $requestedParams[$name] = "Component: " . get_class($component) . " \$$name";
        } elseif ($module->has($typeName) && ($service = $module->get($typeName)) instanceof $typeName) {
            $args[] = $service;
            $requestedParams[$name] = 'Module ' . get_class($this->module) . " DI: $typeName \$$name";
        } elseif (\Yii::$container->has($typeName) && ($service = \Yii::$container->get($typeName)) instanceof $typeName) {
            $args[] = $service;
            $requestedParams[$name] = "Container DI: $typeName \$$name";
        } elseif ($type->allowsNull()) {
            $args[] = null;
            $requestedParams[$name] = "Unavailable service: $name";
        } else {
            throw new Exception('Could not load required service: ' . $name);
        }
    }
}