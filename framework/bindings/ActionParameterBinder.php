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

final class ActionParameterBinder extends BaseObject implements ActionParameterBinderInterface
{
    private $modelBinderInstance = null;
    public $modelBinderClass = "yii\\bindings\\CompositeModelBinder";

    /**
     * @return ModelBinderInterface
     */
    public function getModelBinder()
    {
        if ($this->modelBinderInstance == null) {
            $this->modelBinderInstance =  Yii::createObject($this->modelBinderClass);
        }
        return $this->modelBinderInstance;
    }

    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($action->controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $binder = $this->getModelBinder();
        $bindingContext = new BindingContext(Yii::$app->request, $binder, $action, $params);

        $arguments = [];
        $missing = [];
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
                $missing[] = $name;
            }
        }

        $result = new ActionBindingResult;
        $result->parameters = $methodParameters;
        $result->arguments = $arguments;
        $result->missing = $missing;
        return $result;
    }
}
