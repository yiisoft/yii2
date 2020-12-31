<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\Action;
use yii\web\Request;

class BindingContext {

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Action
     */
    public $action;

    /**
     * @var array $params
     */
    public $params;

    public function __construct($request, $action, $params)
    {
        $this->request = $request;
        $this->action = $action;
        $this->params = $params;
    }
}
