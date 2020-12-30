<?php

namespace yii\binders;

class TypeFactoryRegistry implements ParameterTypeFactoryInterace
{
    /**
     * @var array the typeFactories for creating action parameter types.
     * The array keys are the factory names, and the array values are the corresponding configurations
     * for creating the type factory objects.
     */
    public $typeFactories = [];

    /**
     * @return ParameterTypeFactoryInterace[]
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultFactories()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function canCreateType($type) {
        $factories = $this->getFactories();
        foreach ($factories as $factory) {
            if ($factory->canCreateType($type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function createType($type, $context) {
        $factories = $this->getFactories();

        foreach ($factories as $factory) {
            if ($factory->canCreateType($type)) {
                return $factory->createType($type, $context);
            }
        }

        return null;
    }
}
