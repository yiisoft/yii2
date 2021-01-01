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
use yii\bindings\BindingParameter;
use yii\bindings\BindingProperty;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class ClassTypeBinder extends BaseObject implements ParameterBinderInterface
{
    protected function getParams($param, $context)
    {
        if ($context->request->getIsGet()) {
            $params = $param->getValue();
            if (!is_array($params)) {
                $params = json_decode($params, true);
            }
        } else {
            $params = $context->request->getBodyParams();
        }
        return $params;
    }

    public function bindModel($param, $context)
    {
        $typeName = $param->getTypeName();

        if ($typeName === null) {
            return null;
        }

        $data = $this->getParams($param, $context);
        $instance = \Yii::createObject($typeName);
        $result = $this->hydrateObject($instance, $data, $context);

        return new BindingResult($result);
    }

    protected function hydrateObject($instance, $data, $context)
    {
        $reflection = new ReflectionClass($instance);

        foreach ($reflection->getProperties() as $prop) {
            try {
                $value = $data[$prop->name] ?? null;

                $bindingParameter = new BindingProperty($prop, $value);

                $result = $context->binder->bindModel($bindingParameter, $context);

                if ($result instanceof BindingResult) {
                    $prop->setAccessible(true);
                    $prop->setValue($instance, $result->value);
                }
            } catch (Exception $e) {
                var_dump($e->getMessage());
                //throw $e;
            }
        }
        return $instance;
    }
}
