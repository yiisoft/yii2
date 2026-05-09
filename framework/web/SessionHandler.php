<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use SessionHandlerInterface;

/**
 * SessionHandler implements an [[\SessionHandlerInterface]] for handling [[Session]] with custom session storage.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 * @since 2.0.52
 */
class SessionHandler implements SessionHandlerInterface
{
    /**
     * @var Session
     */
    private $_session;


    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return $this->_session->closeSession();
    }

    /**
     * @inheritDoc
     */
    public function destroy($id): bool
    {
        return $this->_session->destroySession($id);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        return $this->_session->gcSession($max_lifetime);
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name): bool
    {
        return $this->_session->openSession($path, $name);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function read($id)
    {
        return $this->_session->readSession($id);
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        return $this->_session->writeSession($id, $data);
    }
}
