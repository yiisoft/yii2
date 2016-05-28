<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * SchemaBuilderTrait contains shortcut methods to create instances of
 * [[ColumnSchemaBuilder]].
 *
 * These can be used in database migrations to define database schema types
 * using a PHP interface.
 * This is useful to define a schema in a DBMS independent way so that the
 * application may run on different DBMS the same way.
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
     * @return Connection the database connection to be used for schema
     * building.
     */
    abstract protected function getDb();

    /**
     * Creates a column of the specified type.
     *
     * @param string $type the column type to be created.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.9
     */
    public function createColumnBuilder($type, $length)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(
            $type,
            $length
        );
    }

    /**
     * Creates a primary key column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @param boolean $autoIncrement if this column will use AUTO_INCREMENT
     * parameter since 2.0.9
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     * @see ColumnSchemaBuilder::primaryKey()
     */
    public function primaryKey($length = null, $autoIncrement = true)
    {
        return $this->integer($length)->primaryKey($autoIncrement);
    }

    /**
     * Creates a big primary key column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @param boolean|null $autoIncrement if this column will use AUTO_INCREMENT
     * parameter since 2.0.9
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     * @see ColumnSchemaBuilder::primaryKey()
     */
    public function bigPrimaryKey($length = null, $autoIncrement = true)
    {
        return $this->bigInteger($length)->primaryKey($autoIncrement);
    }

    /**
     * Creates a char column.
     * @param integer $length column size definition i.e. the maximum string
     * length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.8
     */
    public function char($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_CHAR, $length);
    }

    /**
     * Creates a string column.
     * @param integer $length column size definition i.e. the maximum string
     * length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function string($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_STRING, $length);
    }

    /**
     * Creates a text column.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function text()
    {
        return $this->createColumnBuilder(Schema::TYPE_TEXT);
    }

    /**
     * Creates a smallint column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function smallInteger($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_SMALLINT, $length);
    }

    /**
     * Creates an integer column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function integer($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_INTEGER, $length);
    }

    /**
     * Creates a bigint column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function bigInteger($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_BIGINT, $length);
    }

    /**
     * Creates a float column.
     * @param integer $precision column value precision. First parameter passed
     * to the column type, e.g. FLOAT(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function float($precision = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_FLOAT, $precision);
    }

    /**
     * Creates a double column.
     * @param integer $precision column value precision. First parameter passed
     * to the column type, e.g. DOUBLE(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function double($precision = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_DOUBLE, $precision);
    }

    /**
     * Creates a decimal column.
     * @param integer $precision column value precision, which is usually the
     * total number of digits.
     * First parameter passed to the column type, e.g.
     * DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param integer $scale column value scale, which is usually the number of
     * digits after the decimal point.
     * Second parameter passed to the column type, e.g.
     * DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
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
        return $this->createColumnBuilder(Schema::TYPE_DECIMAL, $length);
    }

    /**
     * Creates a datetime column.
     * @param integer $precision column value precision. First parameter passed
     * to the column type, e.g. DATETIME(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function dateTime($precision = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_DATETIME, $precision);
    }

    /**
     * Creates a timestamp column.
     * @param integer $precision column value precision. First parameter
     * passed to the column type, e.g. TIMESTAMP(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function timestamp($precision = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_TIMESTAMP, $precision);
    }

    /**
     * Creates a time column.
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. TIME(precision).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function time($precision = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_TIME, $precision);
    }

    /**
     * Creates a date column.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function date()
    {
        return $this->createColumnBuilder(Schema::TYPE_DATE);
    }

    /**
     * Creates a binary column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function binary($length = null)
    {
        return $this->createColumnBuilder(Schema::TYPE_BINARY, $length);
    }

    /**
     * Creates a boolean column.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
     * @since 2.0.6
     */
    public function boolean()
    {
        return $this->createColumnBuilder(Schema::TYPE_BOOLEAN);
    }

    /**
     * Creates a money column.
     * @param integer $precision column value precision, which is usually the
     * total number of digits.
     * First parameter passed to the column type, e.g.
     * DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param integer $scale column value scale, which is usually the number of
     * digits after the decimal point.
     * Second parameter passed to the column type, e.g.
     * DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further
     * customized.
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
        return $this->createColumnBuilder(Schema::TYPE_MONEY, $length);
    }
}
