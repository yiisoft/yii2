<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

use ReflectionMethod;
use Yii;

/**
 * Action is the base class for all controller action classes.
 *
 * Action provides a way to reuse action method code. An action method in an Action
 * class can be used in multiple controllers or in different projects.
 *
 * Derived classes must implement a method named `run()`. This method
 * will be invoked by the controller when the action is requested.
 * The `run()` method can have parameters which will be filled up
 * with user input values automatically according to their names.
 * For example, if the `run()` method is declared as follows:
 *
 * ```php
 * public function run($id, $type = 'book') { ... }
 * ```
 *
 * And the parameters provided for the action are: `['id' => 1]`.
 * Then the `run()` method will be invoked as `run(1)` automatically.
 *
 * For more details and usage information on Action, see the [guide article on actions](guide:structure-controllers).
 *
 * @property-read string $uniqueId The unique ID of this action among the whole application.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends Component
{
    /**
     * @var string ID of the action
     */
    public $id;

    /**
     * @var Controller|\yii\web\Controller|\yii\console\Controller the controller that owns this action
     */
    public $controller;

    /**
     * @var ReflectionMethod the action handler used to define this action
     */
    protected $actionHandler = null;

    /**
     * @var array the action handler arguments, the derived class may access resolved action arguments in the beforeRun or the afterRun methods
     */
    protected $arguments = [];

    /**
     * @var mixed the result of the action, derived class my set or get action result in the afterRun method
     */
    protected $result = null;

    /**
     * @var mixed the params used to run this action
     */
    protected $params = null;

    /**
     * Constructor.
     *
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;

        if ($this->actionHandler === null) {
            $reflection = new \ReflectionClass($this);
            if ($reflection->hasMethod("run")) {
                $this->actionHandler = $reflection->getMethod("run");
            }
        }
        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application.
     *
     * @return string the unique ID of this action among the whole application.
     */
    public function getUniqueId()
    {
        return $this->controller->getUniqueId() . '/' . $this->id;
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     *
     * @param array $params the parameters to be bound to the action's run() method.
     * @return mixed the result of the action
     * @throws InvalidConfigException if the action class does not have a run() method
     */
    public function runWithParams($params)
    {
        $this->params = $params;
        $methodName = $this->getActionMethodName();
        $instance = $this->getActionObject();

        Yii::debug('Running action: ' . get_class($instance) . "::{$methodName}(), invoked by "  . get_class($this->controller), __METHOD__);

        $this->arguments = $this->resolveActionArguments($params);
        $this->result = null;

        if ($this->beforeRun()) {
            $this->result = $this->executeAction($this->arguments);
            $this->afterRun();
        }

        return $this->result;
    }

    /**
     * This method is called right before `run()` is executed.
     * You may override this method to do preparation work for the action run.
     * If the method returns false, it will cancel the action.
     *
     * @return bool whether to run the action.
     */
    protected function beforeRun()
    {
        return true;
    }

    /**
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     */
    protected function afterRun()
    {
    }

    /**
     * Resolves action arguments, derived class my override this method to provide custom argument resolver
     *
     * @return array
     */
    public function resolveActionArguments(array $params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }
        return $args;
    }

    /**
     * Returns handler for this action
     *
     * @return ReflectionMethod
     */
    public function getActionHandler()
    {
        if ($this->actionHandler === null) {
            $methodName = $this->getActionMethodName();
            $instance = $this->getActionObject();
            throw new InvalidConfigException(get_class($instance) . " must define a \"{$methodName}()\" method.");
        }
        return $this->actionHandler;
    }

    /**
     * Gets params used to run action
     *
     * @return mixed the params used to run action using runWithParams method
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gets object that contains a method for this action, for InlineAction it's controller instance, otherwise, it's the action itself
     *
     * @return object the object that contains method for this action
     */
    public function getActionObject()
    {
        return $this;
    }

    /**
     * Returns action method name
     *
     * @return string the action method name
     */
    public function getActionMethodName()
    {
        return "run";
    }

    /**
     * Executes action handler using resolved action arguments
     *
     * @param array $args the action handler arguments
     * @return mixed the result of action handler invocation
     */
    public function executeAction($args)
    {
        $instance = $this->getActionObject();
        return $this->getActionHandler()->invokeArgs($instance, $args);
    }
}
