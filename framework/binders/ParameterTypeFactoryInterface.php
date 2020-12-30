<?php

namespace yii\binders;

use ReflectionParameter;

interface ParameterTypeFactoryInterace
{
    /**
     * @param ReflectionParameter $type
     * @return bool
     */
    public function canCreateType($type);

    /**
     * @param ReflectionParameter $type
     * @param BindingContext $context
     * @return mixed
     */
    public function createType($type, $context);
}
