<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class ContainerBinder implements ParameterBinderInterface {
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context) {

        $result = null;

        $name = $param->getName();
        $type = $param->getType();

        $module = $context->action->controller->module;
        $container = \Yii::$container;


        // Since it is not a builtin type it must be DI injection.
        $typeName = $type->getName();
        if (($component = $module->get($name, false)) instanceof $typeName) {
            $result  = new BindingResult($component);
            $result->message = "Component: " . get_class($component) . " \$$name";
        } elseif ($module->has($typeName) && ($service = $module->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = 'Module ' . get_class($this->module) . " DI: $typeName \$$name";
        } elseif ($container->has($typeName) && ($service = $container->get($typeName)) instanceof $typeName) {
            $result  = new BindingResult($service);
            $result->message = "Container DI: $typeName \$$name";
        } elseif ($type->allowsNull()) {
            $result  = new BindingResult(null);
            $result->message = "Unavailable service: $name";
        }

        return $result;
    }
}
