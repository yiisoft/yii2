<?php

namespace yii\web;

trait MiddlewareTrait
{
    public $middleware = [];

    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function callMiddleware(Request $request, Response $response)
    {
        foreach ($this->middleware as $middleware) {
            if (is_callable($middleware)) {
                $middleware($request, $response);
                continue;
            }

            if (class_exists($middleware)) {
                $object = new $middleware;
                if (!($object instanceof MiddlewareInterface)) {
                    throw new MiddlewareException();
                }
                $object->handle($request, $response);
                continue;
            }

            throw new MiddlewareException();
        }
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}