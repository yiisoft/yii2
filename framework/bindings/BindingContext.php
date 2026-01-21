<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\Action;
use yii\web\Request;

final class BindingContext
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var ModelBinderInterface
     */
    public $binder;

    /**
     * @var Action
     */
    public $action;

    /**
     * @var array $params
     */
    public $params;

    public function __construct($request, $binder, $action, $params)
    {
        $this->request = $request;
        $this->binder = $binder;
        $this->action = $action;
        $this->params = $params;
    }

    public function getParameterValue($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }
}
