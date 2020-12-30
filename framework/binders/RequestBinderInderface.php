<?php

namespace yii\binders;

use yii\web\Request;

interface RequestBinderInterface
{
    /**
     * @param BindingContext $context
     * @return void
     */
    public function bind($context);
}
