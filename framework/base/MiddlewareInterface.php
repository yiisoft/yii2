<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * This interface defines the methods required to use the middleware.
 * This interface is not bound to any particular request or response interfaces, using just a notation instead.
 * This interface is not explicitly required anywhere around the core, but you can use it while creating middleware
 * for [[MiddlewareDispatcher]] if you wish.
 *
 * @see MiddlewareDispatcher
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming console request and return a response, optionally delegating
     * response creation to a handler.
     * @param Request $request request instance.
     * @param callable $handler request default (final) handler.
     * @return Response response instance.
     */
    public function process($request, $handler);
}