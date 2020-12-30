<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders;

use yii\base\Action;
use yii\base\BaseObject;
use yii\web\Request;

class BindingContext extends BaseObject {

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
}
