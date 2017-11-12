<?php

namespace yii\web;

interface MiddlewareInterface
{
    public function handle(Request $request, Response $response);
}