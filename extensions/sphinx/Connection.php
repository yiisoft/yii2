<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\NotSupportedException;

/**
 * Connection represents the Sphinx connection via MySQL protocol.
 * This class uses [PDO](http://www.php.net/manual/en/ref.pdo.php) to maintain such connection.
 * Note: although PDO supports numerous database drivers, this class supports only MySQL.
 *
 * In order to setup Sphinx "searchd" to support MySQL protocol following configuration should be added:
 * ~~~
 * searchd
 * {
 *     listen = localhost:9306:mysql41
 *     ...
 * }
 * ~~~
 *
 * The following example shows how to create a Connection instance and establish
 * the Sphinx connection:
 * ~~~
 * $connection = new \yii\db\Connection([
 *     'dsn' => 'mysql:host=127.0.0.1;port=9306;',
 *     'username' => $username,
 *     'password' => $password,
 * ]);
 * $connection->open();
 * ~~~
 *
 * After the Sphinx connection is established, one can execute SQL statements like the following:
 * ~~~
 * $command = $connection->createCommand("SELECT * FROM idx_article WHERE MATCH('programming')");
 * $articles = $command->queryAll();
 * $command = $connection->createCommand('UPDATE idx_article SET status=2 WHERE id=1');
 * $command->execute();
 * ~~~
 *
 * For more information about how to perform various DB queries, please refer to [[Command]].
 *
 * This class supports transactions exactly as "yii\db\Connection".
 *
 * Note: while this class extends "yii\db\Connection" some of its methods are not supported.
 *
 * @method \yii\sphinx\Schema getSchema() The schema information for this Sphinx connection
 * @method \yii\sphinx\QueryBuilder getQueryBuilder() the query builder for this Sphinx connection
 *
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the
 * sequence object. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Connection extends \yii\db\Connection
{
    /**
     * @inheritdoc
     */
    public $schemaMap = [
        'mysqli' => 'yii\sphinx\Schema',   // MySQL
        'mysql' => 'yii\sphinx\Schema',    // MySQL
    ];

    /**
     * Obtains the schema information for the named index.
     * @param string $name index name.
     * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
     * @return IndexSchema index schema information. Null if the named index does not exist.
     */
    public function getIndexSchema($name, $refresh = false)
    {
        return $this->getSchema()->getIndexSchema($name, $refresh);
    }

    /**
     * Quotes a index name for use in a query.
     * If the index name contains schema prefix, the prefix will also be properly quoted.
     * If the index name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     * @param string $name index name
     * @return string the properly quoted index name
     */
    public function quoteIndexName($name)
    {
        return $this->getSchema()->quoteIndexName($name);
    }

    /**
     * Alias of [[quoteIndexName()]].
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteTableName($name)
    {
        return $this->quoteIndexName($name);
    }

    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the Sphinx command
     */
    public function createCommand($sql = null, $params = [])
    {
        $this->open();
        $command = new Command([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

    /**
     * This method is not supported by Sphinx.
     * @param string $sequenceName name of the sequence object
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @throws \yii\base\NotSupportedException always.
     */
    public function getLastInsertID($sequenceName = '')
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not supported.');
    }

    /**
     * Escapes all special characters from 'MATCH' statement argument.
     * Make sure you are using this method whenever composing 'MATCH' search statement.
     * Note: this method does not perform quoting, you should place the result in the quotes manually.
     * @param string $str string to be escaped.
     * @return string the properly escaped string.
     */
    public function escapeMatchValue($str)
    {
        return str_replace(
            ['\\', "'", '/', '"', '(', ')', '|', '-', '!', '@', '~', '&', '^', '$', '=', "\x00", "\n", "\r", "\x1a"],
            ['\\\\', "\\'", '\\\\/', '\\\\"', '\\\\(', '\\\\)', '\\\\|', '\\\\-', '\\\\!', '\\\\@', '\\\\~', '\\\\&', '\\\\^', '\\\\$', '\\\\=', "\\x00", "\\n", "\\r", "\\x1a"],
            $str
        );
    }
}
