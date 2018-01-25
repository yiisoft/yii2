<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * SchemaBuilderTrait contains shortcut methods to create instances of [[ColumnSchemaBuilder]].
 *
 * These can be used in database migrations to define database schema types using a PHP interface.
 * This is useful to define a schema in a DBMS independent way so that the application may run on
 * different DBMS the same way.
 *
 * For example you may use the following code inside your migration files:
 *
 * ```php
 * $this->createTable('example_table', [
 *   'id' => $this->primaryKey(),
 *   'name' => $this->string(64)->notNull(),
 *   'type' => $this->integer()->notNull()->defaultValue(10),
 *   'description' => $this->text(),
 *   'rule_name' => $this->string(64),
 *   'data' => $this->text(),
 *   'created_at' => $this->datetime()->notNull(),
 *   'updated_at' => $this->datetime(),
 * ]);
 * ```
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.6
 */
trait SchemaBuilderTrait
{
    /**
     * @return Connection the database connection to be used for schema building.
     */
    abstract protected function getDb();

    /**
     * Creates a primary key column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function primaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_PK, $length);
    }

    /**
     * Creates a big primary key column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function bigPrimaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIGPK, $length);
    }

    /**
     * Creates a char column.
     * @param int $length column size definition i.e. the maximum string length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.8
     */
    public function char($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_CHAR, $length);
    }

    /**
     * Creates a string column.
     * @param int $length column size definition i.e. the maximum string length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function string($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, $length);
    }

    /**
     * Creates a text column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function text()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TEXT);
    }

    /**
     * Creates a smallint column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function smallInteger($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_SMALLINT, $length);
    }

    /**
     * Creates an integer column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function integer($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INTEGER, $length);
    }

    /**
     * Creates a bigint column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function bigInteger($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIGINT, $length);
    }

    /**
     * Creates a float column.
     * @param int $precision column value precision. First parameter passed to the column type, e.g. FLOAT(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function float($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_FLOAT, $precision);
    }

    /**
     * Creates a double column.
     * @param int $precision column value precision. First parameter passed to the column type, e.g. DOUBLE(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function double($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DOUBLE, $precision);
    }

    /**
     * Creates a decimal column.
     * @param int $precision column value precision, which is usually the total number of digits.
     * First parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param int $scale column value scale, which is usually the number of digits after the decimal point.
     * Second parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function decimal($precision = null, $scale = null)
    {
        $length = [];
        if ($precision !== null) {
            $length[] = $precision;
        }
        if ($scale !== null) {
            $length[] = $scale;
        }

        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DECIMAL, $length);
    }

    /**
     * Creates a datetime column.
     * @param int $precision column value precision. First parameter passed to the column type, e.g. DATETIME(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function dateTime($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DATETIME, $precision);
    }

    /**
     * Creates a timestamp column.
     * @param int $precision column value precision. First parameter passed to the column type, e.g. TIMESTAMP(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function timestamp($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP, $precision);
    }

    /**
     * Creates a time column.
     * @param int $precision column value precision. First parameter passed to the column type, e.g. TIME(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function time($precision = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIME, $precision);
    }

    /**
     * Creates a date column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function date()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DATE);
    }

    /**
     * Creates a binary column.
     * @param int $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function binary($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BINARY, $length);
    }

    /**
     * Creates a boolean column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function boolean()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN);
    }

    /**
     * Creates a money column.
     * @param int $precision column value precision, which is usually the total number of digits.
     * First parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param int $scale column value scale, which is usually the number of digits after the decimal point.
     * Second parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function money($precision = null, $scale = null)
    {
        $length = [];
        if ($precision !== null) {
            $length[] = $precision;
        }
        if ($scale !== null) {
            $length[] = $scale;
        }

        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_MONEY, $length);
    }

    /**
     * Creates a JSON column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.14
     * @throws \yii\base\Exception
     */
    public function json()
    {
        /*
         * TODO Remove in Yii 2.1
         *
         * Disabled due to bug in MySQL extension
         * @link https://bugs.php.net/bug.php?id=70384
         */
        if (version_compare(PHP_VERSION, '5.6', '<') && $this->getDb()->getDriverName() === 'mysql') {
            throw new \yii\base\Exception('JSON column type is not supported in PHP < 5.6');
        }

        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_JSON);
    }
}
