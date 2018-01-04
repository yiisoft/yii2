<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http\server;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use yii\base\BaseObject;

/**
 * CallbackRequestHandler
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class CallbackRequestHandler extends BaseObject implements RequestHandlerInterface
{
    /**
     * @var callable a PHP callback matching signature of [[RequestHandlerInterface::handle()]].
     */
    public $callback;


    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func($this->callback, $request);
    }
}