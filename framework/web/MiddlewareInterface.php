<?php

namespace yii\web;

interface MiddlewareInterface
{
    public function process(Request $request, Response $response);
}