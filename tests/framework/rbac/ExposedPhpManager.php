<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use yii\rbac\PhpManager;

/**
 * Exposes protected properties and methods to inspect from outside.
 */
class ExposedPhpManager extends PhpManager
{
    /**
     * @var \yii\rbac\Item[]
     */
    public $items = []; // itemName => item
    /**
     * @var array
     */
    public $children = []; // itemName, childName => child
    /**
     * @var \yii\rbac\Assignment[]
     */
    public $assignments = []; // userId, itemName => assignment
    /**
     * @var \yii\rbac\Rule[]
     */
    public $rules = []; // ruleName => rule

    public function load()
    {
        parent::load();
    }

    public function save()
    {
        parent::save();
    }
}
