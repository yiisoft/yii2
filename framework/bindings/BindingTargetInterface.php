<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;

interface BindingTargetInterface
{
    /**
     * @return ReflectionParameter|ReflectionProperty
     */
    public function getTarget();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return ReflectionType|null
     */
    public function getType();

    /**
     * @return string|null
     */
    public function getTypeName();

    /**
     * @return bool
     */
    public function isArray();

    /**
     * @return bool
     */
    public function isBuiltin();

    /**
     * @return bool
     */
    public function isDefaultValueAvailable();

    /**
     * @return mixed
     */
    public function getDefaultValue();
}
