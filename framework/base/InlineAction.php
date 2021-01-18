<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\web\BadRequestHttpException;

/**
 * InlineAction represents an action that is defined as a controller method.
 *
 * The name of the controller method is available via [[actionMethod]] which
 * is set by the [[controller]] who creates this action.
 *
 * For more details and usage information on InlineAction, see the [guide article on actions](guide:structure-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
    /**
     * @var string the controller method that this inline action is associated with
     */
    public $actionMethod;

    /**
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param string $actionMethod the controller method that this inline action is associated with
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        $this->actionHandler = (new \ReflectionClass($controller))->getMethod($actionMethod);
        parent::__construct($id, $controller, $config);
    }

    /**
     * @inheritdoc
     */
    public function getActionObject()
    {
        return $this->controller;
    }

    /**
     * @inheritdoc
     */
    public function getActionMethodName()
    {
        return $this->actionMethod;
    }

    /**
     * Executes action handler using resolved action arguments
     *
     * @param array $args the action handler arguments
     * @return mixed the result of action handler invocation
     */
    protected function executeAction($args)
    {
        $instance = $this->getActionObject();
        return $this->result = $this->getActionHandler()->invokeArgs($instance, $args);
        
    }
    /**
     * Get  argument passed to the specified parameter
     *
     * @param string $paramName name of declared parameter in inlineAction actionMethod/ Action::run().
     * @return mixed value bound to the parameter `$paramName`
     * @throws BadRequestHttpException if the parameter `$paramName` is not defined or argument not yet bound to it.
     */
    public function getRequestedParam($paramName)
    {
        if (isset($this->controller->actionParams[$paramName])) {
            $arg = $this->controller->actionParams[$paramName];
            if (is_callable($arg, true)) {
                return call_user_func($arg);
            } else {
                return $arg;
            }
        } 

        throw new BadRequestHttpException(Yii::t('yii', 'Parameter: {param} does not exist or no argument yet bound to it', [
            'param' => $paramName,
        ]));
    }
}
