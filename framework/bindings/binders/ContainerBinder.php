<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class ContainerBinder extends BaseObject implements ParameterBinderInterface
{
    public function bindModel($param, $context)
    {
        $result = null;

        $name = $param->getName();
        $typeName = $param->getTypeName();

        if ($typeName === null) {
            return null;
        }

        $module = $context->action->controller->module;
        $container = \Yii::$container;

        // Since it is not a builtin type it must be DI injection.
        if (($component = $module->get($name, false)) instanceof $typeName) {
            $result  = new BindingResult($component);
            $result->message = "Component: " . get_class($component) . " \$$name";
        } elseif ($module->has($typeName) && ($service = $module->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = 'Module ' . get_class($this->module) . " DI: $typeName \$$name";
        } elseif ($container->has($typeName) && ($service = $container->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = "Container DI: $typeName \$$name";
        } elseif ($param->allowsNull()) {
            $result  = new BindingResult(null);
            $result->message = "Unavailable service: $name";
        }

        return $result;
    }
}
