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
     *
     * Generates the ID the same way as PHP's default session module does, honoring
     * `session.sid_length` and `session.sid_bits_per_character` ini settings.
     * `session_create_id()` can't be used here because it fails on PHP < 8 when
     * a user save handler (such as this class) is registered.
     */
    #[\ReturnTypeWillChange]
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- method name is defined by SessionHandlerInterface
    public function create_sid()
    {
        static $charsets = [
            4 => '0123456789abcdef',
            5 => '0123456789abcdefghijklmnopqrstuv',
            6 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,-',
        ];

        $bits = (int) ini_get('session.sid_bits_per_character');
        $charset = $charsets[$bits] ?? $charsets[5];
        $length = (int) ini_get('session.sid_length');
        if ($length < 22 || $length > 256) {
            $length = 32;
        }

        $id = '';
        $maxIndex = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $id .= $charset[random_int(0, $maxIndex)];
        }

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function validateId($sessionId): bool
    {
        return $this->_session->readSession($sessionId) !== '';
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
