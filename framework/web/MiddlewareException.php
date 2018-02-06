<?php

namespace yii\web;

use yii\base\Exception;

class MiddlewareException extends Exception
{
    public function getName()
    {
        return "Middleware error";
    }
}