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
     * @param string $typeName
     * @return bool
     */
    public function isInstanceOf($typeName);

    /**
     * @return bool
     */
    public function isBuiltin();

    /**
     * @return bool
     */
    public function allowsNull();

    /**
     * @return bool
     */
    public function hasDefaultValue();

    /**
     * @return mixed
     */
    public function getDefaultValue();
}
