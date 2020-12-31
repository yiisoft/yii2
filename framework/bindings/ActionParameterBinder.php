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

class ActionParameterBinder extends BaseObject implements ActionParameterBinderInterface {
    private $bindingRegistry = null;

    public function getBindingRegistry() {
        if ($this->bindingRegistry == null) {
            $this->bindingRegistry =  Yii::createObject([
                'class' => "yii\bindings\BindingRegistry"
            ]);
        }
        return $this->bindingRegistry;
    }

    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($action->controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $bindingRegistry = $this->getBindingRegistry();
        $bindingContext = new BindingContext(\Yii::$app->request,$action, $params);

        $arguments = [];

        $methodParameters = [];
        foreach ($method->getParameters() as $param) {
            $methodParameters[$param->getName()] = $param;
        }

        foreach ($methodParameters as $name => $param) {
            $result = $bindingRegistry->bindModel($param, $bindingContext);
            if ($result instanceof BindingResult) {
                $arguments[$name] = $result->value;
            }
        }

        foreach($arguments as $name => $argument) {
            if ($argument instanceof ParameterBinderInterface) {
                $param = $methodParameters[$name];
                $argument->bindModel($param,  $bindingContext);
            }
        }

        return $arguments;
    }
}
