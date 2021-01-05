<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use Exception;
use ReflectionClass;
use yii\base\BaseObject;
use yii\bindings\BindingProperty;
use yii\bindings\BindingResult;
use yii\bindings\ModelBinderInterface;

final class ClassTypeBinder extends BaseObject implements ModelBinderInterface
{
    protected function getParams($target, $context)
    {
        if ($context->request->getIsGet()) {
            $params = $target->getValue();
            if (!is_array($params)) {
                $params = json_decode($params, true);
            }
        } else {
            $params = $context->request->getBodyParams();
        }
        return $params;
    }

    public function bindModel($target, $context)
    {
        $typeName = $target->getTypeName();
        // echo "\n => Hydrating $typeName\n";

        if ($typeName === null) {
            return null;
        }

        $data = $this->getParams($target, $context);
        $instance = \Yii::createObject($typeName);
        $result = $this->hydrateObject($instance, $data, $context);

        return new BindingResult($result);
    }

    protected function hydrateObject($instance, $data, $context)
    {

        $reflection = new ReflectionClass($instance);

        foreach ($reflection->getProperties() as $prop) {
            try
            {
                $value = $data[$prop->name] ?? null;
                $bindingParameter = new BindingProperty($prop, $value);
                $result = $context->binder->bindModel($bindingParameter, $context);
                // echo " ===> ",
                //     $bindingParameter->getTypeName(), " => ",
                //     json_encode($value), " => ",
                //     get_debug_type($result->value),
                //     "\n";

                if ($result instanceof BindingResult) {
                    $prop->setAccessible(true);
                    $prop->setValue($instance, $result->value);
                }
            } catch (Exception $e) {
                //var_dump($e->getMessage());
            }
        }
        return $instance;
    }
}
