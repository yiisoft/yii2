<?php

namespace yii\web;

interface ParameterTypeFactoryInterace
{
    public function canCreateType(ParameterType $type);

    public function createType(ParameterType $type);
}
