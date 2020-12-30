<?php

namespace yii\web;

class TypeFactoryRegistry
{
    public $typeFactories = [];

    /**
     * @return ParameterTypeFactoryInterace[]
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * @return ParameterTypeFactoryInterace[]
     */
    public function getDefaultFactories()
    {
        return [];
    }

    public function createType(ParameterType $type)
    {
        $factories = $this->getFactories();

        foreach ($factories as $factory) {
            if ($factory->canCreateType($type)) {
                $instance = $factory->createType($type);
                return $instance;
            }
        }

        return null;
    }
}
