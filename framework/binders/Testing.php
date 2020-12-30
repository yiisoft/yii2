<?php

use yii\base\Action;
use yii\base\BaseObject;
use yii\base\InlineAction;
use yii\binders\BindingContext;

class BindingResult {
    public $value;
    public function __construct($value)
    {
        $this->value;
    }
}

interface ParameterFactoryInterface {
    public function create($type, $context);
}

interface ParameterBinderInterface {
    public function bind($context);
}

class DataFilterBinder implements ParameterFactoryInterface {
    public function create($type, $context) {
        $typeName = $type->getType()->getName();
        $dataFilter = new $typeName;
        $dataFilter->load($context->request->getBodyParams());
        return new BindingResult($dataFilter);
    }
}

class BindingRegistry extends BaseObject implements ParameterFactoryInterface
{
    public function getBinders() {
        return [];
    }

    protected function getDefaultBinders() {
        return [
            'yii\binders\BuiltinTypeBinder',
            'yii\binders\ContainerTypeBinder',
            'yii\binders\ActiveRecordBinder',
            'yii\binders\DataFilterBinder',
            'yii\binders\DateTimeBinder',
            'yii\binders\ClassTypeBinder',
        ];
    }

    public function create($type, $context)
    {
        $binders = $this->getBinders();
        foreach($binders as $binder) {
            $result = $binder->create($type, $context);
            if ($result instanceof BindingResult) {
                return $result;
            }
        }
        return null;
    }
}


interface ActionParameterBinderInterface {
    /**
     * @param Action $action
     * @param array $params
     * @return array
     */
    public function bindActionParams($action, $params);
}

class ActionParameterBinder extends BaseObject implements ActionParameterBinderInterface {


    public function getBindingRegistry() {
        return new BindingRegistry();
    }

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

        $bindingRegistry = $this->getBindingRegistry();

        $context = new BindingContext([
            'request' => \Yii::$app->request,
            'action'  => $action,
            'params'  => $params
        ]);

        $arguments = [];

        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            $result = $bindingRegistry->create($param, $context);
            if ($result instanceof BindingResult) {
                $arguments[$name] = $result->value;
            }
        }

        foreach($arguments as $argument) {
            if ($argument instanceof ParameterBinderInterface) {
                $argument->bind($context);
            }
        }

        return $arguments;
    }
}