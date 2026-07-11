<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use PDO;
use RuntimeException;

use function fclose;
use function fopen;
use function fwrite;
use function is_resource;
use function is_string;
use function rewind;
use function str_starts_with;
use function strlen;

/**
 * Command represents an Oracle SQL statement to be executed against a database.
 *
 * @since 2.0.33
 */
class Command extends \yii\db\Command
{
    /**
     * @var string|null OUT bind receiving `SQL%ROWCOUNT` from a PL/SQL `BLOB` upsert block, or `null` when the
     * current statement is not such a block.
     */
    private string|null $_upsertAffected = null;
    /**
     * @var resource[] Temporary streams created and owned by this command.
     */
    private array $_ownedLobStreams = [];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $result = parent::execute();

            return $this->_upsertAffected !== null ? (int) $this->_upsertAffected : $result;
        } finally {
            $this->closeOwnedLobStreams();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        parent::cancel();

        $this->closeOwnedLobStreams();
    }

    /**
     * {@inheritdoc}
     */
    protected function bindPendingParams()
    {
        $paramsPassedByReference = [];
        $requiresTransaction = false;

        foreach ($this->pendingParams as $name => $value) {
            if ($this->isUpsertAffectedParam($name, $value[1])) {
                $this->_upsertAffected = $value[0];
                $this->pdoStatement->bindParam($name, $this->_upsertAffected, $value[1], 2);

                continue;
            }

            if ($value[0] instanceof LobValue) {
                $paramsPassedByReference[$name] = $this->resolveLobStream($value[0]->getValue());
                $this->pdoStatement->bindParam($name, $paramsPassedByReference[$name], PDO::PARAM_LOB);

                $requiresTransaction = true;
            } elseif (PDO::PARAM_STR === $value[1] && is_string($value[0])) {
                $paramsPassedByReference[$name] = $value[0];
                $this->pdoStatement->bindParam($name, $paramsPassedByReference[$name], $value[1], strlen($value[0]));
            } else {
                $this->pdoStatement->bindValue($name, $value[0], $value[1]);
            }
        }

        if ($requiresTransaction) {
            // PDO_OCI writes the payload through the RETURNING LOB locator after execution, before the commit.
            $this->requireTransaction();
        }

        $this->pendingParams = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function reset()
    {
        $this->_upsertAffected = null;

        $this->closeOwnedLobStreams();
        parent::reset();
    }

    /**
     * Resolves a string or resource to a temporary stream owned by this command.
     *
     * @param string|resource $value Payload to bind.
     *
     * @return resource Caller-owned or temporary stream.
     */
    private function resolveLobStream($value)
    {
        if (is_resource($value)) {
            return $value;
        }

        $stream = fopen('php://temp', 'w+b');
        $length = strlen($value);

        if (
            $stream === false
            || fwrite($stream, $value) !== $length
            || rewind($stream) === false
        ) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            throw new RuntimeException('Unable to create an Oracle LOB stream.');
        }

        return $this->_ownedLobStreams[] = $stream;
    }

    /**
     * Returns whether a pending parameter is the registered `BLOB` upsert affected-row OUT bind.
     *
     * @param int|string $name Parameter name.
     * @param int $type Parameter type.
     */
    private function isUpsertAffectedParam(int|string $name, int $type): bool
    {
        return is_string($name)
            && str_starts_with($name, QueryBuilder::UPSERT_AFFECTED_PARAM_PREFIX)
            && ($type & PDO::PARAM_INPUT_OUTPUT) === PDO::PARAM_INPUT_OUTPUT;
    }

    /**
     * Closes temporary streams owned by this command.
     */
    private function closeOwnedLobStreams(): void
    {
        foreach ($this->_ownedLobStreams as $stream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->_ownedLobStreams = [];
    }
}
