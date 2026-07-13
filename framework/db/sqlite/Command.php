<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\Exception;
use yii\db\SqlToken;
use yii\helpers\StringHelper;

/**
 * Command represents an SQLite's SQL statement to be executed against a database.
 *
 * {@inheritdoc}
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.14
 */
class Command extends \yii\db\Command
{
    /**
     * Whether the command is an integrity check command.
     */
    private bool $_isIntegrityCheck = false;

    /**
     * {@inheritdoc}
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        parent::checkIntegrity($check, $schema, $table);

        $this->_isIntegrityCheck = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->_isIntegrityCheck && $this->db->getMasterPdo()->inTransaction()) {
            throw new Exception(
                'SQLite cannot change foreign-key enforcement inside a transaction. '
                . 'Execute this operation before BEGIN/SAVEPOINT or after COMMIT/ROLLBACK.'
            );
        }

        $sql = $this->getSql();

        $params = $this->params;

        $statements = $this->splitStatements($sql, $params);

        if ($statements === false) {
            return parent::execute();
        }

        $result = null;

        foreach ($statements as $statement) {
            [$statementSql, $statementParams] = $statement;

            $this->setSql($statementSql)->bindValues($statementParams);

            $result = parent::execute();
        }

        $this->setSql($sql)->bindValues($params);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        $sql = $this->getSql();
        $params = $this->params;
        $statements = $this->splitStatements($sql, $params);
        if ($statements === false) {
            return parent::queryInternal($method, $fetchMode);
        }

        list($lastStatementSql, $lastStatementParams) = array_pop($statements);
        foreach ($statements as $statement) {
            list($statementSql, $statementParams) = $statement;
            $this->setSql($statementSql)->bindValues($statementParams);
            parent::execute();
        }
        $this->setSql($lastStatementSql)->bindValues($lastStatementParams);
        $result = parent::queryInternal($method, $fetchMode);
        $this->setSql($sql)->bindValues($params);
        return $result;
    }

    /**
     * Splits the specified SQL code into individual SQL statements and returns them
     * or `false` if there's a single statement.
     * @param string $sql
     * @param array $params
     * @return list<array{string, array}>|false
     */
    private function splitStatements($sql, $params)
    {
        $semicolonIndex = strpos($sql, ';');
        if ($semicolonIndex === false || $semicolonIndex === StringHelper::byteLength($sql) - 1) {
            return false;
        }

        $tokenizer = new SqlTokenizer($sql);
        $codeToken = $tokenizer->tokenize();
        if (count($codeToken->getChildren()) === 1) {
            return false;
        }

        $statements = [];
        foreach ($codeToken->getChildren() as $statement) {
            $statements[] = [$statement->getSql(), $this->extractUsedParams($statement, $params)];
        }
        return $statements;
    }

    /**
     * {@inheritdoc}
     */
    protected function reset()
    {
        parent::reset();
        $this->_isIntegrityCheck = false;
    }

    /**
     * Returns named bindings used in the specified statement token.
     * @param SqlToken $statement
     * @param array $params
     * @return array
     */
    private function extractUsedParams(SqlToken $statement, $params)
    {
        preg_match_all('/(?P<placeholder>:\w+)/', $statement->getSql(), $matches, PREG_SET_ORDER);
        $result = [];
        foreach ($matches as $match) {
            $phName = ltrim($match['placeholder'], ':');
            if (isset($params[$phName])) {
                $result[$phName] = $params[$phName];
            } elseif (isset($params[':' . $phName])) {
                $result[':' . $phName] = $params[':' . $phName];
            }
        }
        return $result;
    }
}
