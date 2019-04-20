<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\SqlToken;
use yii\helpers\StringHelper;

/**
 * Command 表示 SQLite 数据库要执行的 SQL 语句。
 *
 * {@inheritdoc}
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.14
 */
class Command extends \yii\db\Command
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $sql = $this->getSql();
        $params = $this->params;
        $statements = $this->splitStatements($sql, $params);
        if ($statements === false) {
            return parent::execute();
        }

        $result = null;
        foreach ($statements as $statement) {
            list($statementSql, $statementParams) = $statement;
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
     * 将指定的 SQL 代码拆分为单独的 SQL 语句并返回，
     * 如果只有一个语句，则返回 `false`。
     * @param string $sql
     * @param array $params
     * @return string[]|false
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
     * 返回指定语句标记中使用的命名绑定。
     * @param SqlToken $statement
     * @param array $params
     * @return array
     */
    private function extractUsedParams(SqlToken $statement, $params)
    {
        preg_match_all('/(?P<placeholder>[:][a-zA-Z0-9_]+)/', $statement->getSql(), $matches, PREG_SET_ORDER);
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
