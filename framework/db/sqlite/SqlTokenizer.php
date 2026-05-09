<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

/**
 * SqlTokenizer splits SQLite query into individual SQL tokens.
 * It's used to obtain a `CHECK` constraint information from a `CREATE TABLE` SQL code.
 *
 * @see https://www.sqlite.org/draft/tokenreq.html
 * @see https://sqlite.org/lang.html
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class SqlTokenizer extends \yii\db\SqlTokenizer
{
    /**
     * {@inheritdoc}
     */
    protected function isWhitespace(&$length)
    {
        static $whitespaces = [
            "\f" => true,
            "\n" => true,
            "\r" => true,
            "\t" => true,
            ' ' => true,
        ];

        $length = 1;
        return isset($whitespaces[$this->substring($length)]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isComment(&$length)
    {
        static $comments = [
            '--' => true,
            '/*' => true,
        ];

        $length = 2;
        if (!isset($comments[$this->substring($length)])) {
            return false;
        }

        if ($this->substring($length) === '--') {
            $length = $this->indexAfter("\n") - $this->offset;
        } else {
            $length = $this->indexAfter('*/') - $this->offset;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isOperator(&$length, &$content)
    {
        static $operators = [
            '!=',
            '%',
            '&',
            '(',
            ')',
            '*',
            '+',
            ',',
            '-',
            '.',
            '/',
            ';',
            '<',
            '<<',
            '<=',
            '<>',
            '=',
            '==',
            '>',
            '>=',
            '>>',
            '|',
            '||',
            '~',
        ];

        return $this->startsWithAnyLongest($operators, true, $length);
    }

    /**
     * {@inheritdoc}
     */
    protected function isIdentifier(&$length, &$content)
    {
        static $identifierDelimiters = [
            '"' => '"',
            '[' => ']',
            '`' => '`',
        ];

        if (!isset($identifierDelimiters[$this->substring(1)])) {
            return false;
        }

        $delimiter = $identifierDelimiters[$this->substring(1)];
        $offset = $this->offset;
        while (true) {
            $offset = $this->indexAfter($delimiter, $offset + 1);
            if ($delimiter === ']' || $this->substring(1, true, $offset) !== $delimiter) {
                break;
            }
        }
        $length = $offset - $this->offset;
        $content = $this->substring($length - 2, true, $this->offset + 1);
        if ($delimiter !== ']') {
            $content = strtr($content, ["$delimiter$delimiter" => $delimiter]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isStringLiteral(&$length, &$content)
    {
        if ($this->substring(1) !== "'") {
            return false;
        }

        $offset = $this->offset;
        while (true) {
            $offset = $this->indexAfter("'", $offset + 1);
            if ($this->substring(1, true, $offset) !== "'") {
                break;
            }
        }
        $length = $offset - $this->offset;
        $content = strtr($this->substring($length - 2, true, $this->offset + 1), ["''" => "'"]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isKeyword($string, &$content)
    {
        static $keywords = [
            'ABORT' => true,
            'ACTION' => true,
            'ADD' => true,
            'AFTER' => true,
            'ALL' => true,
            'ALTER' => true,
            'ANALYZE' => true,
            'AND' => true,
            'AS' => true,
            'ASC' => true,
            'ATTACH' => true,
            'AUTOINCREMENT' => true,
            'BEFORE' => true,
            'BEGIN' => true,
            'BETWEEN' => true,
            'BY' => true,
            'CASCADE' => true,
            'CASE' => true,
            'CAST' => true,
            'CHECK' => true,
            'COLLATE' => true,
            'COLUMN' => true,
            'COMMIT' => true,
            'CONFLICT' => true,
            'CONSTRAINT' => true,
            'CREATE' => true,
            'CROSS' => true,
            'CURRENT_DATE' => true,
            'CURRENT_TIME' => true,
            'CURRENT_TIMESTAMP' => true,
            'DATABASE' => true,
            'DEFAULT' => true,
            'DEFERRABLE' => true,
            'DEFERRED' => true,
            'DELETE' => true,
            'DESC' => true,
            'DETACH' => true,
            'DISTINCT' => true,
            'DROP' => true,
            'EACH' => true,
            'ELSE' => true,
            'END' => true,
            'ESCAPE' => true,
            'EXCEPT' => true,
            'EXCLUSIVE' => true,
            'EXISTS' => true,
            'EXPLAIN' => true,
            'FAIL' => true,
            'FOR' => true,
            'FOREIGN' => true,
            'FROM' => true,
            'FULL' => true,
            'GLOB' => true,
            'GROUP' => true,
            'HAVING' => true,
            'IF' => true,
            'IGNORE' => true,
            'IMMEDIATE' => true,
            'IN' => true,
            'INDEX' => true,
            'INDEXED' => true,
            'INITIALLY' => true,
            'INNER' => true,
            'INSERT' => true,
            'INSTEAD' => true,
            'INTERSECT' => true,
            'INTO' => true,
            'IS' => true,
            'ISNULL' => true,
            'JOIN' => true,
            'KEY' => true,
            'LEFT' => true,
            'LIKE' => true,
            'LIMIT' => true,
            'MATCH' => true,
            'NATURAL' => true,
            'NO' => true,
            'NOT' => true,
            'NOTNULL' => true,
            'NULL' => true,
            'OF' => true,
            'OFFSET' => true,
            'ON' => true,
            'OR' => true,
            'ORDER' => true,
            'OUTER' => true,
            'PLAN' => true,
            'PRAGMA' => true,
            'PRIMARY' => true,
            'QUERY' => true,
            'RAISE' => true,
            'RECURSIVE' => true,
            'REFERENCES' => true,
            'REGEXP' => true,
            'REINDEX' => true,
            'RELEASE' => true,
            'RENAME' => true,
            'REPLACE' => true,
            'RESTRICT' => true,
            'RIGHT' => true,
            'ROLLBACK' => true,
            'ROW' => true,
            'SAVEPOINT' => true,
            'SELECT' => true,
            'SET' => true,
            'TABLE' => true,
            'TEMP' => true,
            'TEMPORARY' => true,
            'THEN' => true,
            'TO' => true,
            'TRANSACTION' => true,
            'TRIGGER' => true,
            'UNION' => true,
            'UNIQUE' => true,
            'UPDATE' => true,
            'USING' => true,
            'VACUUM' => true,
            'VALUES' => true,
            'VIEW' => true,
            'VIRTUAL' => true,
            'WHEN' => true,
            'WHERE' => true,
            'WITH' => true,
            'WITHOUT' => true,
        ];

        $string = mb_strtoupper($string, 'UTF-8');
        if (!isset($keywords[$string])) {
            return false;
        }

        $content = $string;
        return true;
    }
}
