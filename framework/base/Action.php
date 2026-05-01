<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

use function call_user_func_array;
use function get_class;

/**
 * Action is the base class for all controller action classes.
 *
 * Action provides a way to reuse action method code. An action method in an Action class can be used in multiple
 * controllers or in different projects.
 *
 * Derived classes must implement a method named `run()`. This method will be invoked by the controller when the action
 * is requested.
 *
 * The `run()` method can have parameters which will be filled up with user input values automatically according to
 * their names.
 *
 * For example, if the `run()` method is declared as follows:
 *
 * ```
 * public function run($id, $type = 'book') { ... }
 * ```
 *
 * And the parameters provided for the action are: `['id' => 1]`.
 *
 * Then the `run()` method will be invoked as `run(1)` automatically.
 *
 * For more details and usage information on Action, see the [guide article on actions](guide:structure-controllers).
 *
 * @property-read string $uniqueId The unique ID of this action among the whole application.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of Controller = Controller
 */
class Action extends Component
{
    /**
     * @var string ID of the action
     */
    public $id;
    /**
     * @var T|null the controller that owns this action, or `null` when the action runs standalone.
     * via {@see Module::$actionMap}
     */
    public $controller;

    /**
     * This action when invoked standalone (without a controller).
     *
     * @since 22.0
     */
    private Module|null $_module = null;

    /**
     * Constructor.
     *
     * @param string|null $id The ID of this action, or `null` when the action is instantiated through the DI container
     * and the ID is assigned afterwards by the dispatcher.
     * @param T|null $controller The controller that owns this action, or `null` for standalone handlers.
     * @param array<string, mixed> $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id = null, $controller = null, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;

        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application.
     *
     * When the action is hosted by a controller, the ID is composed of the controller's unique ID and the action ID.
     *
     * For standalone handlers (no controller), the ID falls back to the owning module's unique ID, or to the action ID
     * alone when no module is attached.
     *
     * @return string The unique ID of this action among the whole application.
     */
    public function getUniqueId()
    {
        if ($this->controller !== null) {
            return $this->controller->getUniqueId() . '/' . $this->id;
        }

        $module = $this->getModule();
        $moduleId = $module !== null ? $module->getUniqueId() : '';

        return $moduleId === '' ? $this->id : $moduleId . '/' . $this->id;
    }

    /**
     * Returns the module that owns this action when invoked standalone.
     *
     * Falls back to the controller's module when a controller hosts the action.
     *
     * @return Module|null The owning module, or `null` if neither a module nor a controller is attached.
     *
     * @since 22.0
     */
    public function getModule(): Module|null
    {
        if ($this->_module !== null) {
            return $this->_module;
        }

        return $this->controller?->module;
    }

    /**
     * Sets the module that owns this action when invoked standalone.
     *
     * Used by {@see Module::runAction()} when dispatching a handler registered in {@see Module::$actionMap}.
     *
     * @param Module|null $module The owning module, or `null` to detach.
     *
     * @since 22.0
     */
    public function setModule(Module|null $module): void
    {
        $this->_module = $module;
    }

    /**
     * Runs this action with the specified parameters.
     *
     * When a controller hosts the action, parameter binding is delegated to {@see Controller::bindActionParams()}.
     *
     * For standalone handlers the typed parameters of `run()` are resolved through the DI container via
     * {@see resolveStandaloneParams()}.
     *
     * @param array $params The parameters to be bound to the action's `run()` method.
     *
     * @throws InvalidConfigException if the action class does not have a `run()` method.
     *
     * @return mixed The result of the action.
     */
    public function runWithParams(array $params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }

        if ($this->controller !== null) {
            $args = $this->controller->bindActionParams($this, $params);

            Yii::debug(
                'Running action: ' . get_class($this) . '::run(), invoked by ' . get_class($this->controller),
                __METHOD__,
            );
        } else {
            $args = $this->resolveStandaloneParams($params);

            Yii::debug(
                'Running standalone action: ' . get_class($this) . '::run()',
                __METHOD__,
            );
        }

        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);

            $this->afterRun();

            return $result;
        }

        return null;
    }

    /**
     * Resolves typed parameters of `run()` when the action is invoked without a hosting controller.
     *
     * Delegates to {@see \yii\di\Container::resolveCallableDependencies()} so that route parameters are matched by name
     * and class-typed parameters are autowired from the DI container.
     *
     * @param array $params Route parameters to be matched by name against `run()`'s signature.
     * @return array<array-key, mixed> The resolved positional argument list for `run()`.
     *
     * @since 22.0
     */
    protected function resolveStandaloneParams($params): array
    {
        return Yii::$container->resolveCallableDependencies([$this, 'run'], $params);
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
}
