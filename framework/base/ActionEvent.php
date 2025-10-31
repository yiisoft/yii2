<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of Action
 */
class ActionEvent extends Event
{
    /**
     * @var Action the action currently being executed
     *
     * @phpstan-var T
     * @psalm-var T
     */
    public $action;
    /**
     * @var mixed the action result. Event handlers may modify this property to change the action result.
     */
    public $result;
    /**
     * @var bool whether to continue running the action. Event handlers of
     * [[Controller::EVENT_BEFORE_ACTION]] may set this property to decide whether
     * to continue running the current action.
     */
    public $isValid = true;


    /**
     * Constructor.
     * @param Action $action the action associated with this action event.
     * @param array $config name-value pairs that will be used to initialize the object properties
     *
     * @phpstan-param T $action
     * @psalm-param T $action
     *
     * @phpstan-param array<string, mixed> $config
     * @psalm-param array<string, mixed> $config
     */
    public function __construct($action, $config = [])
    {
        $this->action = $action;
        parent::__construct($config);
    }
}
