<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ModelBinderInterface;

final class ContainerBinder extends BaseObject implements ModelBinderInterface
{
    public function bindModel($target, $context)
    {
        $result = null;
        $name = $target->getName();
        $typeName = $target->getTypeName();

        if ($typeName === null) {
            return null;
        }

        $module = $context->action->controller->module;
        $container = \Yii::$container;

        // Try using DI container to bind target result
        if (($component = $module->get($name, false)) instanceof $typeName) {
            $result  = new BindingResult($component);
            $result->message = "Component: " . get_class($component) . " \$$name";
        } elseif ($module->has($typeName) && ($service = $module->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = 'Module ' . get_class($module) . " DI: $typeName \$$name";
        } elseif ($container->has($typeName) && ($service = $container->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = "Container DI: $typeName \$$name";
        } elseif ($target->allowsNull()) {
            //NOTE: Binding may be supported by other binders in collection
            //$result  = new BindingResult(null);
            //$result->message = "Unavailable service: $name";
        }

        return $result;
    }
}
