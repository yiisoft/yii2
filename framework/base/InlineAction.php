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
 * InlineAction represents an action that is defined as a controller method.
 *
 * The name of the controller method is available via [[actionMethod]] which is set by the [[controller]] who creates
 * this action.
 *
 * For more details and usage information on InlineAction, see the [guide article on actions](guide:structure-controllers).
 *
 * Not standalone-compatible: by definition InlineAction targets a controller method, so it cannot be registered in
 * [[\yii\base\Module::$actionMap]] and is always created with a non-`null` controller.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of Controller = Controller
 * @extends Action<T>
 */
class InlineAction extends Action
{
    /**
     * @var string The controller method that this inline action is associated with.
     */
    public string $actionMethod = '';

    /**
     * @param string $id the ID of this action
     * @param T $controller the controller that owns this action
     * @param string $actionMethod the controller method that this inline action is associated with
     * @param array<string, mixed> $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;

        parent::__construct($id, $controller, $config);
    }

    /**
     * Runs this action with the specified parameters.
     *
     * This method is mainly invoked by the controller.
     *
     * Since version 22.0, the controller method is wrapped in the same {@see Action::beforeRun()} / {@see afterRun()}
     * guard as standalone {@see Action::runWithParams()}, so subclasses overriding `beforeRun()` to short-circuit the
     * action also short-circuit inline action methods.
     *
     * @param array $params Action parameters.
     *
     * @return mixed The result of the action, or `null` if {@see beforeRun()} returned `false`.
     */
    public function runWithParams($params): mixed
    {
        $args = $this->controller->bindActionParams($this, $params);

        Yii::debug(
            'Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()',
            __METHOD__,
        );

        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        if ($this->beforeRun()) {
            $result = call_user_func_array([$this->controller, $this->actionMethod], $args);

            $this->afterRun();

            return $result;
        }

        return null;
    }
}
