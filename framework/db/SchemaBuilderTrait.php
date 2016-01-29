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
    protected abstract function getDb();

    /**
     * Creates a primary key column.
     * @param integer $length column size or precision definition.
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
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function bigPrimaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIGPK, $length);
    }

    /**
     * Creates a string column.
     * @param integer $length column size definition i.e. the maximum string length.
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
     * @param integer $length column size or precision definition.
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
     * @param integer $length column size or precision definition.
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
     * @param integer $length column size or precision definition.
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
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. FLOAT(precision).
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
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. DOUBLE(precision).
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
     * @param integer $precision column value precision, which is usually the total number of digits.
     * First parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param integer $scale column value scale, which is usually the number of digits after the decimal point.
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
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. DATETIME(precision).
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
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. TIMESTAMP(precision).
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
     * @param integer $precision column value precision. First parameter passed to the column type, e.g. TIME(precision).
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
     * @param integer $length column size or precision definition.
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
     * @param integer $precision column value precision, which is usually the total number of digits.
     * First parameter passed to the column type, e.g. DECIMAL(precision, scale).
     * This parameter will be ignored if not supported by the DBMS.
     * @param integer $scale column value scale, which is usually the number of digits after the decimal point.
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
     * Creates a bit column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function bit($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_JSONB);
    }
    /**
     * Creates a bit varying column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function bitVarying($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BIT_VARYING);
    }
    
    /**
     * Creates a binary box column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function box()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOX);
    }
    
    /**
     * Creates a binary data column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function bytea()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BYTEA);
    }
    
    /**
     * Creates a cid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function cid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_CID);
    }
    
    /**
     * Creates a cidr column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function cidr()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_CIDR);
    }
    
    /**
     * Creates a circle column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function circle()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_CIRCLE);
    }
    
    /**
     * Creates a daterange column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function daterange()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DATERANGE);
    }
    
    /**
     * Creates a double precision column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function doublePrecision()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_DOUBLE_PRECISION);
    }
    
    /**
     * Creates a gtsvector column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function gtsvector()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_GTSVECTOR);
    }
    
    /**
     * Creates a inet column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function inet()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INET);
    }
    
    /**
     * Creates a interval column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function interval()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INTERVAL);
    }  
    
    /**
     * Creates a json column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function json()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_JSONB);
    }
    
    /**
     * Creates a binary json column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function jsonb()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_JSONB);
    } 

    /**
     * Creates a line column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function line()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_LINE);
    }
    
    /**
     * Creates a lseg column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function lseg()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_LSEG);
    }
    
    /**
     * Creates a macaddr column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function macaddr()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_MACADDR);
    }
    
    /**
     * Creates a numeric column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function numeric($precision = null, $scale = null)
    {
        $length = [];
        if ($precision !== null) {
            $length[] = $precision;
        }
        if ($scale !== null) {
            $length[] = $scale;
        }
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_NUMERIC, $length);
    }
    
    /**
     * Creates a numrange column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function numrange()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_NUMRANGE);
    }
    
    /**
     * Creates a oid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function oid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_OID);
    }
    
    /**
     * Creates a oidvector column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function oidvector()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_OIDVECTOR);
    }
    
    /**
     * Creates a path column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function path()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_PATH);
    }
    
    /**
     * Creates a point column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function point()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_POINT);
    }
    
    /**
     * Creates a real column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function real()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_REAL);
    }
    
    /**
     * Creates a tid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TID);
    }
    
    /**
     * Creates a tinterval column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tinterval()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TINTERVAL);
    }
    
    /**
     * Creates a tsquery column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tsquery()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TSQUERY);
    }
    
    /**
     * Creates a time with time zone column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function timeWithTimeZone()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIME_WITH_TIME_ZONE);
    }
    
    /**
     * Creates a timestamp with time zone column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function timestampWithTimeZone()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP_WITH_TIME_ZONE);
    }
    
    /**
     * Creates a timestamp without time zone range column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tsrange()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TSRANGE);
    }
    
    /**
     * Creates a timestamp with time zone range column column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tstzrange()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TSTZRANGE);
    }
    
    /**
     * Creates a tsvector column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function tsvector()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_TSVECTOR);
    }
    
    /**
     * Creates a uuid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function uuid()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_UUID);
    }
    
    /**
     * Creates a xml column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * This function can be used only postgresql.
     */
    public function xml()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_XML);
    }  

}
