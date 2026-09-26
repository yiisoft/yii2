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
 * PHP detects [[create_sid()]] and [[validateId()]] by name, without the class implementing `SessionIdInterface`
 * or `SessionUpdateTimestampHandlerInterface`.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 * @since 2.0.52
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
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
     * Returns a new session ID created by [[Session::createSessionId()]].
     * @return string the new session ID
     * @since 2.0.56
     */
    public function create_sid(): string
    {
        return $this->_session->createSessionId();
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
     * Returns whether a session with the given ID exists, as reported by [[Session::sessionIdExists()]].
     * PHP calls this method only when `session.use_strict_mode` is enabled.
     * @param string $id the session ID
     * @return bool whether the session exists
     * @since 2.0.56
     */
    public function validateId($id): bool
    {
        return $this->_session->sessionIdExists($id);
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        return $this->_session->writeSession($id, $data);
    }
}
