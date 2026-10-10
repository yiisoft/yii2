<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use yii\rbac\Item;
use yii\rbac\Assignment;
use yii\rbac\Rule;
use yii\rbac\PhpManager;

/**
 * Exposes protected properties and methods to inspect from outside.
 */
class ExposedPhpManager extends PhpManager
{
    /**
     * @var Item[]
     */
    public $items = []; // itemName => item
    /**
     * @var array
     */
    public $children = []; // itemName, childName => child
    /**
     * @var Assignment[]
     */
    public $assignments = []; // userId, itemName => assignment
    /**
     * @var Rule[]
     */
    public $rules = []; // ruleName => rule

    public function load(): void
    {
        parent::load();
    }

    public function save(): void
    {
        parent::save();
    }
}
