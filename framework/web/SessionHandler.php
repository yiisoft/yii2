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
    /**
     * @var string|null the ID returned by the last [[create_sid()]] call, forgotten by [[open()]]
     */
    private $_createdId;


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
     * The ID is remembered until [[open()]], so that [[validateId()]] reports it as missing when PHP checks it for
     * collisions.
     * @return string the new session ID
     * @since 2.0.56
     */
    public function create_sid(): string
    {
        $this->_createdId = $this->_session->createSessionId();

        return $this->_createdId;
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
        $this->_createdId = null;

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
     * Returns whether a session with the given ID exists.
     * PHP calls this method for the ID supplied by the client when `session.use_strict_mode` is enabled, and for the
     * ID returned by [[create_sid()]] to detect collisions: in `session_regenerate_id()` when strict mode is enabled,
     * and in `session_create_id()` regardless of it. The ID returned by the last [[create_sid()]] call is reported as
     * missing without consulting the storage, as [[Session::createSessionId()]] must return IDs that do not collide;
     * any other ID is checked through [[Session::sessionIdExists()]].
     * @param string $id the session ID
     * @return bool whether the session exists
     * @since 2.0.56
     */
    public function validateId($id): bool
    {
        if ($id === $this->_createdId) {
            return false;
        }

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
