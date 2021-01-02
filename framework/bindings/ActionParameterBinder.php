<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use Yii;
use yii\base\BaseObject;
use yii\base\InlineAction;

class ActionParameterBinder extends BaseObject implements ActionModelBinderInterface
{
    private $bindingRegistryInstance = null;
    public $bindingRegistry = "yii\\bindings\\BindingRegistry";

    /**
     * @return ModelBinderInterface
     */
    public function getBindingRegistry()
    {
        if ($this->bindingRegistryInstance == null) {
            $this->bindingRegistryInstance =  Yii::createObject($this->bindingRegistry);
        }
        return $this->bindingRegistryInstance;
    }

    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($action->controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $binder = $this->getBindingRegistry();

        $bindingContext = new BindingContext(
            Yii::$app->request,
            $binder,
            $action,
            $params
        );

        $arguments = [];

        $methodParameters = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            $value = $bindingContext->getParameterValue($name);
            $methodParameters[$name] = new BindingParameter($param, $value);
        }

        foreach ($methodParameters as $name => $param) {
            $result = $binder->bindModel($param, $bindingContext);
            if ($result instanceof BindingResult) {
                $arguments[$name] = $result->value;
            } else {
                $arguments[$name] = null;
            }
        }

        $result = new ActionBindingResult;
        $result->arguments = $arguments;
        return $result;
    }
}
