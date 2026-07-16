<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

use function bindec;
use function get_resource_type;
use function in_array;
use function is_array;
use function is_bool;
use function is_resource;
use function json_decode;
use function preg_match;
use function str_replace;
use function stream_get_contents;
use function strtolower;

/**
 * Represents the metadata of a column in a PostgreSQL database table.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * Pattern fragment matching the type suffix of a `pg_get_expr()` cast, such as `character varying(10)`, `"bit"`,
     * `numeric(5,2)`, or `text[]`. Shared by every literal branch of {@see defaultPhpTypecast()} so the cast grammar
     * cannot drift between them.
     */
    private const string CAST_SUFFIX = '[\w" .\[\](),]+';

    /**
     * @var int the dimension of array. Defaults to 0, means this column is not an array.
     */
    public $dimension = 0;
    /**
     * @var string|null name of associated sequence if column is auto-incremental. `null` if no sequence.
     * @since 2.0.29
     */
    public $sequenceName = null;

    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if ($this->dimension > 0) {
            return new ArrayExpression($value, $this->dbType, $this->dimension);
        }
        if (in_array($this->dbType, [Schema::TYPE_JSON, Schema::TYPE_JSONB], true)) {
            return new JsonExpression($value, $this->dbType);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            } elseif ($value === null) {
                return null;
            }

            return $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * Casts $value after retrieving from the DBMS to PHP representation.
     *
     * @param string|null $value
     * @return bool|mixed|null
     */
    protected function phpTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        // `bytea` columns are returned as streams by the PDO PGSQL driver; read them into a string for consumers such
        // as DbCache.
        if ($this->type === Schema::TYPE_BINARY && is_resource($value) && get_resource_type($value) === 'stream') {
            return stream_get_contents($value);
        }

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
                if (is_bool($value)) {
                    return $value;
                }

                switch (strtolower($value)) {
                    case 't':
                    case 'true':
                        return true;
                    case 'f':
                    case 'false':
                        return false;
                }
                return (bool) $value;
            case Schema::TYPE_JSON:
                return json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }

    /**
     * Converts a PostgreSQL column default value to its PHP representation.
     *
     * Recognizes the literal formats produced by `pg_get_expr()` and typecasts only those:
     * - `NULL` or `(NULL)`, optionally followed by a direct type cast, to `null`.
     * - Binary bit literals (`B'...'::`) to their integer value via `bindec()`; kept for backward compatibility,
     *   `pg_get_expr()` reports bit defaults in the quoted form.
     * - Quoted bit literals (`'...'::"bit"`) to their integer value via `bindec()`; `bit varying` defaults carry the
     *   same `"bit"` cast label.
     * - Quoted literals with a cast (`'value'::type`, including type modifiers such as `character varying(10)`) to
     *   the unquoted value with doubled quotes unescaped, delegated to {@see phpTypecast()}.
     * - Numeric literals, optionally parenthesized and optionally cast (`42`, `1.5`, `(0)::numeric`), delegated to
     *   {@see phpTypecast()}.
     * - Bare `true`/`false` to booleans.
     *
     * Everything else — operator expressions, function calls, `nextval(...)`, `CURRENT_*` keywords — becomes an
     * executable {@see Expression}. Branch order is significant: the bit branches must precede the generic
     * quoted-literal branch, or bit strings would be typecast as plain integers.
     *
     * @param mixed $value Raw default value in PostgreSQL schema metadata format.
     *
     * @return mixed Converted PHP value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/^(?:NULL|\(NULL\))(?:::' . self::CAST_SUFFIX . ')?$/i', $value)) {
            return null;
        }

        if (preg_match("/^B'([01]*)'::/", $value, $matches)) {
            return bindec($matches[1]);
        }

        if (preg_match("/^'([01]+)'::\"bit\"$/", $value, $matches)) {
            return bindec($matches[1]);
        }

        if (preg_match("/^'((?:[^']|'')*)'::" . self::CAST_SUFFIX . '$/s', $value, $matches)) {
            return $this->phpTypecast(str_replace("''", "'", $matches[1]));
        }

        if (preg_match('/^(\()?(-?\d+(?:\.\d+)?)(?(1)\))(?:::' . self::CAST_SUFFIX . ')?$/', $value, $matches)) {
            return $this->phpTypecast($matches[2]);
        }

        if ($value === 'true' || $value === 'false') {
            return $value === 'true';
        }

        return new Expression($value);
    }

    /**
     * Creates instance of ArrayParser
     *
     * @return ArrayParser
     */
    protected function getArrayParser()
    {
        static $parser = null;

        if ($parser === null) {
            $parser = new ArrayParser();
        }

        return $parser;
    }
}
