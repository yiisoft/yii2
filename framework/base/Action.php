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
 * @property-read string $uniqueId The unique ID of this action among the whole application. This property is
 * read-only.
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
     * @var mixed the result of the action, derived class may set or get action result in the afterRun method.
     * @since 2.0.41
     */
    protected $result = null;

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
     * @param array $params the parameters to be bound to the action's run() or $this->actionMethod method.
     * @return mixed the result of the action
     * @throws InvalidConfigException if the action or controller class does not have a run() or $this->actionMethod method
     */
    public function runWithParams($params)
    {
        $arguments = $this->resolveActionArguments($params);

        $modules = [];
        $runAction = true;

        // call beforeAction on modules
        foreach ($this->controller->getModules() as $module) {
            if ($module->beforeAction($this)) {
                array_unshift($modules, $module);
            } else {
                $runAction = false;
                break;
            }
        }
        $this->result = null;
        if ($runAction && $this->controller->beforeAction($this)) {
            // run the action
            $this->executeAction($arguments);
            $this->result = $this->controller->afterAction($this, $this->result);

            // call afterAction on modules
            foreach ($modules as $module) {
                /* @var $module Module */
                $this->result = $module->afterAction($this, $this->result);
            }
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
     * @return mixed the processed action result got from `$this->result`.
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
     * Executes action handler using resolved action arguments
     *
     * @param array $args the action handler arguments
     */
    protected function executeAction($args)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        
        if ($this->beforeRun()) {
            Yii::debug('Running action: ' . get_class($this) . '::run(), invoked by '  . get_class($this->controller), __METHOD__);
            $this->result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun();
        }        
    }
    
    /**
     * Get argument passed to the specified parameter
     *
     * @param string $paramName name of declared parameter in inlineAction actionMethod/ Action::run().
     * @return mixed value bound to the parameter `$paramName`
     * @throws BadRequestHttpException if the parameter `$paramName` is not defined or argument not yet bound to it.
     * @since 2.0.41
     */
    public function getRequestedParam($paramName)
    { 
        if (array_key_exists($paramName, (array) Yii::$app->requestedParams)) {
            $requestedParam = Yii::$app->requestedParams[$paramName];          
            if (isset($this->controller->actionInjectionsMeta[$paramName])) {                
                $injectionMeta = $this->controller->actionInjectionsMeta[$paramName];
                if ($injectionMeta['injector'] === 'ServiceLocator') {
                    $requestedParam = $this->controller->module->get($injectionMeta['type']);
                } elseif ($injectionMeta['injector'] === 'Container' ) {
                    $requestedParam =  \Yii::$container->get($injectionMeta['type']);
                } else {
                    $requestedParam = null;
                }
            }
            
            return $requestedParam;
        }

        throw new BadRequestHttpException("Parameter: {$paramName} does not exist or no argument yet bound to it");
    }    
}
